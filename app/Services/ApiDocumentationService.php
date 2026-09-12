<?php

namespace App\Services;

use Illuminate\Support\Str;

class ApiDocumentationService
{
    public function openApiV1(): array
    {
        $baseUrl = rtrim((string) config('app.url'), '/').'/api/v1';

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Platform Keperawatan API v1',
                'description' => 'API ingestion untuk data lulusan, sertifikasi, dan legalitas.',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => $baseUrl,
                    'description' => 'Primary',
                ],
            ],
            'tags' => [
                ['name' => 'Lulusan'],
                ['name' => 'Sertifikasi'],
                ['name' => 'Legalitas'],
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-KEY',
                    ],
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                ],
                'schemas' => [
                    'IngestSummaryResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'processed' => ['type' => 'integer', 'example' => 1],
                            'created' => ['type' => 'integer', 'example' => 1],
                            'updated' => ['type' => 'integer', 'example' => 0],
                            'errors' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'example' => [],
                            ],
                        ],
                    ],
                    'ErrorResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string', 'example' => 'Unauthorized'],
                        ],
                    ],
                    'LulusanPayload' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['nik', 'nama'],
                                    'properties' => [
                                        'nik' => ['type' => 'string', 'example' => '1234567890123456'],
                                        'nama' => ['type' => 'string', 'example' => 'Siti Aminah'],
                                        'email' => ['type' => 'string', 'nullable' => true, 'example' => 'siti@example.com'],
                                        'no_hp' => ['type' => 'string', 'nullable' => true, 'example' => '08123456789'],
                                        'jenis_kelamin' => ['type' => 'string', 'nullable' => true, 'enum' => ['L', 'P']],
                                        'tempat_lahir' => ['type' => 'string', 'nullable' => true, 'example' => 'Bandung'],
                                        'tanggal_lahir' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                                        'alamat' => ['type' => 'string', 'nullable' => true],
                                        'jenjang' => ['type' => 'string', 'nullable' => true, 'example' => 'S1'],
                                        'program_studi' => ['type' => 'string', 'nullable' => true, 'example' => 'Keperawatan'],
                                        'nomor_ijazah' => ['type' => 'string', 'nullable' => true, 'example' => 'IJZ-2024-0001'],
                                        'tanggal_ijazah' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                                        'tahun_masuk' => ['type' => 'string', 'nullable' => true, 'example' => '2020'],
                                        'tahun_lulus' => ['type' => 'string', 'nullable' => true, 'example' => '2024'],
                                        'ipk' => ['type' => 'number', 'nullable' => true, 'format' => 'float', 'minimum' => 0, 'maximum' => 4],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'SertifikasiPayload' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['nik', 'nama_sertifikat'],
                                    'properties' => [
                                        'nik' => ['type' => 'string', 'example' => '1234567890123456'],
                                        'nama_sertifikat' => ['type' => 'string', 'example' => 'Pelatihan ICU'],
                                        'nomor_sertifikat' => ['type' => 'string', 'nullable' => true, 'example' => 'CERT-ICU-001'],
                                        'tanggal_terbit' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                                        'tanggal_kadaluarsa' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                                        'status_verifikasi' => ['type' => 'string', 'nullable' => true, 'enum' => ['pending', 'verified', 'rejected', 'valid']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'LegalitasPayload' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['nik'],
                                    'properties' => [
                                        'nik' => ['type' => 'string', 'example' => '1234567890123456'],
                                        'nomor_str' => ['type' => 'string', 'nullable' => true, 'example' => 'STR-001'],
                                        'status_str' => ['type' => 'string', 'nullable' => true, 'enum' => ['aktif', 'hold', 'expired']],
                                        'str_berlaku_mulai' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                                        'str_berlaku_sampai' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                                        'nomor_izin_luar_negeri' => ['type' => 'string', 'nullable' => true, 'example' => 'LN-001'],
                                        'status_izin_luar_negeri' => ['type' => 'string', 'nullable' => true, 'example' => 'aktif'],
                                        'sumber_validasi' => ['type' => 'string', 'nullable' => true, 'example' => 'api'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/lulusan' => $this->buildPostPath(
                    'Lulusan',
                    'Ingest data lulusan baru',
                    'lulusan:write',
                    '#/components/schemas/LulusanPayload'
                ),
                '/sertifikasi' => $this->buildPostPath(
                    'Sertifikasi',
                    'Ingest data sertifikasi kompetensi',
                    'sertifikasi:write',
                    '#/components/schemas/SertifikasiPayload'
                ),
                '/legalitas' => $this->buildPostPath(
                    'Legalitas',
                    'Ingest data legalitas perawat',
                    'legalitas:write',
                    '#/components/schemas/LegalitasPayload'
                ),
            ],
        ];
    }

    public function postmanCollectionV1(): array
    {
        return [
            'info' => [
                '_postman_id' => (string) Str::uuid(),
                'name' => 'Platform Keperawatan API v1',
                'description' => 'Collection untuk ingestion API v1.',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => rtrim((string) config('app.url'), '/').'/api/v1',
                ],
                [
                    'key' => 'api_key',
                    'value' => '',
                ],
                [
                    'key' => 'timestamp',
                    'value' => '',
                ],
                [
                    'key' => 'signature',
                    'value' => '',
                ],
                [
                    'key' => 'idempotency_key',
                    'value' => 'req-{{timestamp}}',
                ],
            ],
            'item' => [
                $this->buildPostmanItem('POST Lulusan', '/lulusan', ['data' => [[
                    'nik' => '1234567890123456',
                    'nama' => 'Siti Aminah',
                ]]]),
                $this->buildPostmanItem('POST Sertifikasi', '/sertifikasi', ['data' => [[
                    'nik' => '1234567890123456',
                    'nama_sertifikat' => 'Pelatihan ICU',
                ]]]),
                $this->buildPostmanItem('POST Legalitas', '/legalitas', ['data' => [[
                    'nik' => '1234567890123456',
                    'nomor_str' => 'STR-001',
                    'status_str' => 'aktif',
                ]]]),
            ],
        ];
    }

    private function buildPostPath(string $tag, string $summary, string $scope, string $schemaRef): array
    {
        return [
            'post' => [
                'tags' => [$tag],
                'summary' => $summary,
                'description' => "Scope required: {$scope}",
                'security' => [
                    ['ApiKeyAuth' => []],
                    ['BearerAuth' => []],
                ],
                'parameters' => [
                    [
                        'in' => 'header',
                        'name' => 'X-API-TIMESTAMP',
                        'required' => false,
                        'schema' => ['type' => 'integer'],
                        'description' => 'Unix timestamp. Wajib jika API key dikonfigurasi HMAC.',
                    ],
                    [
                        'in' => 'header',
                        'name' => 'X-API-SIGNATURE',
                        'required' => false,
                        'schema' => ['type' => 'string'],
                        'description' => 'HMAC SHA256 dari "{timestamp}.{raw_body}".',
                    ],
                    [
                        'in' => 'header',
                        'name' => 'Idempotency-Key',
                        'required' => false,
                        'schema' => ['type' => 'string', 'maxLength' => 120],
                        'description' => 'Opsional. Cegah duplikasi request POST dengan key unik per payload.',
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => $schemaRef,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/IngestSummaryResponse',
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Unauthorized',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                            ],
                        ],
                    ],
                    '403' => [
                        'description' => 'Forbidden',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                            ],
                        ],
                    ],
                    '422' => [
                        'description' => 'Validation error',
                    ],
                    '409' => [
                        'description' => 'Idempotency conflict',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildPostmanItem(string $name, string $path, array $body): array
    {
        return [
            'name' => $name,
            'request' => [
                'method' => 'POST',
                'header' => [
                    ['key' => 'Content-Type', 'value' => 'application/json'],
                    ['key' => 'X-API-KEY', 'value' => '{{api_key}}'],
                    ['key' => 'Idempotency-Key', 'value' => '{{idempotency_key}}'],
                    ['key' => 'X-API-TIMESTAMP', 'value' => '{{timestamp}}'],
                    ['key' => 'X-API-SIGNATURE', 'value' => '{{signature}}'],
                ],
                'url' => [
                    'raw' => '{{base_url}}'.$path,
                    'host' => ['{{base_url}}'],
                    'path' => array_values(array_filter(explode('/', trim($path, '/')))),
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ],
            ],
            'response' => [],
        ];
    }
}
