<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\InstitusiPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CsvImportIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function attachUserToInstitusi(User $user, InstitusiPendidikan $institusi): void
    {
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $institusi->id,
            'organisasiable_type' => InstitusiPendidikan::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function importLulusanCsv(User $user, InstitusiPendidikan $institusi, string $csv): ImportBatch
    {
        $previewResponse = $this->actingAs($user)->post(route('kampus.lulusan.import.store'), [
            'action' => 'preview',
            'institusi_pendidikan_id' => $institusi->id,
            'file' => UploadedFile::fake()->createWithContent('lulusan.csv', $csv),
        ]);
        $previewResponse->assertOk();

        $preview = $previewResponse->viewData('preview');
        $mapping = [];
        foreach ($preview['headers'] as $header) {
            if (!empty($header['mapped_field'])) {
                $mapping[$header['key']] = $header['mapped_field'];
            }
        }

        $this->actingAs($user)->post(route('kampus.lulusan.import.store'), [
            'action' => 'import',
            'institusi_pendidikan_id' => $institusi->id,
            'temp_file' => $preview['temp_file'],
            'original_filename' => $preview['original_filename'],
            'mapping' => $mapping,
        ])->assertRedirect(route('kampus.lulusan.import'));

        return ImportBatch::query()
            ->where('module', 'lulusan')
            ->latest('created_at')
            ->firstOrFail();
    }

    public function test_duplicate_rows_are_skipped_with_row_level_idempotency(): void
    {
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $user = User::factory()->create(['role' => User::ROLE_KAMPUS]);
        $this->attachUserToInstitusi($user, $institusi);

        $csv = implode("\n", [
            'nik,nama,ipk',
            '1234567890123456,Nursi,3.75',
        ]);

        $firstBatch = $this->importLulusanCsv($user, $institusi, $csv);
        $this->assertSame(0, $firstBatch->duplicate_rows);
        $this->assertSame(1, $firstBatch->processed_rows);

        $secondBatch = $this->importLulusanCsv($user, $institusi, $csv);
        $this->assertSame(1, $secondBatch->duplicate_rows);
        $this->assertSame(0, $secondBatch->processed_rows);
        $this->assertSame(0, $secondBatch->failed_rows);
        $this->assertSame(0, $secondBatch->updated_rows);

        $this->assertDatabaseCount('perawat', 1);
        $this->assertDatabaseCount('riwayat_pendidikan', 1);
    }

    public function test_same_idempotency_key_but_changed_payload_is_processed_as_update(): void
    {
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $user = User::factory()->create(['role' => User::ROLE_KAMPUS]);
        $this->attachUserToInstitusi($user, $institusi);

        $firstCsv = implode("\n", [
            'nik,nama,ipk',
            '1234567890123456,Nursi,3.10',
        ]);
        $this->importLulusanCsv($user, $institusi, $firstCsv);

        $updatedCsv = implode("\n", [
            'nik,nama,ipk',
            '1234567890123456,Nursi,3.90',
        ]);
        $secondBatch = $this->importLulusanCsv($user, $institusi, $updatedCsv);

        $this->assertSame(0, $secondBatch->duplicate_rows);
        $this->assertSame(1, $secondBatch->processed_rows);
        $this->assertSame(1, $secondBatch->updated_rows);

        $this->assertDatabaseHas('riwayat_pendidikan', [
            'perawat_nik' => '1234567890123456',
            'institusi_pendidikan_id' => $institusi->id,
            'ipk' => '3.90',
        ]);
    }
}
