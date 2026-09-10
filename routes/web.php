<?php

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\LockScreenController;
use App\Http\Controllers\TenantDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', WelcomeController::class);

Route::get('product-images/{path}', function (string $path) {
    $path = urldecode($path);
    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    $response = $disk->response($path);
    $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

    return $response;
})->where('path', '.*')->name('product-images');

Route::get('user-avatars/{path}', function (string $path) {
    $path = urldecode($path);
    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    $response = $disk->response($path);
    $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

    return $response;
})->where('path', '.*')->name('user-avatars');

// ── Platform Admin Routes (BEFORE store catch-all) ─────────────────
require __DIR__.'/platform.php';

// ── Public Registration ────────────────────────────────────────────
Route::get('/register', [\App\Http\Controllers\RegistrationController::class, 'showForm'])->name('registration.show');
Route::post('/register', [\App\Http\Controllers\RegistrationController::class, 'register'])->name('registration.store');
Route::get('/register/sent', [\App\Http\Controllers\RegistrationController::class, 'sent'])->name('registration.sent');
Route::post('/register/resend', [\App\Http\Controllers\RegistrationController::class, 'resend'])->name('registration.resend');

// ── Account Activation ─────────────────────────────────────────────
Route::get('/create-account/{token}', [\App\Http\Controllers\AccountActivationController::class, 'showForm'])->name('activation.show');
Route::post('/create-account/{token}', [\App\Http\Controllers\AccountActivationController::class, 'createAccount'])->name('activation.store');

// ── Onboarding (auth required) ─────────────────────────────────────
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('index');
    Route::get('/{step}', [\App\Http\Controllers\OnboardingController::class, 'step'])->name('step');
    Route::post('/business', [\App\Http\Controllers\OnboardingController::class, 'saveBusiness'])->name('save-business');
    Route::post('/branch', [\App\Http\Controllers\OnboardingController::class, 'saveBranch'])->name('save-branch');
    Route::post('/pos', [\App\Http\Controllers\OnboardingController::class, 'savePos'])->name('save-pos');
    Route::post('/tax', [\App\Http\Controllers\OnboardingController::class, 'saveTax'])->name('save-tax');
    Route::post('/receipt', [\App\Http\Controllers\OnboardingController::class, 'saveReceipt'])->name('save-receipt');
    Route::post('/payment-methods', [\App\Http\Controllers\OnboardingController::class, 'savePaymentMethods'])->name('save-payment-methods');
    Route::post('/products', [\App\Http\Controllers\OnboardingController::class, 'saveProducts'])->name('save-products');
    Route::post('/opening-stock', [\App\Http\Controllers\OnboardingController::class, 'saveOpeningStock'])->name('save-opening-stock');
    Route::post('/team', [\App\Http\Controllers\OnboardingController::class, 'saveTeam'])->name('save-team');
    Route::post('/hardware', [\App\Http\Controllers\OnboardingController::class, 'saveHardware'])->name('save-hardware');
    Route::get('/skip/{step}', [\App\Http\Controllers\OnboardingController::class, 'skip'])->name('skip');
    Route::get('/complete', [\App\Http\Controllers\OnboardingController::class, 'complete'])->name('complete');
});

// ── Logout (works for onboarding, tenant, and platform) ────────────
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware('auth')->prefix('tenant')->group(function () {
    Route::get('/lock-status', [LockScreenController::class, 'status'])->name('tenant.lock-status');
    Route::post('/lock', [LockScreenController::class, 'lock'])->name('tenant.lock');
    Route::post('/unlock', [LockScreenController::class, 'unlock'])->name('tenant.unlock');

    Route::post('/switch-language', function (Illuminate\Http\Request $request) {
        $code = $request->input('language');
        $user = $request->user();
        if ($user && $code) {
            $user->forceFill(['language' => $code])->save();
            session(['locale' => $code]);
        }
        return redirect('/tenant');
    })->name('tenant.switch-language');

    Route::post('/switch-branch', function (Illuminate\Http\Request $request) {
        $branchId = $request->input('branch');
        $user = $request->user();
        if ($user && $branchId) {
            $branchService = app(\App\Services\BranchService::class);
            if ($branchService->setActiveBranch($branchId)) {
                return redirect('/tenant')->with('branch_status', 'Branch switched successfully.');
            }
            return redirect('/tenant')->with('branch_status', 'You do not have access to that branch.');
        }
        return redirect('/tenant');
    })->name('tenant.switch-branch');

    Route::post('/user-avatars/upload', function (Illuminate\Http\Request $request) {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        $user = $request->user();
        $old = $user->avatar_url;
        $path = $request->file('avatar')->store('avatars', 'public');

        if ($old && $old !== $path) {
            Storage::disk('public')->delete($old);
        }

        $user->avatar_url = $path;
        $user->save();

        return redirect()->back()->with('avatar_status', 'Profile photo updated successfully.');
    })->name('user-avatars.upload');

    Route::post('/user-avatars/remove', function (Illuminate\Http\Request $request) {
        $user = $request->user();

        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
            $user->avatar_url = null;
            $user->save();
        }

        return redirect()->back()->with('avatar_status', 'Profile photo removed.');
    })->name('user-avatars.remove');
});

Route::prefix('{slug}')->name('store.')->group(function () {
    Route::get('/', [\App\Http\Controllers\StoreController::class, 'index'])->name('home');
    Route::get('/products', [\App\Http\Controllers\StoreController::class, 'products'])->name('products');
    Route::get('/product/{id}', [\App\Http\Controllers\StoreController::class, 'productDetail'])->name('product');
    Route::get('/cart', [\App\Http\Controllers\StoreController::class, 'cart'])->name('cart');
    Route::post('/cart/add', [\App\Http\Controllers\StoreController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update', [\App\Http\Controllers\StoreController::class, 'updateCart'])->name('cart.update');
    Route::post('/cart/remove', [\App\Http\Controllers\StoreController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/checkout', [\App\Http\Controllers\StoreController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/place-order', [\App\Http\Controllers\StoreController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/order/{orderNumber}', [\App\Http\Controllers\StoreController::class, 'orderTracking'])->name('order-tracking');
    Route::get('/cart/count', [\App\Http\Controllers\StoreController::class, 'cartCount'])->name('cart-count');
});

