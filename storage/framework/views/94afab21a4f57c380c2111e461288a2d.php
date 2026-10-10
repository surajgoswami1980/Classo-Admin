<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['size' => 40, 'showText' => false, 'textClass' => 'text-gray-900', 'dark' => false]));

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

foreach (array_filter((['size' => 40, 'showText' => false, 'textClass' => 'text-gray-900', 'dark' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>


<span <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-3'])); ?>>
    <svg width="<?php echo e($size); ?>" height="<?php echo e($size); ?>" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0">
        <defs>
            <linearGradient id="quiloRing" x1="15" y1="10" x2="85" y2="90" gradientUnits="userSpaceOnUse">
                <stop stop-color="#2EA7FF"/>
                <stop offset="0.55" stop-color="#1E6FE0"/>
                <stop offset="1" stop-color="#1746B0"/>
            </linearGradient>
            <linearGradient id="quiloTail" x1="45" y1="55" x2="80" y2="95" gradientUnits="userSpaceOnUse">
                <stop stop-color="#2BD4C4"/>
                <stop offset="1" stop-color="#15A58E"/>
            </linearGradient>
        </defs>
        
        <circle cx="46" cy="42" r="30" stroke="url(#quiloRing)" stroke-width="14"/>
        
        <path d="M50 54 L84 92 L64 92 L40 64 Z" fill="url(#quiloTail)"/>
    </svg>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showText): ?>
        <span class="text-xl font-extrabold tracking-tight <?php echo e($textClass); ?>">Quilo</span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</span>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\components\quilo-logo.blade.php ENDPATH**/ ?>