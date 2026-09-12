@props(['perawat' => null, 'result' => null])

@php
    $hasKampus = true; // Always true for gateway/graduates
    $hasStr = false;
    $hasSertifikasi = false;
    $hasPengalaman = false;
    $hasInternational = false;

    if ($perawat) {
        $hasStr = optional($perawat->dataLegalitas)->status_str === 'Aktif' || optional($perawat->dataLegalitas)->data_status === 'verified';
        $hasSertifikasi = $perawat->sertifikasiKompetensi()->exists();
        $hasPengalaman = $perawat->isVerifiedExperience();
        $hasInternational = optional($perawat->dataLegalitas)->nomor_izin_luar_negeri !== null || $perawat->riwayatPekerjaan()->where('kategori_lokasi', 'luar_negeri')->exists();
    } elseif ($result) {
        $hasStr = isset($result['legalitas']) && $result['legalitas']['status_str'] === 'Aktif';
        $hasSertifikasi = !empty($result['sertifikasi']);
        $hasPengalaman = isset($result['pengalaman']) && $result['pengalaman']['verified'];
    }

    $stages = [
        ['step' => '1', 'title' => 'Pendidikan Kampus', 'label' => 'Fresh Graduate', 'icon' => '🎓', 'active' => $hasKampus],
        ['step' => '2', 'title' => 'Legalitas STR', 'label' => $hasStr ? 'STR Aktif' : 'Proses STR', 'icon' => '📜', 'active' => $hasStr],
        ['step' => '3', 'title' => 'Uji Kompetensi', 'label' => $hasSertifikasi ? 'Tersertifikasi' : 'Uji BTCLS', 'icon' => '🎖️', 'active' => $hasSertifikasi],
        ['step' => '4', 'title' => 'Pengalaman Faskes', 'label' => $hasPengalaman ? 'Verified Experience' : 'Junior Nurse', 'icon' => '🏥', 'active' => $hasPengalaman],
        ['step' => '5', 'title' => 'Go International', 'label' => $hasInternational ? 'Global Ready' : 'Dalam Negeri', 'icon' => '🌐', 'active' => $hasInternational],
    ];
@endphp

<div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
        <div>
            <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e05252;">Peta Kesiapan Talent 360°</div>
            <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#1a1a1a;">Siklus Integrasi Karir Perawat</div>
        </div>
        <span class="badge badge-teal">Living CV Sync</span>
    </div>

    <!-- Stepper Grid -->
    <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:0.75rem; position:relative;" class="sm:grid-cols-5 grid-cols-2">
        @foreach ($stages as $st)
            <div style="text-align:center; padding:1rem 0.5rem; border-radius:0.875rem; border:1px solid {{ $st['active'] ? 'rgba(15,80,80,0.25)' : '#f0e5cf' }}; background:{{ $st['active'] ? 'rgba(15,80,80,0.05)' : '#fdfaf5' }}; transition:all 0.2s;">
                <div style="font-size:1.5rem; margin-bottom:0.375rem;">{{ $st['icon'] }}</div>
                <div style="font-size:0.75rem; font-weight:700; color:{{ $st['active'] ? '#0f5050' : '#1a1a1a' }}; margin-bottom:0.125rem;">
                    {{ $st['title'] }}
                </div>
                <div style="font-size:0.65rem; font-weight:600; color:{{ $st['active'] ? '#065f46' : '#9a9a9a' }};">
                    @if($st['active'])
                        ✓ {{ $st['label'] }}
                    @else
                        {{ $st['label'] }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
