<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: true, sidebarMobileOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> — <?php echo e(\App\Models\Setting::get('company_name', 'Listing ERP')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { 'poppins': ['Poppins', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                        sidebar: '#1E293B',
                        'sidebar-hover': '#334155',
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
        .sidebar-transition { transition: width 0.3s ease, transform 0.3s ease; }
        .content-transition { transition: margin-left 0.3s ease; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .card-hover { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-poppins antialiased">
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar Backdrop for mobile -->
        <div x-show="sidebarMobileOpen" 
             x-transition:opacity
             class="fixed inset-0 z-45 bg-gray-900/50 backdrop-blur-sm lg:hidden"
             @click="sidebarMobileOpen = false"
             style="display: none;"></div>

        
        <aside class="sidebar-transition fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 text-gray-800 dark:text-gray-200"
               :class="sidebarMobileOpen ? 'w-64 translate-x-0' : (sidebarOpen ? 'w-64 -translate-x-full lg:translate-x-0' : 'w-20 -translate-x-full lg:translate-x-0')">

            
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100 dark:border-gray-800">
                <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md shadow-blue-500/30">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <span class="text-md font-bold tracking-tight text-gray-900 dark:text-white whitespace-nowrap" x-show="sidebarOpen" x-transition>Listing ERP</span>
                </a>
                <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 hidden lg:block">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5" x-data="{
                pinnedCategories: {
                    'menu-clients': <?php echo e(request()->routeIs('clients.*') || request()->routeIs('payments.*') ? 'true' : 'false'); ?>,
                    'menu-employees': <?php echo e(request()->routeIs('employees.*') || request()->routeIs('salary.*') || request()->routeIs('admin.advances*') || request()->routeIs('admin.holidays*') ? 'true' : 'false'); ?>,
                    'menu-investors': <?php echo e(request()->routeIs('investors.*') || request()->routeIs('investments.*') ? 'true' : 'false'); ?>

                },
                hoveredCategory: null
            }">
                <?php
                    if (auth()->user()->hasRole('Employee')) {
                        $sidebarMenu = [
                            ['type' => 'link', 'route' => 'employee.dashboard', 'label' => 'My Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'active' => 'employee.dashboard'],
                            ['type' => 'link', 'route' => 'employee.clients', 'label' => 'My Clients', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'active' => 'employee.clients'],
                            ['type' => 'link', 'route' => 'employee.salaries', 'label' => 'Salaries Payout', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'active' => 'employee.salaries'],
                            ['type' => 'link', 'route' => 'employee.advances', 'label' => 'Advance Requests', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'active' => 'employee.advances'],
                            ['type' => 'link', 'route' => 'employee.holidays', 'label' => 'Holiday Requests', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'active' => 'employee.holidays'],
                        ];
                    } else {
                        $sidebarMenu = [
                            [
                                'type' => 'link',
                                'route' => 'dashboard',
                                'label' => 'Dashboard',
                                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                                'active' => 'dashboard'
                            ],
                            [
                                'type' => 'category',
                                'label' => 'Clients Management',
                                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                                'id' => 'menu-clients',
                                'active_patterns' => ['clients.*', 'payments.*'],
                                'children' => [
                                    ['route' => 'clients.index', 'label' => 'All Clients', 'active' => 'clients.*'],
                                    ['route' => 'payments.index', 'label' => 'Payments', 'active' => 'payments.*'],
                                ]
                            ],
                            [
                                'type' => 'category',
                                'label' => 'Employees',
                                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                'id' => 'menu-employees',
                                'active_patterns' => ['employees.*', 'salary.*', 'admin.advances', 'admin.holidays'],
                                'children' => [
                                    ['route' => 'employees.index', 'label' => 'All Employees', 'active' => 'employees.*'],
                                    ['route' => 'salary.index', 'label' => 'Salary & Payroll', 'active' => 'salary.*'],
                                    ['route' => 'admin.advances', 'label' => 'Advance Requests', 'active' => 'admin.advances'],
                                    ['route' => 'admin.holidays', 'label' => 'Holiday Requests', 'active' => 'admin.holidays'],
                                ]
                            ],
                            [
                                'type' => 'category',
                                'label' => 'Investors Group',
                                'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                                'id' => 'menu-investors',
                                'active_patterns' => ['investors.*', 'investments.*'],
                                'children' => [
                                    ['route' => 'investors.index', 'label' => 'All Investors', 'active' => 'investors.*'],
                                    ['route' => 'investments.index', 'label' => 'Investments Ledger', 'active' => 'investments.*'],
                                ]
                            ],
                            [
                                'type' => 'link',
                                'route' => 'work-tracker.index',
                                'label' => 'Work Tracker',
                                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                                'active' => 'work-tracker.*'
                            ],
                            [
                                'type' => 'link',
                                'route' => 'expenses.index',
                                'label' => 'Expenses',
                                'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z',
                                'active' => 'expenses.*'
                            ],
                            [
                                'type' => 'link',
                                'route' => 'reports.index',
                                'label' => 'Reports',
                                'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                'active' => 'reports.*'
                            ],
                            [
                                'type' => 'link',
                                'route' => 'activity-logs.index',
                                'label' => 'Activity Logs',
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'active' => 'activity-logs.*',
                                'role' => 'Main Admin'
                            ],
                            [
                                'type' => 'link',
                                'route' => 'settings.index',
                                'label' => 'Settings',
                                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                                'active' => 'settings.*'
                            ],
                        ];
                    }
                ?>

                <?php $__currentLoopData = $sidebarMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(isset($item['role']) && !auth()->user()->hasRole($item['role'])): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if($item['type'] === 'category'): ?>
                        <div class="space-y-1"
                             @mouseenter="hoveredCategory = '<?php echo e($item['id']); ?>'"
                             @mouseleave="hoveredCategory = null">
                            <button @click="pinnedCategories['<?php echo e($item['id']); ?>'] = !pinnedCategories['<?php echo e($item['id']); ?>']"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white focus:outline-none">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 flex-shrink-0 text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($item['icon']); ?>"/>
                                    </svg>
                                    <span class="ml-3 text-sm font-medium" x-show="sidebarOpen" x-transition><?php echo e($item['label']); ?></span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="pinnedCategories['<?php echo e($item['id']); ?>'] || hoveredCategory === '<?php echo e($item['id']); ?>' ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="sidebarOpen" x-transition>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <div x-show="pinnedCategories['<?php echo e($item['id']); ?>'] || hoveredCategory === '<?php echo e($item['id']); ?>'" x-transition class="pl-9 pr-2 py-1 space-y-1">
                                <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $isActive = false;
                                        if (isset($child['anchor'])) {
                                            $isActive = request()->url() === route($child['route']);
                                        } else {
                                            $isActive = request()->routeIs($child['active']);
                                        }
                                    ?>
                                    <a href="<?php echo e(route($child['route'])); ?><?php echo e(isset($child['anchor']) ? $child['anchor'] : ''); ?>"
                                       class="block px-3 py-2 text-xs rounded-lg transition-all duration-150 
                                              <?php echo e($isActive ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white'); ?>">
                                        <?php echo e($child['label']); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo e(route($item['route'])); ?>"
                           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group
                                  <?php echo e(request()->routeIs($item['active']) ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white'); ?>">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors <?php echo e(request()->routeIs($item['active']) ? 'text-white' : 'text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($item['icon']); ?>"/>
                            </svg>
                            <span class="ml-3 text-sm" x-show="sidebarOpen" x-transition><?php echo e($item['label']); ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            
            <?php if(request()->routeIs('clients.*') || request()->routeIs('dashboard')): ?>
            <div class="px-5 py-4 mx-4 mb-4 bg-gray-50/50 dark:bg-gray-850 rounded-2xl border border-gray-100 dark:border-gray-800" x-show="sidebarOpen" x-transition>
                <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 mb-3 tracking-tight">Quick Stats</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Total Clients</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 dark:text-white"><?php echo e(\App\Models\Client::count()); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Active Clients</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 dark:text-white"><?php echo e(\App\Models\Client::where('status', 'active')->count()); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Inactive Clients</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 dark:text-white"><?php echo e(\App\Models\Client::where('status', 'inactive')->count()); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Payment Due</span>
                        </div>
                        <span class="text-xs font-bold text-red-600">₹<?php echo e(number_format(\App\Models\ClientBillingCycle::whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance'), 0)); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between" x-show="sidebarOpen">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0 shadow-sm">
                        <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate"><?php echo e(auth()->user()->name); ?></p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 truncate">Main Admin</p>
                    </div>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="flex items-center justify-center">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        
        <div class="flex-1 flex flex-col content-transition overflow-hidden bg-gray-50/50 dark:bg-gray-950" :class="sidebarOpen ? 'ml-0 lg:ml-64' : 'ml-0 lg:ml-20'">

            
            <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between px-8 flex-shrink-0">
                <div class="flex items-center space-x-6">
                    <button @click="sidebarMobileOpen = !sidebarMobileOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    
                    <div class="relative w-72 hidden md:block">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" placeholder="Search anything..." class="w-full pl-9 pr-12 py-1.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-gray-600 dark:text-gray-300">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-[9px] font-semibold text-gray-400 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded px-1 py-0.5">CTRL + K</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-5">
                    
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                            class="p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-gray-500">
                        <svg x-show="!darkMode" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    
                    <div x-data="{ notifOpen: false }" class="relative">
                        <button @click="notifOpen = !notifOpen" class="p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 relative text-gray-500">
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <?php if(auth()->user()->unreadNotifications()->count() > 0): ?>
                                <span class="absolute top-1.5 right-1.5 bg-red-500 w-1.5 h-1.5 rounded-full"></span>
                            <?php endif; ?>
                        </button>
                        <div x-show="notifOpen" @click.outside="notifOpen = false" x-transition
                             class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-y-auto">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="font-semibold text-gray-800 dark:text-white">Notifications</h3>
                                <a href="<?php echo e(route('notifications.index')); ?>" class="text-blue-600 text-sm hover:underline">View All</a>
                            </div>
                            <?php $__empty_1 = true; $__currentLoopData = auth()->user()->unreadNotifications()->latest()->limit(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-800 dark:text-white"><?php echo e($notif->title); ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo e($notif->message); ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?php echo e($notif->created_at->diffForHumans()); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="p-4 text-center text-gray-500 text-sm">No new notifications</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full overflow-hidden shadow-sm">
                            <div class="w-full h-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                                <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </header>

            
            <main class="flex-1 overflow-y-auto p-6">
                
                <?php if(session('success')): ?>
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                         class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <?php echo e(session('success')); ?>

                        </div>
                        <button @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div x-data="{ show: true }" x-show="show" x-transition
                         class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <?php echo e(session('error')); ?>

                        </div>
                        <button @click="show = false" class="text-red-500 hover:text-red-700">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\jigar\OneDrive\Documents\.l\Listing website v1\resources\views/layouts/app.blade.php ENDPATH**/ ?>