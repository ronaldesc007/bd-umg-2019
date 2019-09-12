<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\ControllerPelicula;

use App\Http\Controllers\ControllerSincro;

use App\Http\Controllers\ControllerActor;


// All route names are prefixed with 'admin.'.
Route::redirect('/', '/admin/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('peliculas', [ControllerPelicula::class, 'index'])->name('peliculas');
Route::get('actor', [ControllerActor::class, 'index'])->name('actor');
Route::post('peliculas/guardar', [ControllerPelicula::class, 'store'])->name('peliculas.guardar');
Route::get('peliculas/crear', [ControllerPelicula::class, 'create'])->name('peliculas.crear');

Route::delete('peliculas/{cod_pelicula}', [ControllerPelicula::class, 'destroy'])->name('peliculas.eliminar');
Route::get('peliculas/{cod_pelicula}', [ControllerPelicula::class, 'show'])->name('peliculas.ver');
Route::get('peliculas/{cod_pelicula}/editar', [ControllerPelicula::class, 'edit'])->name('peliculas.editar');

Route::get('sincronizacion', [ControllerSincro::class, 'index'])->name('sincronizacion');

Route::post('actor/guardar', [ControllerPelicula::class, 'store'])->name('actor.guardar');
Route::get('actor/crear', [ControllerPelicula::class, 'create'])->name('actor.crear');
