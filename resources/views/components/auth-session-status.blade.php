@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700']) }}>
        {{ $status }}
    </div>
@endif
