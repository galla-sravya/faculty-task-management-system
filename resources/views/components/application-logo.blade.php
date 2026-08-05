@props([
    'variant' => 'color', // 'white' | 'color'
    'height'  => '42'
])

@php
    $textColor   = ($variant === 'white') ? '#ffffff' : '#1e3a8a';
    $shieldBlue  = ($variant === 'white') ? '#ffffff' : '#2b3990';
    $shieldRed   = ($variant === 'white') ? '#ffffff' : '#c1272d';
    $whitePart   = ($variant === 'white') ? '#12275a' : '#ffffff';
    $subTextColor= ($variant === 'white') ? 'rgba(255, 255, 255, 0.85)' : '#12275a';
@endphp

<div {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-3']) }}>
    <!-- Official PSG iTech Shield Crest Emblem (SVG) -->
    <svg viewBox="0 0 135 150" height="{{ $height }}" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
        <defs>
            <clipPath id="leftShield">
                <rect x="0" y="0" width="67.5" height="150" />
            </clipPath>
            <clipPath id="rightShield">
                <rect x="67.5" y="0" width="67.5" height="150" />
            </clipPath>
        </defs>

        <!-- Outer Shield Border & Background -->
        @if($variant === 'white')
            <!-- All White Shield Outline -->
            <path d="M 6.75 6.75 L 128.25 6.75 L 128.25 105 L 67.5 143.25 L 6.75 105 Z" fill="none" stroke="#ffffff" stroke-width="9" stroke-linejoin="round" />
            
            <!-- Left Side Chevrons & Fills (White) -->
            <g clip-path="url(#leftShield)">
                <path d="M 6.75 6.75 L 128.25 6.75 L 128.25 105 L 67.5 143.25 L 6.75 105 Z" fill="#ffffff" />
            </g>
            <!-- Right Side Chevrons & Fills (White) -->
            <g clip-path="url(#rightShield)">
                <path d="M 6.75 6.75 L 128.25 6.75 L 128.25 105 L 67.5 143.25 L 6.75 105 Z" fill="#ffffff" />
            </g>

            <!-- Inner Detail Lines & Diamond (Navy cutout inside white shield) -->
            <path d="M 67.5 15 L 115.5 45 L 67.5 75 L 19.5 45 Z" fill="{{ $whitePart }}" />
            <path d="M 67.5 22.5 L 105 45 L 67.5 67.5 L 30 45 Z" fill="none" stroke="#ffffff" stroke-width="4.5" />
            <text x="67.5" y="49.5" text-anchor="middle" fill="#ffffff" font-family="'Inter', sans-serif" font-weight="900" font-size="19" letter-spacing="1">PSG</text>

            <!-- Left / Right Chevron Stripes -->
            <path d="M 19.5 82.5 L 67.5 112.5 L 67.5 127.5 L 12 93 Z" fill="{{ $whitePart }}" />
            <path d="M 115.5 82.5 L 67.5 112.5 L 67.5 127.5 L 123 93 Z" fill="{{ $whitePart }}" />

            <path d="M 19.5 63 L 67.5 93 L 67.5 105 L 12 70.5 Z" fill="{{ $whitePart }}" />
            <path d="M 115.5 63 L 67.5 93 L 67.5 105 L 123 70.5 Z" fill="{{ $whitePart }}" />

            <!-- Bottom iTech Banner -->
            <path d="M 22.5 105 L 112.5 105 L 67.5 138 Z" fill="{{ $whitePart }}" />
            <text x="67.5" y="123" text-anchor="middle" fill="#ffffff" font-family="'Inter', sans-serif" font-weight="800" font-style="italic" font-size="16">iTech</text>
        @else
            <!-- Dual Color Shield: Blue Left / Red Right -->
            <!-- Left Half (Blue) -->
            <g clip-path="url(#leftShield)">
                <path d="M 6.75 6.75 L 128.25 6.75 L 128.25 105 L 67.5 143.25 L 6.75 105 Z" fill="{{ $shieldBlue }}" />
            </g>
            <!-- Right Half (Red) -->
            <g clip-path="url(#rightShield)">
                <path d="M 6.75 6.75 L 128.25 6.75 L 128.25 105 L 67.5 143.25 L 6.75 105 Z" fill="{{ $shieldRed }}" />
            </g>

            <!-- Inner White Diamond Background -->
            <path d="M 67.5 15 L 115.5 45 L 67.5 75 L 19.5 45 Z" fill="#ffffff" />
            <!-- Inner Diamond Outline -->
            <path d="M 67.5 22.5 L 105 45 L 67.5 67.5 L 30 45 Z" fill="none" stroke="{{ $shieldBlue }}" stroke-width="4.5" />
            <!-- Diamond PSG Text -->
            <text x="67.5" y="49.5" text-anchor="middle" fill="{{ $shieldBlue }}" font-family="'Inter', sans-serif" font-weight="900" font-size="19" letter-spacing="1">PSG</text>

            <!-- Chevron White Cutouts -->
            <path d="M 19.5 82.5 L 67.5 112.5 L 67.5 124.5 L 12 90 Z" fill="#ffffff" />
            <path d="M 115.5 82.5 L 67.5 112.5 L 67.5 124.5 L 123 90 Z" fill="#ffffff" />

            <path d="M 19.5 63 L 67.5 93 L 67.5 102 L 12 69 Z" fill="#ffffff" />
            <path d="M 115.5 63 L 67.5 93 L 67.5 102 L 123 69 Z" fill="#ffffff" />

            <!-- Bottom iTech Banner Background (Blue/Red split) -->
            <text x="67.5" y="129" text-anchor="middle" fill="#ffffff" font-family="'Inter', sans-serif" font-weight="800" font-style="italic" font-size="16">iTech</text>
        @endif
    </svg>

    <!-- Typography: PSG Institute of Technology and Applied Research -->
    <div class="d-flex flex-column text-start">
        <span class="fw-bold" style="color: {{ $textColor }}; font-size: 1.15rem; line-height: 1.15; font-family: 'Inter', sans-serif; letter-spacing: -0.2px;">
            PSG Institute of Technology
        </span>
        <span class="fw-bold" style="color: {{ $subTextColor }}; font-size: 1.05rem; line-height: 1.15; font-family: 'Inter', sans-serif; letter-spacing: -0.2px;">
            and Applied Research
        </span>
    </div>
</div>
