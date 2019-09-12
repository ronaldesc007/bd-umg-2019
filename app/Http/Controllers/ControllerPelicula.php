<?php

namespace App\Http\Controllers;

use App\ModelPelicula;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Validator;

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
        // validate
        $rules = [
            'titulo' => 'required|max:45',
            'categoria' => 'required',
        ];

        $Input = $request->all();
        $validator = Validator::make($Input, $rules);

        // process the store
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)->withInput();
        }
        
         // Start transaction!
        DB::beginTransaction();

        // store
        $new_pelicula = new ModelPelicula;
        $new_pelicula->titulo = $request->titulo;
        $new_pelicula->categoria = $request->categoria;
        $new_pelicula->save();

        if (! $new_pelicula) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error

        return Redirect::route('admin.peliculas')
            ->withFlashInfo('Nueva Pelicula Agregada');
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
