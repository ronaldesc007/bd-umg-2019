<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\ControllerPelicula;

// All route names are prefixed with 'admin.'.
Route::redirect('/', '/admin/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('peliculas', [ControllerPelicula::class, 'index'])->name('peliculas');
Route::get('peliculas/guardar', [ControllerPelicula::class, 'store'])->name('peliculas.guardar');

