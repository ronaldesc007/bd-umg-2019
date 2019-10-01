<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\ControllerPelicula;
use App\Http\Controllers\ControllerActor;
use App\Http\Controllers\ControllerReparto;
use App\Http\Controllers\ControllerCliente;
use App\Http\Controllers\ControllerDisco;
use App\Http\Controllers\ControllerRenta;

use App\Http\Controllers\ControllerSincro;




// All route names are prefixed with 'admin.'.
Route::redirect('/', '/admin/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('peliculas', [ControllerPelicula::class, 'index'])->name('peliculas');
Route::get('actores', [ControllerActor::class, 'index'])->name('actores');
Route::get('repartos', [ControllerReparto::class, 'index'])->name('repartos');
Route::get('clientes', [ControllerCliente::class, 'index'])->name('clientes');
Route::get('discos', [ControllerDisco::class, 'index'])->name('discos');
Route::get('rentas', [ControllerRenta::class, 'index'])->name('rentas');

Route::post('peliculas/guardar', [ControllerPelicula::class, 'store'])->name('peliculas.guardar');
Route::get('peliculas/crear', [ControllerPelicula::class, 'create'])->name('peliculas.crear');
Route::delete('peliculas/{cod_pelicula}', [ControllerPelicula::class, 'destroy'])->name('peliculas.eliminar');
Route::get('peliculas/{cod_pelicula}', [ControllerPelicula::class, 'show'])->name('peliculas.ver');
Route::get('peliculas/{cod_pelicula}/editar', [ControllerPelicula::class, 'edit'])->name('peliculas.editar');
Route::post('peliculas/{cod_pelicula}', [ControllerPelicula::class, 'update'])->name('peliculas.update');

Route::post('actores/guardar', [ControllerActor::class, 'store'])->name('actores.guardar');
Route::get('actores/crear', [ControllerActor::class, 'create'])->name('actores.crear');
Route::delete('actores/{cod_actor}', [ControllerActor::class, 'destroy'])->name('actores.eliminar');
Route::get('actores/{cod_actor}', [ControllerActor::class, 'show'])->name('actores.ver');
Route::get('actores/{cod_actor}/editar', [ControllerActor::class, 'edit'])->name('actores.editar');
Route::post('actores/{cod_actor}', [ControllerActor::class, 'update'])->name('actores.update');

Route::post('repartos/guardar', [ControllerReparto::class, 'store'])->name('repartos.guardar');
Route::get('repartos/crear', [ControllerReparto::class, 'create'])->name('repartos.crear');
Route::delete('repartos/{cod_reparto}', [ControllerReparto::class, 'destroy'])->name('repartos.eliminar');
Route::get('repartos/{cod_reparto}', [ControllerReparto::class, 'show'])->name('repartos.ver');
Route::get('repartos/{cod_reparto}/editar', [ControllerReparto::class, 'edit'])->name('repartos.editar');
Route::post('repartos/{cod_reparto}', [ControllerReparto::class, 'update'])->name('repartos.update');

Route::post('clientes/guardar', [ControllerCliente::class, 'store'])->name('clientes.guardar');
Route::get('clientes/crear', [ControllerCliente::class, 'create'])->name('clientes.crear');
Route::delete('clientes/{no_membresia}', [ControllerCliente::class, 'destroy'])->name('clientes.eliminar');
Route::get('clientes/{no_membresia}', [ControllerCliente::class, 'show'])->name('clientes.ver');
Route::get('clientes/{no_membresia}/editar', [ControllerCliente::class, 'edit'])->name('clientes.editar');
Route::post('clientes/{no_membresia}', [ControllerCliente::class, 'update'])->name('clientes.update');

Route::post('discos/guardar', [ControllerDisco::class, 'store'])->name('discos.guardar');
Route::get('discos/crear', [ControllerDisco::class, 'create'])->name('discos.crear');
Route::delete('discos/{cod_disco}', [ControllerDisco::class, 'destroy'])->name('discos.eliminar');
Route::get('discos/{cod_disco}', [ControllerDisco::class, 'show'])->name('discos.ver');
Route::get('discos/{cod_disco}/editar', [ControllerDisco::class, 'edit'])->name('discos.editar');
Route::post('discos/{cod_disco}', [ControllerDisco::class, 'update'])->name('discos.update');

Route::post('rentas/guardar', [ControllerRenta::class, 'store'])->name('rentas.guardar');
Route::get('rentas/crear', [ControllerRenta::class, 'create'])->name('rentas.crear');
Route::delete('rentas/{cod_disco}', [ControllerRenta::class, 'destroy'])->name('rentas.eliminar');
Route::get('rentas/{cod_disco}', [ControllerRenta::class, 'show'])->name('rentas.ver');
Route::get('rentas/{cod_disco}/editar', [ControllerRenta::class, 'edit'])->name('rentas.editar');
Route::post('rentas/{cod_disco}', [ControllerRenta::class, 'update'])->name('rentas.update');


Route::get('sincronizacion', [ControllerSincro::class, 'index'])->name('sincronizacion');



