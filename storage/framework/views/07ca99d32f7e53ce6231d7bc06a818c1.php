
<?php $__env->startSection('title', 'Subscriptions'); ?>
<?php $__env->startSection('page-title', 'Subscription Management'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-900">Plans</h3>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.revenue')); ?>" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Revenue</a>
                <a href="<?php echo e(route('admin.subscriptions.create')); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Assign Subscription</a>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border rounded-xl p-4">
                    <h4 class="font-semibold text-gray-900"><?php echo e($plan['label']); ?></h4>
                    <p class="text-xl font-bold text-gray-900 mt-1">₹<?php echo e(number_format($plan['price'])); ?><span class="text-xs font-normal text-gray-500">/mo</span></p>
                    <p class="text-xs text-gray-500 mt-1">Up to <?php echo e(number_format($plan['max_students'])); ?> students</p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="School name or code" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Plan</label>
            <select name="plan" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php echo e(($filters['plan'] ?? '') === $key ? 'selected' : ''); ?>><?php echo e($plan['label']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All</option>
                <option value="active" <?php echo e(($filters['status'] ?? '') === 'active' ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e(($filters['status'] ?? '') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                <option value="expired" <?php echo e(($filters['status'] ?? '') === 'expired' ? 'selected' : ''); ?>>Expired</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">Filter</button>
    </form>

    
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left px-4 py-3 font-medium">School</th>
                    <th class="text-left px-4 py-3 font-medium">Plan</th>
                    <th class="text-left px-4 py-3 font-medium">Period</th>
                    <th class="text-left px-4 py-3 font-medium">Limits</th>
                    <th class="text-left px-4 py-3 font-medium">Status</th>
                    <th class="text-right px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $expired = $school->subscription_end && \Carbon\Carbon::parse($school->subscription_end)->isPast();
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo e($school->name); ?></p>
                            <p class="text-xs text-gray-400 font-mono"><?php echo e($school->code); ?></p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 capitalize"><?php echo e($school->subscription_plan ?? '—'); ?></span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($school->subscription_start): ?>
                                <?php echo e(\Carbon\Carbon::parse($school->subscription_start)->format('d M Y')); ?>

                                — <?php echo e($school->subscription_end ? \Carbon\Carbon::parse($school->subscription_end)->format('d M Y') : '—'); ?>

                            <?php else: ?>
                                —
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            <?php echo e(number_format($school->max_students ?? 0)); ?> students / <?php echo e(number_format($school->max_staff ?? 0)); ?> staff
                        </td>
                        <td class="px-4 py-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$school->is_active): ?>
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Inactive</span>
                            <?php elseif($expired): ?>
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-red-50 text-red-600">Expired</span>
                            <?php else: ?>
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-600">Active</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="<?php echo e(route('admin.subscriptions.show', $school)); ?>" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="<?php echo e(route('admin.subscriptions.edit', $school)); ?>" class="text-gray-600 hover:underline text-xs ml-3">Edit</a>
                            <form action="<?php echo e(route('admin.subscriptions.destroy', $school)); ?>" method="POST" class="inline ml-3" onsubmit="return confirm('Cancel this subscription? The school will be deactivated.');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-500 hover:underline text-xs">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">No schools found.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div><?php echo e($subscriptions->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\super-admin\subscriptions\index.blade.php ENDPATH**/ ?>