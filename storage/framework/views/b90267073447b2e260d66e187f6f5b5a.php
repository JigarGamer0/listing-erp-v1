<?php $__env->startSection('title', 'Add Client'); ?>
<?php $__env->startSection('page-title', 'Add New Client'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Client Information</h2>

        <form method="POST" action="<?php echo e(route('clients.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Name *</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex justify-between items-center">
                        <span>Mobile Number *</span>
                        <button type="button" id="add-secondary-mobile" class="text-xs text-blue-600 hover:underline flex items-center gap-0.5">
                            <span>+ Add secondary</span>
                        </button>
                    </label>
                    <div class="space-y-2">
                        <input type="text" name="mobile" value="<?php echo e(old('mobile')); ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <div id="secondary-mobile-container" class="<?php echo e(old('mobile_secondary') ? '' : 'hidden'); ?>">
                            <input type="text" name="mobile_secondary" value="<?php echo e(old('mobile_secondary')); ?>" placeholder="Secondary Mobile Number" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Work Location *</label>
                    <select name="work_location" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="our_office" <?php echo e(old('work_location') == 'our_office' ? 'selected' : ''); ?>>Our Office</option>
                        <option value="client_office" <?php echo e(old('work_location') == 'client_office' ? 'selected' : ''); ?>>Client Office</option>
                        <option value="hybrid" <?php echo e(old('work_location') == 'hybrid' ? 'selected' : ''); ?>>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Joining Date *</label>
                    <input type="date" name="joining_date" value="<?php echo e(old('joining_date', date('Y-m-d'))); ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Start Date *</label>
                    <input type="date" name="service_start_date" value="<?php echo e(old('service_start_date', date('Y-m-d'))); ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Package & GST</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Package (₹) *</label>
                    <input type="number" name="current_package" value="<?php echo e(old('current_package', 0)); ?>" required step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Flipkart GST (Count)</label>
                    <input type="number" name="current_flipkart_gst" value="<?php echo e(old('current_flipkart_gst', 0)); ?>" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meesho GST (Count)</label>
                    <input type="number" name="current_meesho_gst" value="<?php echo e(old('current_meesho_gst', 0)); ?>" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Advance Payment (₹) (Optional)</label>
                    <input type="number" name="advance_payment" value="<?php echo e(old('advance_payment', 0)); ?>" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. 5000">
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Assignments & Commissions</h3>
                <button type="button" id="add-assignment-btn" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-all flex items-center gap-1">
                    <span>➕ Add Employee</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manager</label>
                    <select name="manager_id" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="">— Select Manager —</option>
                        <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($manager->id); ?>" <?php echo e(old('manager_id') == $manager->id ? 'selected' : ''); ?>><?php echo e($manager->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div id="assignments-container" class="space-y-4 mb-5">
                <!-- Javascript will populate rows here -->
            </div>

            <div class="mt-5">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"><?php echo e(old('address')); ?></textarea>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo e(route('clients.index')); ?>" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg shadow-blue-600/20 transition-all">Create Client</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('assignments-container');
    const addBtn = document.getElementById('add-assignment-btn');
    const packageInput = document.querySelector('input[name="current_package"]');
    
    const employees = [
        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        {
            id: <?php echo e($emp->id); ?>,
            name: "<?php echo e(addslashes($emp->name)); ?>",
            salary_type: "<?php echo e($emp->salary_type); ?>",
            commission_type: "<?php echo e($emp->commission_type); ?>",
            commission_value: <?php echo e($emp->commission_value); ?>

        },
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    ];

    let rowIndex = 0;

    function createAssignmentRow() {
        const idx = rowIndex++;
        const row = document.createElement('div');
        row.className = 'assignment-row p-4 bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-700 rounded-2xl relative space-y-3';
        row.dataset.index = idx;

        let optionsHtml = '<option value="">— Select Employee —</option>';
        employees.forEach(emp => {
            optionsHtml += `<option value="${emp.id}">${emp.name}</option>`;
        });

        row.innerHTML = `
            <button type="button" class="remove-row-btn absolute top-3 right-3 text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee *</label>
                    <select name="assignments[${idx}][employee_id]" required class="employee-select w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        ${optionsHtml}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned GST Count (Optional)</label>
                    <input type="number" name="assignments[${idx}][gst_count]" min="0" value="0" class="gst-count-input w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GST Platform (Optional)</label>
                    <select name="assignments[${idx}][gst_platform]" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                        <option value="">— Select Platform —</option>
                        <option value="flipkart">Flipkart</option>
                        <option value="meesho">Meesho</option>
                    </select>
                </div>
            </div>
            <div class="commission-config grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Commission Type</label>
                    <select name="assignments[${idx}][commission_type]" class="commission-type w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                        <option value="fixed_amount">Fixed Amount (₹)</option>
                        <option value="percentage">Percentage (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">INR (Base Amount)</label>
                    <input type="number" step="0.01" min="0" name="assignments[${idx}][custom_package_amount]" class="custom-package w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" placeholder="e.g. 5000">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Percentage / Value</label>
                    <input type="number" step="0.01" min="0" name="assignments[${idx}][commission_value]" class="commission-value w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" placeholder="e.g. 10">
                </div>
            </div>
            <div class="calculation-result text-right text-xs font-semibold text-gray-700 dark:text-gray-300 hidden">
                Calculated Commission: <span class="calc-text text-blue-600 font-bold">₹0.00</span>
            </div>
        `;

        container.appendChild(row);

        // Bind Events
        const empSelect = row.querySelector('.employee-select');
        const commConfig = row.querySelector('.commission-config');
        const calcResult = row.querySelector('.calculation-result');
        const commType = row.querySelector('.commission-type');
        const customPkg = row.querySelector('.custom-package');
        const commVal = row.querySelector('.commission-value');
        const removeBtn = row.querySelector('.remove-row-btn');

        function updateRowComm() {
            const empId = empSelect.value;
            if (!empId) {
                commConfig.classList.add('hidden');
                calcResult.classList.add('hidden');
                return;
            }

            const emp = employees.find(e => e.id == empId);
            if (emp.salary_type === 'package_based' || emp.salary_type === 'both') {
                commConfig.classList.remove('hidden');
                calcResult.classList.remove('hidden');
            } else {
                commConfig.classList.add('hidden');
                calcResult.classList.add('hidden');
                return;
            }

            const mainPkg = parseFloat(packageInput.value) || 0;
            if (!customPkg.value) {
                customPkg.placeholder = mainPkg;
            }

            const base = parseFloat(customPkg.value) || mainPkg;
            const type = commType.value;
            const val = parseFloat(commVal.value) || 0;

            let finalComm = 0;
            if (type === 'fixed_amount') {
                finalComm = val;
            } else {
                finalComm = (base * val) / 100;
            }

            row.querySelector('.calc-text').textContent = '₹' + finalComm.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        }

        empSelect.addEventListener('change', function() {
            const empId = empSelect.value;
            if (empId) {
                const emp = employees.find(e => e.id == empId);
                commType.value = emp.commission_type || 'fixed_amount';
                commVal.value = emp.commission_value || 0;
                updateRowComm();
            } else {
                updateRowComm();
            }
        });

        commType.addEventListener('change', updateRowComm);
        customPkg.addEventListener('input', updateRowComm);
        commVal.addEventListener('input', updateRowComm);

        removeBtn.addEventListener('click', function() {
            row.remove();
        });
    }

    // Add initial row
    createAssignmentRow();

    addBtn.addEventListener('click', createAssignmentRow);

    packageInput.addEventListener('input', function() {
        document.querySelectorAll('.assignment-row').forEach(row => {
            const empSelect = row.querySelector('.employee-select');
            if (empSelect.value) {
                empSelect.dispatchEvent(new Event('change'));
            }
        });
    });

    const addSecondaryMobileBtn = document.getElementById('add-secondary-mobile');
    const secondaryMobileContainer = document.getElementById('secondary-mobile-container');
    if (addSecondaryMobileBtn && secondaryMobileContainer) {
        addSecondaryMobileBtn.addEventListener('click', function() {
            secondaryMobileContainer.classList.toggle('hidden');
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jigar\OneDrive\Documents\.l\Listing website v1\resources\views/clients/create.blade.php ENDPATH**/ ?>