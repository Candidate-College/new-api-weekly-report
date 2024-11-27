<?php

use App\Http\Controllers\Web\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserController;

Route::get('/', function () {
    return view('welcome');
});

// =========================================== Admin Panel ===========================================

// Login Routes (Only accessible to guests)
Route::prefix('admin')
    ->middleware('guest:web')
    ->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('admin.login.post');
    });

// User Management Routes (Only accessible to authenticated users using the 'web' guard)
Route::prefix('admin')
    ->middleware(['auth:web', 'check.admin']) 
    ->group(function () {
        Route::get('/dashboard', [UserController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.user');
        Route::post('/users/updateFlags', [UserController::class, 'updateFlags'])->name('admin.users.updateFlags');
        Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');
    });
