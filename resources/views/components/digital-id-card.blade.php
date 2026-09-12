@props(['nik' => '3578000000000001', 'nama' => 'Nursi Rahma, S.Kep', 'kampus' => 'Universitas Airlangga', 'ijazah' => 'IJZ/2025/NURSE/3578000000000001', 'hash' => null])

@php
    $cardHash = $hash ?? hash('sha256', $nik.$ijazah);
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode(route('public.perawat.show', ['token' => substr($cardHash, 0, 16)]));
@endphp

<div id="digital-id-card-wrapper" style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; overflow:hidden; box-shadow:0 12px 36px rgba(0,0,0,0.08); max-width:32rem; margin:0 auto;">
    <!-- Top Card Header -->
    <div style="background:linear-gradient(135deg, #0b3535 0%, #0f5050 100%); color:#ffffff; padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.25rem; height:2.25rem; border-radius:0.5rem; background:rgba(244,124,90,0.2); display:flex; align-items:center; justify-content:center;">
                <x-application-logo style="width:1.25rem; height:1.25rem; color:#f47c5a;" />
            </div>
            <div>
                <div style="font-family:'Playfair Display',serif; font-size:0.9375rem; font-weight:700; color:#ffffff;">Platform Keperawatan</div>
                <div style="font-size:0.6rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#f7a58a;">Kartu Identitas Digital Perawat</div>
            </div>
        </div>
        <span style="font-size:0.6rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; padding:0.25rem 0.625rem; border-radius:9999px; background:rgba(16,185,129,0.2); border:1px solid rgba(16,185,129,0.4); color:#a7f3d0;">
            ✓ Official Verified
        </span>
    </div>

    <!-- Card Body -->
    <div style="padding:1.5rem; background:#fdfaf5; display:flex; gap:1.25rem; align-items:start;" class="flex-col sm:flex-row">
        <!-- Photo Frame -->
        <div style="width:5.5rem; height:7rem; border-radius:0.875rem; overflow:hidden; border:3px solid #ffffff; box-shadow:0 6px 18px rgba(0,0,0,0.1); flex-shrink:0; background:#e3d0b0;">
            <img src="/images/hero_nursing_1788228946260.png" alt="{{ $nama }}" style="width:100%; height:100%; object-fit:cover; display:block;">
        </div>

        <!-- Info Details -->
        <div style="flex:1;">
            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#1a1a1a; margin-bottom:0.25rem;">{{ $nama }}</div>
            <div style="font-size:0.8125rem; font-weight:700; color:#e05252; margin-bottom:0.75rem;">NIK: {{ $nik }}</div>

            <div style="display:flex; flex-direction:column; gap:0.375rem; font-size:0.75rem; color:#5a5a5a; border-top:1px solid #f0e5cf; padding-top:0.625rem;">
                <div><span>Institusi:</span> <strong style="color:#1a1a1a;">{{ $kampus }}</strong></div>
                <div><span>No. Ijazah:</span> <strong style="color:#1a1a1a;">{{ $ijazah }}</strong></div>
                <div><span>Status Living CV:</span> <strong style="color:#065f46;">Fresh Graduate &amp; Terverifikasi Kampus</strong></div>
            </div>
        </div>

        <!-- QR Code -->
        <div style="text-align:center; flex-shrink:0; align-self:center;">
            <div style="width:4.5rem; height:4.5rem; padding:0.25rem; background:#ffffff; border:1px solid #f0e5cf; border-radius:0.625rem; box-shadow:0 4px 12px rgba(0,0,0,0.05); margin:0 auto;">
                <img src="{{ $qrUrl }}" alt="QR Code Verifikasi" style="width:100%; height:100%; display:block;">
            </div>
            <div style="font-size:0.55rem; font-weight:700; color:#9a9a9a; margin-top:0.375rem; letter-spacing:0.05em; text-transform:uppercase;">Scan Verifikasi</div>
        </div>
    </div>

    <!-- Card Footer -->
    <div style="background:#ffffff; border-top:1px solid #f0e5cf; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
        <div style="font-size:0.65rem; color:#9a9a9a; font-family:monospace; word-break:break-all;">
            Hash: {{ substr($cardHash, 0, 24) }}...
        </div>
        <button type="button" onclick="window.print();" class="btn-secondary text-xs" style="padding:0.35rem 0.875rem;">
            🖨️ Cetak ID Card
        </button>
    </div>
</div>
