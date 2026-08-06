<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'color' => 'navy', // navy | success | warning | danger
    'icon' => null,
    'link' => null
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
    'label',
    'value',
    'color' => 'navy', // navy | success | warning | danger
    'icon' => null,
    'link' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colorMap = [
        'navy' => 'var(--navy)',
        'success' => 'var(--success)',
        'warning' => 'var(--warning)',
        'danger' => 'var(--danger)',
        'maroon' => 'var(--maroon)',
        'gold' => 'var(--gold)',
    ];
    $borderColor = $colorMap[$color] ?? "var(--{$color}, var(--navy))";
?>

<?php if($link): ?>
<a href="<?php echo e($link); ?>" class="text-decoration-none">
<?php endif; ?>
<div <?php echo e($attributes->merge(['class' => 'card stat-card shadow-sm border-0 h-100 bg-white' . ($link ? ' cursor-pointer' : '')])); ?> 
     style="border-left: 5px solid <?php echo e($borderColor); ?> !important; border-radius: var(--radius, 8px);">
    <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div>
            <div class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.6px;">
                <?php echo e($label); ?>

            </div>
            <div class="fw-bold fs-2 text-dark" style="line-height: 1.1;">
                <?php echo e($value); ?>

            </div>
        </div>
        <?php if($icon): ?>
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center"
                 style="background-color: rgba(18, 39, 90, 0.05); width: 44px; height: 44px;">
                <i class="<?php echo e($icon); ?> fs-4" style="color: <?php echo e($borderColor); ?>;"></i>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if($link): ?>
</a>
<?php endif; ?>
<?php /**PATH C:\Project\activity-monitor-main\resources\views/components/stat-card.blade.php ENDPATH**/ ?>