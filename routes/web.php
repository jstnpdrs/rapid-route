<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return to_route('login');
    // return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
    Route::get('dashboard1', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard1');
    Route::get('users', [UserController::class, 'index'])->name('user.index');
    Route::middleware(App\Http\Middleware\isAdmin::class)->put('users/{user}', [UserController::class, 'approve'])->name('user.approve');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
