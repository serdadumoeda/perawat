<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DemoScenarioController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\IntegrationSettingController;
use App\Http\Controllers\Admin\IntegrationPlaygroundController;
use App\Http\Controllers\Admin\InstitusiPendidikanController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\LembagaPelatihanController as AdminLembagaPelatihanController;
use App\Http\Controllers\Admin\MitraPenggunaController;
use App\Http\Controllers\Admin\OrganisasiUserController;
use App\Http\Controllers\Admin\RegulatorOrganisasiController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\WebhookSubscriptionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DataQualityController;
use App\Http\Controllers\Kampus\ForecastingController as KampusForecastingController;
use App\Http\Controllers\Kampus\GatewayController as KampusGatewayController;
use App\Http\Controllers\Kampus\LulusanController;
use App\Http\Controllers\Kampus\TracingAlumniController;


use App\Http\Controllers\Lembaga\DashboardController as LembagaDashboardController;
use App\Http\Controllers\Lembaga\PelatihanController as LembagaPelatihanController;
use App\Http\Controllers\Lembaga\SertifikasiController;
use App\Http\Controllers\Mitra\ExperienceSyncSettingController;
use App\Http\Controllers\Mitra\ExperienceSyncLogController;
use App\Http\Controllers\Mitra\KandidatController;
use App\Http\Controllers\Mitra\LamaranController as MitraLamaranController;
use App\Http\Controllers\Mitra\LowonganController;
use App\Http\Controllers\Mitra\NotifikasiController as MitraNotifikasiController;
use App\Http\Controllers\Mitra\RiwayatPekerjaanController;
use App\Http\Controllers\Perawat\LamaranController as PerawatLamaranController;
use App\Http\Controllers\Perawat\LegalitasAuditController;
use App\Http\Controllers\Perawat\LowonganController as PerawatLowonganController;
use App\Http\Controllers\Perawat\NotifikasiController;
use App\Http\Controllers\Perawat\PelatihanController as PerawatPelatihanController;
use App\Http\Controllers\Perawat\ProfilController;
use App\Http\Controllers\Perawat\RiwayatPekerjaanController as PerawatRiwayatPekerjaanController;
use App\Http\Controllers\Perawat\StrSyncController as PerawatStrSyncController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Regulator\LegalitasController;
use App\Http\Controllers\Regulator\LegalitasDataController;
use App\Http\Controllers\Regulator\LegalitasDocumentController;
use App\Http\Controllers\Regulator\AnalyticsController as RegulatorAnalyticsController;
use App\Http\Controllers\Regulator\StrSyncController as RegulatorStrSyncController;
use App\Http\Controllers\PublicLowonganController;
use App\Http\Controllers\PublicPerawatController;
use App\Http\Controllers\Api\ApiDocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lowongan', [PublicLowonganController::class, 'index'])->middleware('throttle:60,1')->name('public.lowongan.index');
Route::get('/lowongan/{lowongan}', [PublicLowonganController::class, 'show'])->middleware('throttle:60,1')->name('public.lowongan.show');
Route::get('/p/{token}', [PublicPerawatController::class, 'show'])->middleware('throttle:30,1')->name('public.perawat.show');
Route::get('/p/{token}/cv', [PublicPerawatController::class, 'cv'])->middleware('throttle:30,1')->name('public.perawat.cv');
Route::get('/p/{token}/cv-pdf', [PublicPerawatController::class, 'cvPdf'])->middleware('throttle:30,1')->name('public.perawat.cv_pdf');
Route::get('/api/v1/docs', [ApiDocumentationController::class, 'swagger'])->name('api.v1.swagger');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonation.stop');
    Route::get('/pending-approval', function (\Illuminate\Http\Request $request) {
        if ($request->user()->status !== \App\Models\User::STATUS_PENDING) {
            return redirect()->route('dashboard');
        }
        return view('auth.pending-approval');
    })->name('pending_approval');
});


Route::middleware(['auth', 'verified', 'role:kampus'])->group(function () {
    Route::get('/kampus/gateway', [KampusGatewayController::class, 'index'])->name('kampus.gateway');
    Route::post('/kampus/gateway/lookup', [KampusGatewayController::class, 'lookup'])->name('kampus.gateway.lookup');
    Route::post('/kampus/gateway/ingest', [KampusGatewayController::class, 'ingest'])->name('kampus.gateway.ingest');
    Route::get('/kampus/forecasting', [KampusForecastingController::class, 'index'])->name('kampus.forecasting');
});


