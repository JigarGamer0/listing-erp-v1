<?php $__env->startSection('title', 'Employees'); ?>
<?php $__env->startSection('page-title', 'Employee Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="fade-in" x-data="{ showDeleteModal: false, deleteUrl: '', deleteName: '', showAddEmployeeModal: false, createLogin: false }">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Employees</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage team members, commissions and client assignments</p>
        </div>
        <button type="button" @click="showAddEmployeeModal = true" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg shadow-blue-600/20 transition-all">+ Add Employee</button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Phone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Salary Type</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Salary</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Advance</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Pending Salary</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Commission</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Clients</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-all" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location='<?php echo e(route('employees.show', $emp)); ?>'">
                            <td class="px-5 py-3"><a href="<?php echo e(route('employees.show', $emp)); ?>" class="text-sm font-medium text-blue-600 hover:underline"><?php echo e($emp->name); ?></a></td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo e($emp->phone ?? '—'); ?></td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo e(ucfirst(str_replace('_', ' ', $emp->salary_type))); ?></td>
                            <td class="px-5 py-3 text-sm text-right text-gray-800 dark:text-white">₹<?php echo e(number_format($emp->total_salary_estimate, 0)); ?></td>
                            <td class="px-5 py-3 text-sm text-right text-gray-800 dark:text-white"><?php echo e($emp->total_pending_advance > 0 ? '₹' . number_format($emp->total_pending_advance, 0) : '-'); ?></td>
                            <td class="px-5 py-3 text-sm text-right font-semibold text-red-600">₹<?php echo e(number_format($emp->pending_salary, 0)); ?></td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo e($emp->commission_type === 'percentage' ? $emp->commission_value . '%' : '₹' . number_format($emp->commission_value, 0)); ?></td>
                            <td class="px-5 py-3 text-sm text-center text-gray-800 dark:text-white"><?php echo e($emp->activeAssignments->count()); ?></td>
                            <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($emp->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>"><?php echo e(ucfirst($emp->status)); ?></span></td>
                            <td class="px-5 py-3 text-center whitespace-nowrap">
                                <a href="<?php echo e(route('employees.show', $emp)); ?>" title="View" class="p-1.5 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 rounded-lg inline-block transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="<?php echo e(route('employees.edit', $emp)); ?>" title="Edit" class="p-1.5 hover:bg-amber-50 dark:hover:bg-amber-900/20 text-amber-600 rounded-lg inline-block transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button type="button" 
                                        @click="deleteUrl = '<?php echo e(route('employees.destroy', $emp)); ?>'; deleteName = '<?php echo e(addslashes($emp->name)); ?>'; showDeleteModal = true;" 
                                        title="Delete Employee" 
                                        class="p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-500 hover:text-red-700 rounded-lg inline-block transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="10" class="px-5 py-12 text-center text-gray-400">No employees found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700"><?php echo e($employees->links()); ?></div>
    </div>

    
    <div x-show="showDeleteModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-2xl relative"
             @click.away="showDeleteModal = false">
            <div class="flex items-center space-x-3 text-red-600 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center font-bold text-xl">⚠️</div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Delete Employee</h3>
            </div>
            
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Are you sure you want to delete employee <strong class="text-gray-900 dark:text-white" x-text="deleteName"></strong>?
            </p>
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-xl text-xs text-amber-800 dark:text-amber-300 mb-6">
                All currently active clients assigned to this employee will be safely unassigned. Historical salaries & commissions remain preserved.
            </div>

            <form :action="deleteUrl" method="POST" class="flex justify-end space-x-3">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                    Yes, Delete Employee
                </button>
            </form>
        </div>
    </div>

    
    <div x-show="showAddEmployeeModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-2xl w-full mx-4 shadow-2xl relative my-8"
             @click.away="showAddEmployeeModal = false">
            <div class="flex items-center justify-between mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center font-bold text-sm">👔</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Add New Employee</h3>
                </div>
                <button type="button" @click="showAddEmployeeModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form method="POST" action="<?php echo e(route('employees.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="space-y-4 max-h-[72vh] overflow-y-auto px-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Rahul Sharma" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone (Optional)</label>
                            <input type="text" name="phone" placeholder="Mobile number" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email (Optional)</label>
                            <input type="email" name="email" placeholder="staff@example.com" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Joining Date *</label>
                            <input type="date" name="joining_date" value="<?php echo e(date('Y-m-d')); ?>" required class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Role / Designation</label>
                        <input type="text" name="role_title" placeholder="e.g. Listing Executive, Account Manager" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                    </div>

                    <div class="p-3.5 bg-gray-50 dark:bg-gray-750/30 rounded-xl border border-gray-100 dark:border-gray-700 space-y-3">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Salary & Commission</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Salary Type *</label>
                                <select name="salary_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                                    <option value="fixed">Fixed Salary</option>
                                    <option value="package_based">Commission Only (Package Based)</option>
                                    <option value="both">Both (Fixed + Commission)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Fixed Salary (₹)</label>
                                <input type="number" step="0.01" min="0" name="fixed_salary" value="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Commission Type *</label>
                                <select name="commission_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                                    <option value="fixed_amount">Fixed Amount (₹ per client)</option>
                                    <option value="percentage">Percentage (% of package)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Commission Value</label>
                                <input type="number" step="0.01" min="0" name="commission_value" value="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                            </div>
                        </div>
                    </div>

                    
                    <div class="p-3 bg-gray-50/50 dark:bg-gray-750/20 rounded-xl border border-gray-100 dark:border-gray-700">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="create_login" value="1" x-model="createLogin" class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Create system login for this employee</span>
                        </label>
                        <div x-show="createLogin" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Username</label>
                                <input type="text" name="username" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Password</label>
                                <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-xs">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="showAddEmployeeModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-xs">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/25 transition-all text-xs">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jigar\OneDrive\Documents\.l\Listing website v1\resources\views/employees/index.blade.php ENDPATH**/ ?>