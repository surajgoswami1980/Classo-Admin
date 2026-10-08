<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['banner' => null]));

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

foreach (array_filter((['banner' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner): ?>
<?php
    $days = $banner['days_to_expiry'];
    $expiryTone = is_null($days) ? 'neutral' : ($days < 0 ? 'danger' : ($days <= 30 ? 'warning' : 'good'));
    $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 17 ? 'Good afternoon' : 'Good evening');
?>

<div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-700 via-primary-600 to-indigo-600 shadow-lg">
    <!-- decorative blobs -->
    <div class="pointer-events-none absolute -top-16 -right-10 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-indigo-400/20 blur-2xl"></div>

    <div class="relative flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
        <!-- Identity -->
        <div class="flex items-start gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white/15 ring-1 ring-white/25 backdrop-blur">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($banner['logo'])): ?>
                    <img src="<?php echo e(\Illuminate\Support\Str::startsWith($banner['logo'], ['http://','https://']) ? $banner['logo'] : asset('storage/' . $banner['logo'])); ?>"
                         alt="<?php echo e($banner['name']); ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    <span class="text-2xl font-black text-white"><?php echo e(\Illuminate\Support\Str::substr($banner['name'], 0, 1)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="min-w-0">
                <p class="text-sm font-medium text-white/70"><?php echo e($greeting); ?>, welcome back 👋</p>
                <h2 class="mt-0.5 truncate text-2xl font-bold text-white sm:text-3xl"><?php echo e($banner['name']); ?></h2>

                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/80">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner['code']): ?>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                            <?php echo e($banner['code']); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner['city']): ?>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <?php echo e($banner['city']); ?><?php echo e($banner['state'] ? ', ' . $banner['state'] : ''); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner['board']): ?>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg>
                            <?php echo e($banner['board']); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Subscription + usage -->
        <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
            <!-- Plan / expiry -->
            <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur">
                <div class="flex items-center justify-between gap-6">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Plan</p>
                    <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white"><?php echo e($banner['plan']); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!is_null($days)): ?>
                    <p class="mt-1.5 text-sm font-semibold
                        <?php echo e($expiryTone === 'danger' ? 'text-rose-200' : ($expiryTone === 'warning' ? 'text-amber-200' : 'text-emerald-100')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($days < 0): ?>
                            Expired <?php echo e(abs($days)); ?> day<?php echo e(abs($days) === 1 ? '' : 's'); ?> ago
                        <?php elseif($days === 0): ?>
                            Expires today
                        <?php else: ?>
                            <?php echo e($days); ?> day<?php echo e($days === 1 ? '' : 's'); ?> left
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner['subscription_end']): ?>
                        <p class="text-xs text-white/60">until <?php echo e(\Illuminate\Support\Carbon::parse($banner['subscription_end'])->format('d M Y')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <p class="mt-1.5 text-sm font-semibold text-white/80">No expiry set</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Capacity usage -->
            <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur min-w-[12rem]">
                <div class="space-y-2.5">
                    <div>
                        <div class="flex items-center justify-between text-xs text-white/70">
                            <span>Students</span>
                            <span><?php echo e($banner['max_students'] ? $banner['student_usage_pct'] . '%' : '—'); ?></span>
                        </div>
                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-white/15">
                            <div class="h-full rounded-full bg-white/80" style="width: <?php echo e($banner['student_usage_pct']); ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-xs text-white/70">
                            <span>Staff</span>
                            <span><?php echo e($banner['max_staff'] ? $banner['staff_usage_pct'] . '%' : '—'); ?></span>
                        </div>
                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-white/15">
                            <div class="h-full rounded-full bg-white/80" style="width: <?php echo e($banner['staff_usage_pct']); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!is_null($days) && $days <= 30): ?>
        <div class="relative border-t border-white/15 bg-black/10 px-6 py-2.5 sm:px-8">
            <p class="text-sm text-white/90">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($days < 0): ?>
                    ⚠️ Your subscription has expired. Please renew to avoid service interruption.
                <?php else: ?>
                    ⏳ Your subscription renews soon. Contact support to extend your plan.
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/components/school-banner.blade.php ENDPATH**/ ?>