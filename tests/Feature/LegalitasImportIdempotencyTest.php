<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\Perawat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LegalitasImportIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function importLegalitasCsv(User $user, string $csv): ImportBatch
    {
        $previewResponse = $this->actingAs($user)->post(route('regulator.legalitas.import.store'), [
            'action' => 'preview',
            'file' => UploadedFile::fake()->createWithContent('legalitas.csv', $csv),
        ]);
        $previewResponse->assertOk();

        $preview = $previewResponse->viewData('preview');
        $mapping = [];
        foreach ($preview['headers'] as $header) {
            if (!empty($header['mapped_field'])) {
                $mapping[$header['key']] = $header['mapped_field'];
            }
        }

        $this->actingAs($user)->post(route('regulator.legalitas.import.store'), [
            'action' => 'import',
            'temp_file' => $preview['temp_file'],
            'original_filename' => $preview['original_filename'],
            'mapping' => $mapping,
        ])->assertRedirect(route('regulator.legalitas.import'));

        return ImportBatch::query()
            ->where('module', 'legalitas')
            ->latest('created_at')
            ->firstOrFail();
    }

    public function test_duplicate_legalitas_rows_are_skipped_with_row_idempotency(): void
    {
        Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Nursi',
        ]);

        $user = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $csv = implode("\n", [
            'nik,nomor_str,status_str,str_berlaku_mulai,str_berlaku_sampai,sumber_validasi',
            '1234567890123456,STR-001,aktif,2026-01-01,2027-01-01,regulator',
        ]);

        $firstBatch = $this->importLegalitasCsv($user, $csv);
        $this->assertSame(0, $firstBatch->duplicate_rows);
        $this->assertSame(1, $firstBatch->processed_rows);

        $secondBatch = $this->importLegalitasCsv($user, $csv);
        $this->assertSame(1, $secondBatch->duplicate_rows);
        $this->assertSame(0, $secondBatch->processed_rows);
        $this->assertSame(0, $secondBatch->failed_rows);
        $this->assertSame(0, $secondBatch->updated_rows);

        $this->assertDatabaseCount('data_legalitas', 1);
    }

    public function test_changed_legalitas_payload_is_processed_as_update(): void
    {
        Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Nursi',
        ]);

        $user = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $firstCsv = implode("\n", [
            'nik,nomor_str,status_str,str_berlaku_mulai,str_berlaku_sampai,sumber_validasi',
            '1234567890123456,STR-001,aktif,2026-01-01,2027-01-01,regulator',
        ]);
        $this->importLegalitasCsv($user, $firstCsv);

        $updatedCsv = implode("\n", [
            'nik,nomor_str,status_str,str_berlaku_mulai,str_berlaku_sampai,sumber_validasi',
            '1234567890123456,STR-001,expired,2026-01-01,2026-01-31,regulator',
        ]);
        $secondBatch = $this->importLegalitasCsv($user, $updatedCsv);

        $this->assertSame(0, $secondBatch->duplicate_rows);
        $this->assertSame(1, $secondBatch->processed_rows);
        $this->assertSame(1, $secondBatch->updated_rows);

        $this->assertDatabaseHas('data_legalitas', [
            'perawat_nik' => '1234567890123456',
            'status_str' => 'expired',
            'data_status' => 'expired',
        ]);
    }
}
