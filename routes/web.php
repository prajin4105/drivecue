<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return redirect($user->isSuperAdmin() ? '/admin' : '/dashboard');
    }
    return view('welcome');
});

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::post('dashboard/bulk-whatsapp', [DashboardController::class, 'bulkWhatsapp'])
    ->middleware(['auth'])
    ->name('dashboard.bulk-whatsapp');

Route::get('reports', [ReportController::class, 'index'])
    ->middleware(['auth'])
    ->name('reports.index');

use App\Http\Controllers\WhatsappLogController;
Route::get('whatsapp-logs', [WhatsappLogController::class, 'index'])
    ->middleware(['auth'])
    ->name('whatsapp-logs.index');

Route::get('pricing', [PricingController::class, 'index'])->name('pricing.index');
Route::middleware('auth')->group(function () {
    Route::get('checkout/{plan}', [PaymentController::class, 'checkout'])->name('checkout');
    Route::post('payments/razorpay/order', [PaymentController::class, 'createOrder'])->name('payments.create-order');
    Route::post('payments/razorpay/verify', [PaymentController::class, 'verify'])->name('payments.verify');
});
Route::post('payments/razorpay/webhook', [PaymentController::class, 'webhook'])->withoutMiddleware(VerifyCsrfToken::class)->name('payments.webhook');

use App\Http\Controllers\ProfileController;

Route::get('profile', [ProfileController::class, 'edit'])
    ->middleware(['auth'])
    ->name('profile');

Route::post('profile/update', [ProfileController::class, 'update'])
    ->middleware(['auth'])
    ->name('profile.update');

Route::post('profile/password', [ProfileController::class, 'updatePassword'])
    ->middleware(['auth'])
    ->name('profile.password');

use App\Http\Controllers\CustomerController;

Route::middleware(['auth'])->group(function () {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::post('customers/{id}/update', [CustomerController::class, 'update'])->name('customers.update');
    Route::post('customers/{id}/delete', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
});

use App\Http\Controllers\BillingController;

Route::middleware(['auth'])->group(function () {
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('billing/invoice/{payment}', [BillingController::class, 'invoice'])->name('billing.invoice');
});

require __DIR__.'/auth.php';