Route::middleware(['auth', 'verified', 'role:kampus'])->prefix('kampus')->name('kampus.')->group(function () {
    Route::get('lulusan', [LulusanController::class, 'index'])->name('lulusan.index');
    Route::get('lulusan/create', [LulusanController::class, 'create'])->name('lulusan.create');
    Route::post('lulusan', [LulusanController::class, 'store'])->name('lulusan.store');
    Route::get('lulusan/import', [LulusanController::class, 'importForm'])->name('lulusan.import');
    Route::post('lulusan/import', [LulusanController::class, 'import'])->name('lulusan.import.store');
    Route::get('lulusan/import/logs/{importBatch}/download', [LulusanController::class, 'downloadImportLog'])->name('lulusan.import.logs.download');
    Route::get('lulusan/template', [LulusanController::class, 'template'])->name('lulusan.template');
    Route::get('lulusan/{perawat}/edit', [LulusanController::class, 'edit'])->name('lulusan.edit');
    Route::put('lulusan/{perawat}', [LulusanController::class, 'update'])->name('lulusan.update');
    Route::get('tracing-alumni', [TracingAlumniController::class, 'index'])->name('tracing.index');
    Route::get('tracing-alumni/{perawat}', [TracingAlumniController::class, 'edit'])->name('tracing.edit');
    Route::put('tracing-alumni/{perawat}', [TracingAlumniController::class, 'update'])->name('tracing.update');
    Route::get('tracing-analytics', [TracingAlumniController::class, 'analytics'])->name('tracing.analytics');
    Route::get('forecasting', [KampusForecastingController::class, 'index'])->name('forecasting');
});




Route::middleware(['auth', 'verified', 'role:lembaga'])->prefix('lembaga')->name('lembaga.')->group(function () {
    Route::get('dashboard', [LembagaDashboardController::class, 'index'])->name('dashboard');
    Route::get('sertifikasi/create', [SertifikasiController::class, 'create'])->name('sertifikasi.create');
    Route::post('sertifikasi', [SertifikasiController::class, 'store'])->name('sertifikasi.store');
    Route::get('sertifikasi/import', [SertifikasiController::class, 'importForm'])->name('sertifikasi.import');
    Route::post('sertifikasi/import', [SertifikasiController::class, 'import'])->name('sertifikasi.import.store');
    Route::get('sertifikasi/import/logs/{importBatch}/download', [SertifikasiController::class, 'downloadImportLog'])->name('sertifikasi.import.logs.download');
    Route::get('sertifikasi/template', [SertifikasiController::class, 'template'])->name('sertifikasi.template');
    Route::get('sertifikasi/pending', [SertifikasiController::class, 'pending'])->name('sertifikasi.pending');
    Route::post('sertifikasi/{sertifikasi}/verify', [SertifikasiController::class, 'verify'])->name('sertifikasi.verify');
    Route::post('sertifikasi/{sertifikasi}/reject', [SertifikasiController::class, 'reject'])->name('sertifikasi.reject');

    Route::get('pelatihan', [LembagaPelatihanController::class, 'index'])->name('pelatihan.index');
    Route::get('pelatihan/create', [LembagaPelatihanController::class, 'create'])->name('pelatihan.create');
    Route::post('pelatihan', [LembagaPelatihanController::class, 'store'])->name('pelatihan.store');
    Route::get('pelatihan/{pelatihan}', [LembagaPelatihanController::class, 'show'])->name('pelatihan.show');
    Route::post('pelatihan/{pelatihan}/close', [LembagaPelatihanController::class, 'close'])->name('pelatihan.close');
    Route::post('pelatihan/{pelatihan}/peserta/{peserta}/lulus', [LembagaPelatihanController::class, 'markLulus'])->name('pelatihan.peserta.lulus');
    Route::post('pelatihan/{pelatihan}/peserta/{peserta}/tidak-lulus', [LembagaPelatihanController::class, 'markTidakLulus'])->name('pelatihan.peserta.tidak_lulus');
});

