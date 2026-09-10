<div x-data="{ open: false }" class="relative">
    <!-- Add New Button -->
    <button
        @click="open = true"
        class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition duration-200"
    >
        <span class="mr-2">+</span>
        Add New
    </button>

    <!-- Modal Backdrop -->
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 bg-black/50 z-40"
        @click="open = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="open"
        x-transition
        class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-slate-800 rounded-lg shadow-2xl z-50 p-8 w-full max-w-5xl max-h-[80vh] overflow-auto"
    >
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Quick Create</h3>
            <button
                @click="open = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Quick Create Grid - 6 Columns -->
        <div class="grid grid-cols-6 gap-4 mb-8">
            <!-- Branch -->
            <a href="/tenant/branches/create" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-indigo-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Branch</span>
            </a>

            <!-- Category -->
            <a href="/tenant/categories/create" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-blue-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Category</span>
            </a>

            <!-- Product -->
            <a href="/tenant/products/create" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-purple-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m0 0L4 7m16 0v10l-8 4m0-10L4 7v10l8 4"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Product</span>
            </a>

            <!-- Purchase -->
            <a href="/tenant/purchase-orders/create" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-green-500 hover:bg-green-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-green-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Purchase</span>
            </a>

            <!-- Sale -->
            <a href="/tenant/sales-page" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-red-500 hover:bg-red-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-red-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 8m0 0h12m0 0l2-8M9 21a1 1 0 11-2 0 1 1 0 012 0zm8 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Sale</span>
            </a>

            <!-- Expense -->
            <a href="/tenant/expenses" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-yellow-500 hover:bg-yellow-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-yellow-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Expense</span>
            </a>

            <!-- Quotation -->
            <a href="/tenant/quotation-page" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-indigo-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Quotation</span>
            </a>

            <!-- Return -->
            <a href="/tenant/sales-return-page" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-pink-500 hover:bg-pink-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-pink-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Return</span>
            </a>

            <!-- User -->
            <a href="/tenant/users/create" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-cyan-500 hover:bg-cyan-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-cyan-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">User</span>
            </a>

            <!-- Customer -->
            <a href="/tenant/customers/create" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-teal-500 hover:bg-teal-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-teal-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20a9 9 0 0118 0v2H4v-2a9 9 0 0118 0v2H6v-2z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Customer</span>
            </a>

            <!-- Biller -->
            <a href="#" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-orange-500 hover:bg-orange-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-orange-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Biller</span>
            </a>

            <!-- Supplier -->
            <a href="/tenant/suppliers/create" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-emerald-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Supplier</span>
            </a>

            <!-- Transfer -->
            <a href="#" class="flex flex-col items-center justify-center p-5 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-slate-700 transition group cursor-pointer">
                <svg class="w-12 h-12 text-violet-500 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Transfer</span>
            </a>
        </div>

        <!-- Branch Selector -->
        <div class="border-t border-gray-200 dark:border-slate-700 pt-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Select Branch</label>
            <select class="w-full px-4 py-3 border-2 border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                <option value="">-- Select a branch --</option>
                <option value="1">Main Branch</option>
                <option value="2">Downtown Branch</option>
                <option value="3">Mall Branch</option>
            </select>
        </div>
    </div>
</div>
