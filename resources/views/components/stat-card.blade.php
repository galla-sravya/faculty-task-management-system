@props([
    'label',
    'value',
    'color' => 'navy', // navy | success | warning | danger
    'icon' => null,
    'link' => null
])

@php
    $colorMap = [
        'navy' => 'var(--navy)',
        'success' => 'var(--success)',
        'warning' => 'var(--warning)',
        'danger' => 'var(--danger)',
        'maroon' => 'var(--maroon)',
        'gold' => 'var(--gold)',
    ];
    $borderColor = $colorMap[$color] ?? "var(--{$color}, var(--navy))";
@endphp

@if($link)
<a href="{{ $link }}" class="text-decoration-none">
@endif
<div {{ $attributes->merge(['class' => 'card stat-card shadow-sm border-0 h-100 bg-white' . ($link ? ' cursor-pointer' : '')]) }} 
     style="border-left: 5px solid {{ $borderColor }} !important; border-radius: var(--radius, 8px);">
    <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
            <div class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.6px;">
                {{ $label }}
            </div>
            <div class="fw-bold fs-2 text-dark" style="line-height: 1.1;">
                {{ $value }}
            </div>
        </div>
        @if($icon)
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center"
                 style="background-color: rgba(18, 39, 90, 0.05); width: 44px; height: 44px;">
                <i class="{{ $icon }} fs-4" style="color: {{ $borderColor }};"></i>
            </div>
        @endif
    </div>
</div>
@if($link)
</a>
@endif
