<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\InstitusiPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class KampusCsvImportWorkflowTest extends TestCase
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

    public function test_kampus_can_preview_import_and_download_batch_report(): void
    {
        Storage::fake('local');

        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $user = User::factory()->create(['role' => User::ROLE_KAMPUS]);
        $this->attachUserToInstitusi($user, $institusi);

        $csv = implode("\n", [
            'nomor_ktp,nama_lengkap,email,ipk',
            '1234567890123456,Nursi,nursi@example.com,3.75',
            '1234567890123457,Siti,siti@example.com,4.50',
        ]);

        $previewResponse = $this->actingAs($user)->post(route('kampus.lulusan.import.store'), [
            'action' => 'preview',
            'institusi_pendidikan_id' => $institusi->id,
            'file' => UploadedFile::fake()->createWithContent('lulusan.csv', $csv),
        ]);

        $previewResponse->assertOk();
        $preview = $previewResponse->viewData('preview');

        $this->assertIsArray($preview);
        $this->assertArrayHasKey('temp_file', $preview);
        $this->assertSame(2, $preview['total_rows']);

        $mapping = [];
        foreach ($preview['headers'] as $header) {
            if (!empty($header['mapped_field'])) {
                $mapping[$header['key']] = $header['mapped_field'];
            }
        }

        $importResponse = $this->actingAs($user)->post(route('kampus.lulusan.import.store'), [
            'action' => 'import',
            'institusi_pendidikan_id' => $institusi->id,
            'temp_file' => $preview['temp_file'],
            'original_filename' => $preview['original_filename'],
            'mapping' => $mapping,
        ]);

        $importResponse->assertRedirect(route('kampus.lulusan.import'));
        $this->assertDatabaseHas('perawat', [
            'nik' => '1234567890123456',
            'nama' => 'Nursi',
        ]);
        $this->assertDatabaseMissing('perawat', [
            'nik' => '1234567890123457',
        ]);
        $this->assertDatabaseHas('riwayat_pendidikan', [
            'perawat_nik' => '1234567890123456',
            'institusi_pendidikan_id' => $institusi->id,
            'status_verifikasi' => 'verified',
            'source_system' => 'kampus_csv',
            'source_actor' => 'kampus',
        ]);

        $batch = ImportBatch::query()->where('module', 'lulusan')->first();
        $this->assertNotNull($batch);
        $this->assertSame(2, $batch->total_rows);
        $this->assertSame(1, $batch->failed_rows);
        $this->assertNotEmpty($batch->report_path);
        Storage::disk('local')->assertExists($batch->report_path);

        $downloadResponse = $this->actingAs($user)
            ->get(route('kampus.lulusan.import.logs.download', $batch));

        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('content-disposition');
    }
}
