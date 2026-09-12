<?php

namespace App\Services\CsvImport;

use App\Models\ImportBatch;
use App\Models\ImportRowIdempotency;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GenericCsvImportService
{
    private const TMP_DIR = 'imports/tmp';
    private const REPORT_DIR = 'imports/reports';
    private const PREVIEW_LIMIT = 20;

    /**
     * @param array<string,array{label:string,aliases?:array<int,string>}> $fieldDefinitions
     * @return array{
     *     temp_file:string,
     *     original_filename:string,
     *     headers:array<int,array{raw:string,key:string,base:string,index:int,mapped_field:?string}>,
     *     field_options:array<string,string>,
     *     preview_rows:array<int,array<int,string>>,
     *     total_rows:int,
     * }
     */
    public function preview(UploadedFile $file, array $fieldDefinitions): array
    {
        $tempFile = $file->storeAs(
            self::TMP_DIR,
            Str::uuid()->toString().'.csv'
        );

        $analysis = $this->analyzeCsv($tempFile, self::PREVIEW_LIMIT);
        $headers = $analysis['headers'];
        $fieldOptions = [];
        foreach ($fieldDefinitions as $key => $definition) {
            $fieldOptions[$key] = $definition['label'];
        }

        foreach ($headers as &$header) {
            $header['mapped_field'] = $this->suggestField(
                $header['base'],
                $fieldDefinitions
            );
        }
        unset($header);

        return [
            'temp_file' => $tempFile,
            'original_filename' => $file->getClientOriginalName(),
            'headers' => $headers,
            'field_options' => $fieldOptions,
            'preview_rows' => $analysis['preview_rows'],
            'total_rows' => $analysis['total_rows'],
        ];
    }

    /**
     * @param array<string,array{label:string,aliases?:array<int,string>}> $fieldDefinitions
     * @param array<string,mixed> $rules
     * @param array<int,string> $requiredFields
     * @param callable(array<string,mixed>,int): array{created?:int,updated?:int,success?:int}|string|null $rowHandler
     * @param array{
     *     module?:string,
     *     idempotency_key_resolver?:callable(array<string,mixed>,int): ?string
     * } $options
     * @return array{
     *     total_rows:int,
     *     processed:int,
     *     created:int,
     *     updated:int,
     *     success_rows:int,
     *     failed_rows:int,
     *     duplicate_rows:int,
     *     errors:array<int,string>,
     *     warnings:array<int,string>,
     *     error_rows:array<int,array{row:int,error:string,data:array<string,mixed>}>,
     *     duplicate_row_details:array<int,array{row:int,key:string,data:array<string,mixed>}>,
     *     mapping:array<string,string>,
     *     idempotency_keys:array<int,string>,
     * }
     */
    public function importFromTempFile(
        string $tempFile,
        array $mapping,
        array $fieldDefinitions,
        array $rules,
        array $requiredFields,
        callable $rowHandler,
        array $options = []
    ): array {
        $result = [
            'total_rows' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'success_rows' => 0,
            'failed_rows' => 0,
            'duplicate_rows' => 0,
            'errors' => [],
            'warnings' => [],
            'error_rows' => [],
            'duplicate_row_details' => [],
            'mapping' => [],
            'idempotency_keys' => [],
        ];

        if (!str_starts_with($tempFile, self::TMP_DIR.'/') || !Storage::exists($tempFile)) {
            $result['errors'][] = 'File preview tidak ditemukan. Silakan upload ulang CSV.';
            return $result;
        }

        $analysis = $this->analyzeCsv($tempFile, 0);
        $headers = $analysis['headers'];
        $result['total_rows'] = $analysis['total_rows'];

        $mappedFields = [];
        $indexToField = [];
        foreach ($headers as $header) {
            $selectedField = trim((string) ($mapping[$header['key']] ?? ''));
            if ($selectedField === '') {
                continue;
            }

            if (!array_key_exists($selectedField, $fieldDefinitions)) {
                $result['errors'][] = 'Mapping tidak valid untuk kolom: '.$header['raw'];
                continue;
            }

            $indexToField[$header['index']] = $selectedField;
            $result['mapping'][$header['key']] = $selectedField;
            $mappedFields[] = $selectedField;
        }

        if (count($mappedFields) !== count(array_unique($mappedFields))) {
            $result['errors'][] = 'Satu field tidak boleh dipetakan dari beberapa kolom.';
            return $result;
        }

        $missingFields = array_values(array_diff($requiredFields, $mappedFields));
        if ($missingFields !== []) {
            $result['errors'][] = 'Mapping wajib belum lengkap: '.implode(', ', $missingFields);
            return $result;
        }

        $handle = new \SplFileObject(Storage::path($tempFile));
        $handle->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        $idempotencyConfig = $this->resolveIdempotencyConfig($options);
        $rowNumber = 0;
        foreach ($handle as $row) {
            if (!is_array($row)) {
                continue;
            }

            $rowNumber++;
            if ($rowNumber === 1) {
                continue;
            }

            if ($this->isRowEmpty($row)) {
                continue;
            }

            $mappedData = [];
            foreach ($indexToField as $columnIndex => $field) {
                $mappedData[$field] = $this->normalizeCellValue($row[$columnIndex] ?? null);
            }

            $validator = Validator::make($mappedData, $rules);
            if ($validator->fails()) {
                $errorMessage = implode(', ', $validator->errors()->all());
                $result['failed_rows']++;
                $result['errors'][] = 'Baris '.$rowNumber.': '.$errorMessage;
                $result['error_rows'][] = [
                    'row' => $rowNumber,
                    'error' => $errorMessage,
                    'data' => $mappedData,
                ];
                continue;
            }

            $rowIdempotency = $this->resolveRowIdempotency($mappedData, $rowNumber, $idempotencyConfig);
            if ($rowIdempotency !== null && $this->isDuplicateByIdempotency($idempotencyConfig['module'], $rowIdempotency['key'], $rowIdempotency['hash'])) {
                $result['duplicate_rows']++;
                $result['duplicate_row_details'][] = [
                    'row' => $rowNumber,
                    'key' => $rowIdempotency['raw_key'],
                    'data' => $mappedData,
                ];
                $result['warnings'][] = 'Baris '.$rowNumber.' dilewati karena duplikat idempotency key.';
                continue;
            }

            try {
                $handlerResult = $rowHandler($mappedData, $rowNumber);
            } catch (\Throwable $e) {
                $result['failed_rows']++;
                $result['errors'][] = 'Baris '.$rowNumber.': '.$e->getMessage();
                $result['error_rows'][] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $mappedData,
                ];
                continue;
            }

            $result['processed']++;
            $result['success_rows']++;

            if (is_string($handlerResult)) {
                if ($handlerResult === 'created') {
                    $result['created']++;
                } elseif ($handlerResult === 'updated') {
                    $result['updated']++;
                }
            } elseif (is_array($handlerResult)) {
                $result['created'] += (int) ($handlerResult['created'] ?? 0);
                $result['updated'] += (int) ($handlerResult['updated'] ?? 0);
            }

            if ($rowIdempotency !== null) {
                $this->persistIdempotency($idempotencyConfig['module'], $rowIdempotency['key'], $rowIdempotency['hash']);
                $result['idempotency_keys'][] = $rowIdempotency['key'];
            }
        }

        Storage::delete($tempFile);

        $result['idempotency_keys'] = array_values(array_unique($result['idempotency_keys']));

        return $result;
    }

    /**
     * @param array{
     *     total_rows:int,
     *     processed:int,
     *     created:int,
     *     updated:int,
     *     success_rows:int,
     *     failed_rows:int,
     *     duplicate_rows:int,
     *     errors:array<int,string>,
     *     warnings:array<int,string>,
     *     error_rows:array<int,array{row:int,error:string,data:array<string,mixed>}>,
     *     duplicate_row_details:array<int,array{row:int,key:string,data:array<string,mixed>}>,
     *     mapping:array<string,string>,
     *     idempotency_keys:array<int,string>,
     * } $result
     * @param array<string,mixed> $context
     */
    public function createBatchLog(
        string $module,
        ?User $user,
        string $originalFilename,
        array $result,
        array $context = []
    ): ImportBatch {
        $status = $result['failed_rows'] > 0 ? 'completed_with_errors' : 'completed';

        $batch = ImportBatch::create([
            'user_id' => $user?->id,
            'module' => $module,
            'status' => $status,
            'original_filename' => $originalFilename,
            'field_mapping' => $result['mapping'],
            'context' => $context,
            'total_rows' => $result['total_rows'],
            'processed_rows' => $result['processed'],
            'success_rows' => $result['success_rows'],
            'failed_rows' => $result['failed_rows'],
            'created_rows' => $result['created'],
            'updated_rows' => $result['updated'],
            'duplicate_rows' => $result['duplicate_rows'] ?? 0,
            'error_count' => count($result['error_rows']),
        ]);

        $this->attachIdempotencyRowsToBatch($module, $result['idempotency_keys'] ?? [], $batch);

        $reportPath = $this->storeBatchReport($batch, $result);
        $batch->update(['report_path' => $reportPath]);

        return $batch;
    }

    /**
     * @param array{
     *     total_rows:int,
     *     processed:int,
     *     created:int,
     *     updated:int,
     *     success_rows:int,
     *     failed_rows:int,
     *     duplicate_rows:int,
     *     error_rows:array<int,array{row:int,error:string,data:array<string,mixed>}>,
     *     duplicate_row_details:array<int,array{row:int,key:string,data:array<string,mixed>}>,
     *     mapping:array<string,string>,
     * } $result
     */
    private function storeBatchReport(ImportBatch $batch, array $result): string
    {
        $path = self::REPORT_DIR.'/'.$batch->module.'-'.$batch->id.'.csv';
        $absolutePath = Storage::path($path);
        $directory = dirname($absolutePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = fopen($absolutePath, 'w');
        fputcsv($handle, ['batch_id', $batch->id]);
        fputcsv($handle, ['module', $batch->module]);
        fputcsv($handle, ['status', $batch->status]);
        fputcsv($handle, ['original_filename', $batch->original_filename]);
        fputcsv($handle, ['created_at', now()->toDateTimeString()]);
        fputcsv($handle, []);
        fputcsv($handle, ['metric', 'value']);
        fputcsv($handle, ['total_rows', $result['total_rows']]);
        fputcsv($handle, ['processed_rows', $result['processed']]);
        fputcsv($handle, ['success_rows', $result['success_rows']]);
        fputcsv($handle, ['failed_rows', $result['failed_rows']]);
        fputcsv($handle, ['created_rows', $result['created']]);
        fputcsv($handle, ['updated_rows', $result['updated']]);
        fputcsv($handle, ['duplicate_rows', $result['duplicate_rows'] ?? 0]);
        fputcsv($handle, []);

        fputcsv($handle, ['mapping_header_key', 'field_name']);
        foreach ($result['mapping'] as $header => $field) {
            fputcsv($handle, [$header, $field]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['row', 'error', 'row_data_json']);
        foreach ($result['error_rows'] as $errorRow) {
            fputcsv($handle, [
                $errorRow['row'],
                $errorRow['error'],
                json_encode($errorRow['data'], JSON_UNESCAPED_UNICODE),
            ]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['duplicate_row', 'idempotency_key', 'row_data_json']);
        foreach ($result['duplicate_row_details'] ?? [] as $duplicateRow) {
            fputcsv($handle, [
                $duplicateRow['row'],
                $duplicateRow['key'],
                json_encode($duplicateRow['data'], JSON_UNESCAPED_UNICODE),
            ]);
        }

        fclose($handle);

        return $path;
    }

    /**
     * @return array{
     *     headers:array<int,array{raw:string,key:string,base:string,index:int}>,
     *     preview_rows:array<int,array<int,string>>,
     *     total_rows:int
     * }
     */
    private function analyzeCsv(string $path, int $previewLimit): array
    {
        $headers = [];
        $previewRows = [];
        $totalRows = 0;
        $headerCaptured = false;

        $handle = new \SplFileObject(Storage::path($path));
        $handle->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        foreach ($handle as $row) {
            if (!is_array($row)) {
                continue;
            }

            if ($this->isRowEmpty($row)) {
                continue;
            }

            if (!$headerCaptured) {
                $headers = $this->buildHeaderMetadata($row);
                $headerCaptured = true;
                continue;
            }

            $totalRows++;
            if ($previewLimit > 0 && count($previewRows) < $previewLimit) {
                $previewRows[] = array_map(fn ($value) => (string) ($value ?? ''), $row);
            }
        }

        return [
            'headers' => $headers,
            'preview_rows' => $previewRows,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * @param array<int,mixed> $rawHeaders
     * @return array<int,array{raw:string,key:string,base:string,index:int}>
     */
    private function buildHeaderMetadata(array $rawHeaders): array
    {
        $metadata = [];
        $seen = [];

        foreach ($rawHeaders as $index => $header) {
            $raw = ltrim(trim((string) ($header ?? '')), "\xEF\xBB\xBF");
            $base = $this->normalizeHeader($raw);
            if ($base === '') {
                $base = 'column_'.($index + 1);
            }

            $count = ($seen[$base] ?? 0) + 1;
            $seen[$base] = $count;
            $key = $count === 1 ? $base : $base.'_'.$count;

            $metadata[] = [
                'raw' => $raw !== '' ? $raw : 'Column '.($index + 1),
                'key' => $key,
                'base' => $base,
                'index' => (int) $index,
            ];
        }

        return $metadata;
    }

    /**
     * @param array<string,array{label:string,aliases?:array<int,string>}> $fieldDefinitions
     */
    private function suggestField(string $headerBase, array $fieldDefinitions): ?string
    {
        foreach ($fieldDefinitions as $field => $definition) {
            $aliases = array_map(
                fn ($item) => $this->normalizeHeader((string) $item),
                $definition['aliases'] ?? []
            );
            $candidates = array_unique(array_merge([$this->normalizeHeader($field)], $aliases));

            if (in_array($headerBase, $candidates, true)) {
                return $field;
            }
        }

        return null;
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)
            ->lower()
            ->trim()
            ->replace([' ', '-'], '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->toString();
    }

    /**
     * @param array<int,mixed> $row
     */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeCellValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param array{
     *     module?:string,
     *     idempotency_key_resolver?:callable(array<string,mixed>,int): ?string
     * } $options
     * @return array{
     *     module:string,
     *     resolver:callable(array<string,mixed>,int): ?string
     * }|null
     */
    private function resolveIdempotencyConfig(array $options): ?array
    {
        $module = trim((string) ($options['module'] ?? ''));
        $resolver = $options['idempotency_key_resolver'] ?? null;

        if ($module === '' || !is_callable($resolver)) {
            return null;
        }

        return [
            'module' => $module,
            'resolver' => $resolver,
        ];
    }

    /**
     * @param array<string,mixed> $mappedData
     * @param array{
     *     module:string,
     *     resolver:callable(array<string,mixed>,int): ?string
     * }|null $config
     * @return array{key:string,raw_key:string,hash:string}|null
     */
    private function resolveRowIdempotency(array $mappedData, int $rowNumber, ?array $config): ?array
    {
        if ($config === null) {
            return null;
        }

        $rawKey = trim((string) call_user_func($config['resolver'], $mappedData, $rowNumber));
        if ($rawKey === '') {
            return null;
        }

        return [
            'key' => hash('sha256', $rawKey),
            'raw_key' => $rawKey,
            'hash' => $this->rowHash($mappedData),
        ];
    }

    private function isDuplicateByIdempotency(string $module, string $idempotencyKey, string $rowHash): bool
    {
        $existing = ImportRowIdempotency::query()
            ->where('module', $module)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (!$existing) {
            return false;
        }

        return hash_equals((string) $existing->row_hash, $rowHash);
    }

    private function persistIdempotency(string $module, string $idempotencyKey, string $rowHash): void
    {
        ImportRowIdempotency::updateOrCreate(
            [
                'module' => $module,
                'idempotency_key' => $idempotencyKey,
            ],
            [
                'row_hash' => $rowHash,
                'last_imported_at' => now(),
            ]
        );
    }

    /**
     * @param array<int,string> $idempotencyKeys
     */
    private function attachIdempotencyRowsToBatch(string $module, array $idempotencyKeys, ImportBatch $batch): void
    {
        if ($idempotencyKeys === []) {
            return;
        }

        ImportRowIdempotency::query()
            ->where('module', $module)
            ->whereIn('idempotency_key', $idempotencyKeys)
            ->update(['last_import_batch_id' => $batch->id]);
    }

    /**
     * @param array<string,mixed> $mappedData
     */
    private function rowHash(array $mappedData): string
    {
        ksort($mappedData);
        $payload = json_encode($mappedData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            $payload = serialize($mappedData);
        }

        return hash('sha256', $payload);
    }
}
