<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'percent' => 0,
    'label'   => 'Completion Rate',
    'size'    => 160
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'percent' => 0,
    'label'   => 'Completion Rate',
    'size'    => 160
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
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
?>

<div <?php echo e($attributes->merge(['class' => 'd-flex flex-column align-items-center justify-content-center'])); ?>>
    <div class="position-relative d-flex align-items-center justify-content-center" 
         style="width: <?php echo e($size); ?>px; height: <?php echo e($size); ?>px;">
        
        <!-- SVG 270-degree Gauge -->
        <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(135deg);">
            <defs>
                <linearGradient id="<?php echo e($uniqueId); ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f0a500" />
                    <stop offset="50%" stop-color="#12275a" />
                    <stop offset="100%" stop-color="#1a8a4a" />
                </linearGradient>
            </defs>
            
            <!-- Background Track (Gray 270° arc) -->
            <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" 
                    stroke-width="<?php echo e($strokeWidth); ?>" 
                    stroke-dasharray="<?php echo e($maxArc); ?> <?php echo e($fullCircumference); ?>" 
                    stroke-linecap="round" />
            
            <!-- Active Progress Arc (Strictly 0% to 100% based on $percentVal) -->
            <?php if($percentVal > 0): ?>
                <circle cx="50" cy="50" r="38" fill="none" 
                        stroke="url(#<?php echo e($uniqueId); ?>)" 
                        stroke-width="<?php echo e($strokeWidth); ?>" 
                        stroke-dasharray="<?php echo e($filledArc); ?> <?php echo e($fullCircumference); ?>" 
                        stroke-linecap="round" 
                        style="transition: stroke-dasharray 0.6s ease-in-out;" />
            <?php endif; ?>
        </svg>
        
        <!-- Inner Center Text & Label -->
        <div class="position-absolute top-50 start-50 translate-middle text-center d-flex flex-column align-items-center justify-content-center"
             style="width: 72%; pointer-events: none;">
            <span class="fw-bold" style="color: var(--navy); font-size: <?php echo e($fontSize); ?>; line-height: 1;">
                <?php echo e(round($percentVal)); ?>%
            </span>
            <?php if($label): ?>
                <small class="text-muted text-uppercase fw-semibold mt-1 px-1" style="font-size: <?php echo e($labelSize); ?>; letter-spacing: 0.4px; line-height: 1.15;">
                    <?php echo e($label); ?>

                </small>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Project\activity-monitor-main\resources\views/components/progress-ring.blade.php ENDPATH**/ ?>