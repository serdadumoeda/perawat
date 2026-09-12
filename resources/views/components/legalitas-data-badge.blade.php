@props(['status' => null])

@php
    $normalized = \App\Models\DataLegalitas::normalizeDataStatus($status);
    $map = [
        \App\Models\DataLegalitas::DATA_STATUS_VERIFIED => ['label' => 'STR Aktif (Terverifikasi)', 'class' => 'badge badge-success'],
        \App\Models\DataLegalitas::DATA_STATUS_EXPIRED => ['label' => 'STR Kedaluwarsa', 'class' => 'badge badge-danger'],
        \App\Models\DataLegalitas::DATA_STATUS_PENDING_SYNC => ['label' => 'STR Menunggu Sinkronisasi', 'class' => 'badge badge-info'],
        \App\Models\DataLegalitas::DATA_STATUS_REJECTED => ['label' => 'STR Ditolak', 'class' => 'badge badge-danger'],
        \App\Models\DataLegalitas::DATA_STATUS_SUBMITTED => ['label' => 'STR Menunggu Verifikasi', 'class' => 'badge badge-warning'],
        \App\Models\DataLegalitas::DATA_STATUS_DRAFT => ['label' => 'Draft', 'class' => 'badge badge-muted'],
    ];
    $label = $map[$normalized]['label'] ?? $normalized;
    $class = $map[$normalized]['class'] ?? 'badge badge-muted';
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $label }}</span>