Route::middleware(['auth', 'verified', 'role:regulator'])->prefix('regulator')->name('regulator.')->group(function () {
    Route::get('analytics', [RegulatorAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('legalitas/create', [LegalitasController::class, 'create'])->name('legalitas.create');
    Route::post('legalitas', [LegalitasController::class, 'upsert'])->name('legalitas.upsert');
    Route::get('legalitas/import', [LegalitasController::class, 'importForm'])->name('legalitas.import');
    Route::post('legalitas/import', [LegalitasController::class, 'import'])->name('legalitas.import.store');
    Route::get('legalitas/import/logs/{importBatch}/download', [LegalitasController::class, 'downloadImportLog'])->name('legalitas.import.logs.download');
    Route::get('legalitas/template', [LegalitasController::class, 'template'])->name('legalitas.template');
    Route::get('legalitas/data', [LegalitasDataController::class, 'index'])->name('legalitas.data.index');
    Route::patch('legalitas/data/{legalitas}', [LegalitasDataController::class, 'review'])->name('legalitas.data.review');
    Route::patch('legalitas/data/{legalitas}/reopen', [LegalitasDataController::class, 'reopen'])->name('legalitas.data.reopen');
    Route::get('legalitas/documents', [LegalitasDocumentController::class, 'index'])->name('legalitas.documents');
    Route::patch('legalitas/documents/{legalitas}', [LegalitasDocumentController::class, 'review'])->name('legalitas.documents.review');
    Route::patch('legalitas/documents/{legalitas}/reopen', [LegalitasDocumentController::class, 'reopen'])->name('legalitas.documents.reopen');
    Route::get('legalitas/documents/{legalitas}/audit', [LegalitasDocumentController::class, 'audit'])->name('legalitas.documents.audit');
    Route::get('str-sync', [RegulatorStrSyncController::class, 'index'])->name('str_sync.index');
    Route::post('str-sync/sync-all', [RegulatorStrSyncController::class, 'syncAll'])->name('str_sync.sync_all');
    Route::post('str-sync/{syncRequest}/sync', [RegulatorStrSyncController::class, 'sync'])->name('str_sync.sync');
    Route::post('str-sync/{syncRequest}/reject', [RegulatorStrSyncController::class, 'reject'])->name('str_sync.reject');
});

Route::middleware(['auth', 'verified', 'role:mitra,faskes,headhunter'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('lowongan', [LowonganController::class, 'index'])->name('lowongan.index');
    Route::get('lowongan/create', [LowonganController::class, 'create'])->name('lowongan.create');
    Route::get('lowongan/{lowongan}', [LowonganController::class, 'show'])->name('lowongan.show');
    Route::post('lowongan', [LowonganController::class, 'store'])->name('lowongan.store');
    Route::get('lowongan/{lowongan}/edit', [LowonganController::class, 'edit'])->name('lowongan.edit');
    Route::put('lowongan/{lowongan}', [LowonganController::class, 'update'])->name('lowongan.update');
    Route::post('lowongan/{lowongan}/close', [LowonganController::class, 'close'])->name('lowongan.close');
    Route::post('lowongan/{lowongan}/reopen', [LowonganController::class, 'reopen'])->name('lowongan.reopen');
    Route::delete('lowongan/{lowongan}', [LowonganController::class, 'destroy'])->name('lowongan.destroy');

    Route::get('experience-sync', [ExperienceSyncSettingController::class, 'index'])->name('experience_sync.index');
    Route::patch('experience-sync', [ExperienceSyncSettingController::class, 'update'])->name('experience_sync.update');
    Route::get('experience-sync/logs', [ExperienceSyncLogController::class, 'index'])->name('experience_sync.logs');
    Route::post('experience-sync/logs/sync-now', [ExperienceSyncLogController::class, 'syncNow'])->name('experience_sync.sync_now');
    Route::get('experience-sync/logs/export', [ExperienceSyncLogController::class, 'export'])->name('experience_sync.export');

    Route::get('kandidat', [KandidatController::class, 'index'])->name('kandidat.index');
    Route::get('kandidat/{perawat}', [KandidatController::class, 'show'])->name('kandidat.show');
    Route::get('kandidat/{perawat}/cv-pdf', [KandidatController::class, 'cvPdf'])->name('kandidat.cv_pdf');
    Route::post('kandidat/lock', [KandidatController::class, 'lock'])->name('kandidat.lock');
    Route::post('kandidat/hire', [KandidatController::class, 'hire'])->name('kandidat.hire');
    Route::post('kandidat/release', [KandidatController::class, 'release'])->name('kandidat.release');

    Route::post('lamaran/process', [MitraLamaranController::class, 'process'])->name('lamaran.process');
    Route::post('lamaran/reject', [MitraLamaranController::class, 'reject'])->name('lamaran.reject');
    Route::post('lamaran/hire', [MitraLamaranController::class, 'hire'])->name('lamaran.hire');

    Route::get('notifikasi', [MitraNotifikasiController::class, 'index'])->name('notifikasi');

    Route::get('riwayat-pekerjaan/pending', [RiwayatPekerjaanController::class, 'pending'])->name('riwayat_pekerjaan.pending');
    Route::get('riwayat-pekerjaan/aktif', [RiwayatPekerjaanController::class, 'active'])->name('riwayat_pekerjaan.active');
    Route::get('riwayat-kinerja', [RiwayatPekerjaanController::class, 'kinerja'])->name('riwayat_kinerja.index');
    Route::get('riwayat-kinerja/export', [RiwayatPekerjaanController::class, 'exportKinerja'])->name('riwayat_kinerja.export');
    Route::get('riwayat-kinerja/{riwayat}/edit', [RiwayatPekerjaanController::class, 'editKinerja'])->name('riwayat_kinerja.edit');
    Route::put('riwayat-kinerja/{riwayat}', [RiwayatPekerjaanController::class, 'updateKinerja'])->name('riwayat_kinerja.update');
    Route::post('riwayat-pekerjaan/{riwayat}/verify', [RiwayatPekerjaanController::class, 'verify'])->name('riwayat_pekerjaan.verify');
    Route::post('riwayat-pekerjaan/{riwayat}/reject', [RiwayatPekerjaanController::class, 'reject'])->name('riwayat_pekerjaan.reject');
    Route::post('riwayat-pekerjaan/{riwayat}/complete', [RiwayatPekerjaanController::class, 'complete'])->name('riwayat_pekerjaan.complete');

    Route::get('riwayat-pekerjaan/create', [RiwayatPekerjaanController::class, 'create'])->name('riwayat_pekerjaan.create');
    Route::post('riwayat-pekerjaan', [RiwayatPekerjaanController::class, 'store'])->name('riwayat_pekerjaan.store');
});

Route::middleware(['auth', 'verified', 'role:perawat'])->prefix('perawat')->name('perawat.')->group(function () {
    Route::get('dashboard', [ProfilController::class, 'show'])->name('dashboard');
    Route::get('profil/edit', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::patch('profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::patch('profil/share', [ProfilController::class, 'updateShare'])->name('profil.share');
    Route::get('cv', [ProfilController::class, 'cv'])->name('cv');
    Route::get('cv-pdf', [ProfilController::class, 'cvPdf'])->name('cv.pdf');
    Route::get('legalitas/audit', [LegalitasAuditController::class, 'index'])->name('legalitas.audit');
    Route::post('legalitas/audit/mark-read', [LegalitasAuditController::class, 'markAllRead'])->name('legalitas.audit.read');
    Route::get('str-sync', [PerawatStrSyncController::class, 'create'])->name('str_sync.create');
    Route::post('str-sync', [PerawatStrSyncController::class, 'store'])->name('str_sync.store');
    Route::get('pelatihan', [PerawatPelatihanController::class, 'index'])->name('pelatihan.index');
    Route::get('pelatihan/saya', [PerawatPelatihanController::class, 'mine'])->name('pelatihan.mine');
    Route::post('pelatihan/daftar', [PerawatPelatihanController::class, 'daftar'])->name('pelatihan.daftar');
    Route::get('lowongan', [PerawatLowonganController::class, 'index'])->name('lowongan.index');
    Route::get('lowongan/{lowongan}', [PerawatLowonganController::class, 'show'])->name('lowongan.show');
    Route::post('lowongan/apply', [PerawatLowonganController::class, 'apply'])->name('lowongan.apply');
    Route::get('lamaran', [PerawatLamaranController::class, 'index'])->name('lamaran.index');
    Route::post('lamaran/{lamaran}/cancel', [PerawatLamaranController::class, 'cancel'])->name('lamaran.cancel');
    Route::get('riwayat-pekerjaan', [PerawatRiwayatPekerjaanController::class, 'index'])->name('riwayat_pekerjaan.index');
    Route::get('riwayat-pekerjaan/create', [PerawatRiwayatPekerjaanController::class, 'create'])->name('riwayat_pekerjaan.create');
    Route::post('riwayat-pekerjaan', [PerawatRiwayatPekerjaanController::class, 'store'])->middleware('throttle:10,1')->name('riwayat_pekerjaan.store');
    Route::get('riwayat-pekerjaan/{riwayatPekerjaan}/edit', [PerawatRiwayatPekerjaanController::class, 'edit'])->name('riwayat_pekerjaan.edit');
    Route::put('riwayat-pekerjaan/{riwayatPekerjaan}', [PerawatRiwayatPekerjaanController::class, 'update'])->middleware('throttle:10,1')->name('riwayat_pekerjaan.update');
    Route::delete('riwayat-pekerjaan/{riwayatPekerjaan}', [PerawatRiwayatPekerjaanController::class, 'destroy'])->name('riwayat_pekerjaan.destroy');
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('notifikasi/mark-read', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.read');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('data-quality', [DataQualityController::class, 'index'])->name('data_quality');
    Route::get('data-quality/export', [DataQualityController::class, 'exportCsv'])->name('data_quality.export');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
    Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset_password');
    Route::post('users/{user}/impersonate', [ImpersonationController::class, 'start'])->name('users.impersonate');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
    Route::get('integrations', [IntegrationSettingController::class, 'index'])->name('integrations.index');
    Route::patch('integrations/{provider}', [IntegrationSettingController::class, 'update'])->name('integrations.update');
    Route::get('integration-playground', [IntegrationPlaygroundController::class, 'index'])->name('integration_playground.index');
    Route::post('integration-playground/deliveries/{webhookDelivery}/retry', [IntegrationPlaygroundController::class, 'retryDeadLetter'])
        ->name('integration_playground.deliveries.retry');
    Route::get('demo-scenario', [DemoScenarioController::class, 'index'])->name('demo_scenario.index');
    Route::post('demo-scenario/run', [DemoScenarioController::class, 'store'])->name('demo_scenario.run');
    Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api_keys.index');
    Route::get('api-keys/create', [ApiKeyController::class, 'create'])->name('api_keys.create');
    Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api_keys.store');
    Route::post('api-keys/{apiKey}/revoke', [ApiKeyController::class, 'revoke'])->name('api_keys.revoke');
    Route::post('api-keys/{apiKey}/rotate-signature', [ApiKeyController::class, 'rotateSignature'])->name('api_keys.rotate_signature');
    Route::get('api-keys/postman-collection', [ApiKeyController::class, 'downloadPostmanCollection'])->name('api_keys.postman_collection');
    Route::post('webhook-subscriptions', [WebhookSubscriptionController::class, 'store'])->name('webhook_subscriptions.store');
    Route::patch('webhook-subscriptions/{webhookSubscription}', [WebhookSubscriptionController::class, 'update'])->name('webhook_subscriptions.update');
    Route::delete('webhook-subscriptions/{webhookSubscription}', [WebhookSubscriptionController::class, 'destroy'])->name('webhook_subscriptions.destroy');
    Route::get('institusi', [InstitusiPendidikanController::class, 'index'])->name('institusi.index');
    Route::get('institusi/create', [InstitusiPendidikanController::class, 'create'])->name('institusi.create');
    Route::post('institusi', [InstitusiPendidikanController::class, 'store'])->name('institusi.store');

    Route::get('lembaga', [AdminLembagaPelatihanController::class, 'index'])->name('lembaga.index');
    Route::get('lembaga/create', [AdminLembagaPelatihanController::class, 'create'])->name('lembaga.create');
    Route::post('lembaga', [AdminLembagaPelatihanController::class, 'store'])->name('lembaga.store');

    Route::get('mitra', [MitraPenggunaController::class, 'index'])->name('mitra.index');
    Route::get('mitra/create', [MitraPenggunaController::class, 'create'])->name('mitra.create');
    Route::post('mitra', [MitraPenggunaController::class, 'store'])->name('mitra.store');

    Route::get('regulator-organisasi', [RegulatorOrganisasiController::class, 'index'])->name('regulator_org.index');
    Route::get('regulator-organisasi/create', [RegulatorOrganisasiController::class, 'create'])->name('regulator_org.create');
    Route::post('regulator-organisasi', [RegulatorOrganisasiController::class, 'store'])->name('regulator_org.store');

    Route::get('mapping/create', [OrganisasiUserController::class, 'create'])->name('mapping.create');
    Route::post('mapping', [OrganisasiUserController::class, 'store'])->name('mapping.store');

    Route::get('reports/perawat.csv', [ReportController::class, 'perawatCsv'])->name('reports.perawat');
    Route::get('reports/lowongan.csv', [ReportController::class, 'lowonganCsv'])->name('reports.lowongan');
    Route::get('reports/rekrutmen.csv', [ReportController::class, 'rekrutmenCsv'])->name('reports.rekrutmen');
    Route::get('reports/lamaran.csv', [ReportController::class, 'lamaranCsv'])->name('reports.lamaran');
    Route::get('reports/legalitas.csv', [ReportController::class, 'legalitasCsv'])->name('reports.legalitas');
    Route::get('reports/riwayat-pekerjaan.csv', [ReportController::class, 'riwayatPekerjaanCsv'])->name('reports.riwayat_pekerjaan');
    Route::get('reports/audit-logs.csv', [ReportController::class, 'auditLogsCsv'])->name('reports.audit_logs');
});

require __DIR__.'/auth.php';
