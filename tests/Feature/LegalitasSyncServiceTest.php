<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Services\LegalitasSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LegalitasSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_sync_marks_expired_when_date_passed(): void
    {
        Carbon::setTestNow('2026-02-08');

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-001',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'str_berlaku_sampai' => Carbon::parse('2026-02-01'),
        ]);

        app(LegalitasSyncService::class)->sync();

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::STATUS_STR_EXPIRED, $legalitas->status_str);
        $this->assertSame('sync_stub', $legalitas->sumber_validasi);
        $this->assertSame(DataLegalitas::DATA_STATUS_EXPIRED, $legalitas->data_status);
    }

    public function test_sync_sets_hold_when_no_str(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => null,
            'status_str' => null,
        ]);

        app(LegalitasSyncService::class)->sync();

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::STATUS_STR_HOLD, $legalitas->status_str);
    }

    public function test_sync_promotes_pending_sync_to_verified(): void
    {
        Carbon::setTestNow('2026-02-08');

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-003',
            'status_str' => null,
            'str_berlaku_sampai' => Carbon::parse('2027-01-01'),
            'data_status' => DataLegalitas::DATA_STATUS_PENDING_SYNC,
        ]);

        app(LegalitasSyncService::class)->sync();

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::STATUS_STR_AKTIF, $legalitas->status_str);
        $this->assertSame(DataLegalitas::DATA_STATUS_VERIFIED, $legalitas->data_status);
    }

    public function test_sync_marks_active_when_date_future(): void
    {
        Carbon::setTestNow('2026-02-08');

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-002',
            'status_str' => DataLegalitas::STATUS_STR_EXPIRED,
            'str_berlaku_sampai' => Carbon::parse('2027-01-01'),
        ]);

        app(LegalitasSyncService::class)->sync();

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::STATUS_STR_AKTIF, $legalitas->status_str);
        $this->assertSame(DataLegalitas::DATA_STATUS_VERIFIED, $legalitas->data_status);
    }
}
