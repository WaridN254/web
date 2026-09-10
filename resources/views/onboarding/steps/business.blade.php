<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Business Details — HALIS Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('onboarding.partials.topbar', ['progress' => $progress])

    <div class="max-w-2xl mx-auto px-6 py-10">
        @include('onboarding.partials.stepper', ['current' => 'business', 'progress' => $progress])

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Business Details</h2>
            <p class="text-sm text-gray-500 mb-6">Tell us about your business.</p>

            <form method="POST" action="{{ route('onboarding.save-business') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                    <input type="text" name="business_name" value="{{ old('business_name', $tenant->name) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <select name="country" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="UG">Uganda</option>
                            <option value="KE">Kenya</option>
                            <option value="TZ">Tanzania</option>
                            <option value="RW">Rwanda</option>
                            <option value="NG">Nigeria</option>
                            <option value="US">United States</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="UGX">UGX — Ugandan Shilling</option>
                            <option value="KES">KES — Kenyan Shilling</option>
                            <option value="TZS">TZS — Tanzanian Shilling</option>
                            <option value="RWF">RWF — Rwandan Franc</option>
                            <option value="NGN">NGN — Nigerian Naira</option>
                            <option value="USD">USD — US Dollar</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                    <select name="timezone" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="Africa/Kampala">Africa/Kampala (EAT)</option>
                        <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                        <option value="Africa/Dar_es_Salaam">Africa/Dar es Salaam (EAT)</option>
                        <option value="Africa/Kigali">Africa/Kigali (CAT)</option>
                        <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
                        <option value="America/New_York">America/New York (EST)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="+256 700 000000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">TIN Number <span class="text-gray-400">(optional)</span></label>
                        <input type="text" name="tin_number" value="{{ old('tin_number') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Street, City">
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('onboarding.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Back
                    </a>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">
                        Save & Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
