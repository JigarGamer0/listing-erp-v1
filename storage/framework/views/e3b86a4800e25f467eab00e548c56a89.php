<?php $__env->startSection('title', 'Payments'); ?>
<?php $__env->startSection('page-title', 'Payment History'); ?>

<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]"><label class="block text-xs font-medium text-gray-500 mb-1">Search Client</label><input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Client name..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">From</label><input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">To</label><input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></div>
            <button type="submit" class="px-5 py-2.5 bg-gray-800 dark:bg-gray-600 text-white text-sm font-medium rounded-xl">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Received By</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo e($p->payment_date->format('d/m/Y')); ?></td>
                            <td class="px-5 py-3"><a href="<?php echo e(route('clients.show', $p->client_id)); ?>" class="text-sm font-medium text-blue-600 hover:underline"><?php echo e($p->client->name ?? 'N/A'); ?></a></td>
                            <td class="px-5 py-3 text-right text-sm font-semibold text-emerald-600">₹<?php echo e(number_format($p->amount, 2)); ?></td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo e(ucfirst(str_replace('_', ' ', $p->payment_method))); ?></td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo e($p->receivedByUser?->name); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No payments found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700"><?php echo e($payments->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jigar\OneDrive\Documents\.l\Listing website v1\resources\views/payments/index.blade.php ENDPATH**/ ?>