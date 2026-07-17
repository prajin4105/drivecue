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

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::post('dashboard/bulk-whatsapp', [DashboardController::class, 'bulkWhatsapp'])
    ->middleware(['auth'])
    ->name('dashboard.bulk-whatsapp');

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

require __DIR__.'/auth.php';
