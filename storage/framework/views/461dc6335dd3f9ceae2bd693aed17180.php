

<?php $__env->startSection('title', 'Notifications'); ?>
<?php $__env->startSection('page-title', 'Send Notifications'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="notifier()" class="space-y-6">

    
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Total Sent</p><p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($stats['total']); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Delivered</p><p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($stats['sent']); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Active Devices</p><p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($stats['active_tokens']); ?></p></div>
    </div>

    
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Compose Notification</h3>
        <form method="POST" action="<?php echo e(panel_route('notifications.send')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Title *</label>
                <input type="text" name="title" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. Holiday Announcement">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Message *</label>
                <textarea name="body" required rows="3" maxlength="2000" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Write your message..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Delivery</label>
                    <select name="channel" x-model="channel" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="all">Push + In-App</option>
                        <option value="in_app">In-App only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Audience</label>
                    <select name="target_type" x-model="targetType" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="all">Everyone in school</option>
                        <option value="role">By role</option>
                        <option value="class">A class</option>
                        <option value="section">A section</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div x-show="targetType==='role'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="target_role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="student">Students</option>
                        <option value="teacher">Teachers</option>
                        <option value="parent">Parents</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div x-show="targetType==='class' || targetType==='section'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                    <select name="target_class_id" x-model="classId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select class</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cid => $cname): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cid); ?>"><?php echo e($cname); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div x-show="targetType==='section'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                    <select name="target_section_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select section</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" x-show="String(classId)===String(<?php echo e($s->class_id); ?>)"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Send Notification</button>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50"><h3 class="text-sm font-semibold text-gray-800">Recent Notifications</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Audience</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Recipients</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Sent</th>
            </tr></thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e($n->title); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e(\Illuminate\Support\Str::limit($n->body, 60)); ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs capitalize">
                        <?php echo e($n->target_type); ?><?php echo e($n->target_role ? ': '.$n->target_role : ''); ?>

                    </td>
                    <td class="px-4 py-3 text-center text-gray-700"><?php echo e($n->recipients); ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php $sb = ['queued'=>'text-amber-700 bg-amber-50','processing'=>'text-blue-700 bg-blue-50','sent'=>'text-green-700 bg-green-50','failed'=>'text-red-700 bg-red-50'][$n->status] ?? 'text-gray-700 bg-gray-100'; ?>
                        <span class="text-xs font-medium <?php echo e($sb); ?> px-2 py-0.5 rounded-full"><?php echo e(ucfirst($n->status)); ?></span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($n->sent_at ? \Illuminate\Support\Carbon::parse($n->sent_at)->diffForHumans() : '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No notifications sent yet.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notifications->hasPages()): ?><div class="px-4 py-3 border-t"><?php echo e($notifications->links()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function notifier() {
        return { channel: 'all', targetType: 'all', classId: '' };
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\notifications\index.blade.php ENDPATH**/ ?>