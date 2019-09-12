<?php

namespace App\Http\Controllers;

use App\ModelPelicula;
use Illuminate\Http\Request;

class ControllerPelicula extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.peliculas');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ModelPelicula  $modelPelicula
     * @return \Illuminate\Http\Response
     */
    public function show(ModelPelicula $modelPelicula)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModelPelicula  $modelPelicula
     * @return \Illuminate\Http\Response
     */
    public function edit(ModelPelicula $modelPelicula)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelPelicula  $modelPelicula
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModelPelicula $modelPelicula)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelPelicula  $modelPelicula
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModelPelicula $modelPelicula)
    {
        //
    }
}
