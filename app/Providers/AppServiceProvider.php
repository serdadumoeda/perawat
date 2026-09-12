<?php

namespace App\Providers;

use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\Lamaran;
use App\Models\LembagaPelatihan;
use App\Models\Lowongan;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Observers\CareerEventObserver;
use App\Observers\PerawatRelatedObserver;
use App\Observers\VerifiedStatusWebhookObserver;
use App\Policies\DataLegalitasPolicy;
use App\Policies\InstitusiPendidikanPolicy;
use App\Policies\LamaranPolicy;
use App\Policies\LembagaPelatihanPolicy;
use App\Policies\LowonganPolicy;
use App\Policies\RiwayatPekerjaanPolicy;
use App\Policies\SertifikasiKompetensiPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(InstitusiPendidikan::class, InstitusiPendidikanPolicy::class);
        Gate::policy(LembagaPelatihan::class, LembagaPelatihanPolicy::class);
        Gate::policy(SertifikasiKompetensi::class, SertifikasiKompetensiPolicy::class);
        Gate::policy(Lowongan::class, LowonganPolicy::class);
        Gate::policy(RiwayatPekerjaan::class, RiwayatPekerjaanPolicy::class);
        Gate::policy(Lamaran::class, LamaranPolicy::class);
        Gate::policy(DataLegalitas::class, DataLegalitasPolicy::class);

        RateLimiter::for('api_key', function (Request $request) {
            $token = $request->header('X-API-KEY') ?? $request->bearerToken();
            $key = $token ? hash('sha256', $token) : $request->ip();

            return Limit::perMinute(120)->by($key ?: $request->ip());
        });

        DataLegalitas::observe(PerawatRelatedObserver::class);
        RiwayatPendidikan::observe(PerawatRelatedObserver::class);
        SertifikasiKompetensi::observe(PerawatRelatedObserver::class);
        RiwayatPekerjaan::observe(PerawatRelatedObserver::class);

        DataLegalitas::observe(CareerEventObserver::class);
        RiwayatPendidikan::observe(CareerEventObserver::class);
        SertifikasiKompetensi::observe(CareerEventObserver::class);
        RiwayatPekerjaan::observe(CareerEventObserver::class);

        DataLegalitas::observe(VerifiedStatusWebhookObserver::class);
        SertifikasiKompetensi::observe(VerifiedStatusWebhookObserver::class);
        RiwayatPekerjaan::observe(VerifiedStatusWebhookObserver::class);
    }
}
