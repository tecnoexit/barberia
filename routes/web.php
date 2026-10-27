<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Route::get('/logo.jpg', fn (): BinaryFileResponse => response()->file(base_path('logo.jpg')))->name('brand.logo');
Route::get('/fondo.jpg', fn (): BinaryFileResponse => response()->file(base_path('fondo.jpg')))->name('brand.background');

Route::get('/', [BookingController::class, 'home'])->name('home');
Route::get('/slots', [BookingController::class, 'slots'])->name('slots');
Route::post('/reservas', [BookingController::class, 'store'])->name('appointments.store');
Route::get('/login', [AuthController::class, 'form'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/panel', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/panel/turnos/{appointment}', [DashboardController::class, 'status'])->name('appointments.status');
    Route::prefix('panel/admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/servicios', [AdminController::class, 'serviceStore'])->name('services.store');
        Route::patch('/servicios/{service}', [AdminController::class, 'serviceUpdate'])->name('services.update');
        Route::delete('/servicios/{service}', [AdminController::class, 'serviceDestroy'])->name('services.destroy');
        Route::post('/barberos', [AdminController::class, 'barberStore'])->name('barbers.store');
        Route::patch('/barberos/{barber}', [AdminController::class, 'barberUpdate'])->name('barbers.update');
        Route::put('/barberos/{barber}/horarios', [AdminController::class, 'schedules'])->name('schedules.update');
        Route::post('/turnos', [AdminController::class, 'appointmentStore'])->name('appointments.store');
    });
});
