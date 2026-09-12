@props([
    'status' => null,
    'rejectedReason' => null,
    'verifiedBy' => null,
    'verifiedAt' => null,
    'sourceSystem' => null,
    'sourceActor' => null,
    'sourceOrganisasi' => null,
])

@php
    $statusValue = $status ?: '-';
    $statusClass = 'bg-slate-100 text-slate-700';
    $statusLower = strtolower((string) $statusValue);

    if (in_array($statusLower, ['verified', 'valid', 'approved', 'aktif'], true)) {
        $statusClass = 'bg-emerald-100 text-emerald-700';
    } elseif (in_array($statusLower, ['rejected', 'expired'], true)) {
        $statusClass = 'bg-rose-100 text-rose-700';
    } elseif (in_array($statusLower, ['pending', 'submitted', 'pending_sync', 'hold'], true)) {
        $statusClass = 'bg-amber-100 text-amber-700';
    }

    $verifiedByValue = $verifiedBy ?: '-';
    $verifiedAtValue = $verifiedAt ? \Illuminate\Support\Carbon::parse($verifiedAt)->format('d M Y H:i') : '-';
    $sourceSystemValue = $sourceSystem ?: '-';
    $sourceActorValue = $sourceActor ?: '-';
    $sourceOrganisasiValue = $sourceOrganisasi ?: '-';
    $rejectedReasonValue = $rejectedReason ?: '-';
@endphp

<div class="space-y-1 text-xs text-slate-600 leading-5">
    <div class="flex items-start gap-2">
        <span class="min-w-[92px] font-semibold text-slate-500">Status</span>
        <span class="inline-flex rounded-full px-2 py-0.5 font-semibold {{ $statusClass }}">{{ $statusValue }}</span>
    </div>
    <div class="flex items-start gap-2">
        <span class="min-w-[92px] font-semibold text-slate-500">Diverifikasi Oleh</span>
        <span>{{ $verifiedByValue }}</span>
    </div>
    <div class="flex items-start gap-2">
        <span class="min-w-[92px] font-semibold text-slate-500">Waktu Verifikasi</span>
        <span>{{ $verifiedAtValue }}</span>
    </div>
    <div class="flex items-start gap-2">
        <span class="min-w-[92px] font-semibold text-slate-500">Sistem Sumber</span>
        <span>{{ $sourceSystemValue }}</span>
    </div>
    <div class="flex items-start gap-2">
        <span class="min-w-[92px] font-semibold text-slate-500">Aktor Sumber</span>
        <span>{{ $sourceActorValue }}</span>
    </div>
    <div class="flex items-start gap-2">
        <span class="min-w-[92px] font-semibold text-slate-500">Organisasi Sumber</span>
        <span>{{ $sourceOrganisasiValue }}</span>
    </div>
    <div class="flex items-start gap-2">
        <span class="min-w-[92px] font-semibold text-slate-500">Alasan Penolakan</span>
        <span>{{ $rejectedReasonValue }}</span>
    </div>
</div>
