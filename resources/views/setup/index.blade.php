<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard — Listing ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>* { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-600 via-teal-700 to-cyan-800 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl" x-data="{ step: 1 }">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-3xl mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h1 class="text-4xl font-bold text-white">Welcome to Listing ERP</h1>
            <p class="text-teal-200 mt-2 text-lg">Let's set up your system in a few simple steps</p>
        </div>

        {{-- Steps Indicator --}}
        <div class="flex justify-center mb-8 space-x-4">
            <div class="flex items-center">
                <div :class="step >= 1 ? 'bg-white text-teal-700' : 'bg-white/30 text-white'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all">1</div>
                <span class="ml-2 text-white text-sm font-medium">Company</span>
            </div>
            <div class="flex items-center"><div class="w-12 h-0.5 bg-white/30"></div></div>
            <div class="flex items-center">
                <div :class="step >= 2 ? 'bg-white text-teal-700' : 'bg-white/30 text-white'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all">2</div>
                <span class="ml-2 text-white text-sm font-medium">Admin Account</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl p-8">
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('setup.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Step 1: Company Info --}}
                <div x-show="step === 1" x-transition>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Company Information</h2>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', 'Listing ERP') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Logo (Optional)</label>
                        <input type="file" name="company_logo" accept="image/*"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Currency *</label>
                            <select name="currency" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="₹" selected>₹ INR (Indian Rupee)</option>
                                <option value="$">$ USD (US Dollar)</option>
                                <option value="€">€ EUR (Euro)</option>
                                <option value="£">£ GBP (British Pound)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timezone *</label>
                            <select name="timezone" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                            </select>
                        </div>
                    </div>

                    <button type="button" @click="step = 2" class="w-full py-3 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-lg transition-all">
                        Next → Admin Account
                    </button>
                </div>

                {{-- Step 2: Admin Account --}}
                <div x-show="step === 2" x-transition>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Admin Account</h2>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none" placeholder="Your full name">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none" placeholder="admin@company.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                            <input type="text" name="admin_username" value="{{ old('admin_username', 'admin') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none" placeholder="admin">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <input type="password" name="admin_password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none" placeholder="Min 8 characters">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                            <input type="password" name="admin_password_confirmation" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none" placeholder="Re-enter password">
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" @click="step = 1" class="flex-1 py-3 px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-all">← Back</button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-lg shadow-emerald-600/30 transition-all">
                            🚀 Complete Setup
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
