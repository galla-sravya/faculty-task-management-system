@props([
    'percent' => 0,
    'label'   => 'Completion Rate',
    'size'    => 160
])

@php
    $percentVal = min(100, max(0, (float)$percent));
    
    // 270-degree gauge math:
    // Radius r = 38, Full circumference = 2 * PI * 38 = 238.761
    // Total 270° arc length = 0.75 * 238.761 = 179.071
    $maxArc = 179.071;
    $fullCircumference = 238.761;
    $filledArc = ($percentVal / 100) * $maxArc;
    
    // Dynamic border/stroke width & font sizing for different gauge sizes
    $fontSize = $size <= 110 ? '1rem' : ($size <= 160 ? '1.5rem' : '2.1rem');
    $labelSize = $size <= 110 ? '0.58rem' : ($size <= 160 ? '0.65rem' : '0.72rem');
    $strokeWidth = $size <= 110 ? 9 : 8;
    $uniqueId = 'gaugeGrad_' . $size . '_' . rand(100, 999);
@endphp

<div {{ $attributes->merge(['class' => 'd-flex flex-column align-items-center justify-content-center']) }}>
    <div class="position-relative d-flex align-items-center justify-content-center" 
         style="width: {{ $size }}px; height: {{ $size }}px;">
        
        <!-- SVG 270-degree Gauge -->
        <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(135deg);">
            <defs>
                <linearGradient id="{{ $uniqueId }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f0a500" />
                    <stop offset="50%" stop-color="#12275a" />
                    <stop offset="100%" stop-color="#1a8a4a" />
                </linearGradient>
            </defs>
            
            <!-- Background Track (Gray 270° arc) -->
            <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" 
                    stroke-width="{{ $strokeWidth }}" 
                    stroke-dasharray="{{ $maxArc }} {{ $fullCircumference }}" 
                    stroke-linecap="round" />
            
            <!-- Active Progress Arc (Strictly 0% to 100% based on $percentVal) -->
            @if($percentVal > 0)
                <circle cx="50" cy="50" r="38" fill="none" 
                        stroke="url(#{{ $uniqueId }})" 
                        stroke-width="{{ $strokeWidth }}" 
                        stroke-dasharray="{{ $filledArc }} {{ $fullCircumference }}" 
                        stroke-linecap="round" 
                        style="transition: stroke-dasharray 0.6s ease-in-out;" />
            @endif
        </svg>
        
        <!-- Inner Center Text & Label -->
        <div class="position-absolute top-50 start-50 translate-middle text-center d-flex flex-column align-items-center justify-content-center"
             style="width: 72%; pointer-events: none;">
            <span class="fw-bold" style="color: var(--navy); font-size: {{ $fontSize }}; line-height: 1;">
                {{ round($percentVal) }}%
            </span>
            @if($label)
                <small class="text-muted text-uppercase fw-semibold mt-1 px-1" style="font-size: {{ $labelSize }}; letter-spacing: 0.4px; line-height: 1.15;">
                    {{ $label }}
                </small>
            @endif
        </div>
    </div>
</div>
