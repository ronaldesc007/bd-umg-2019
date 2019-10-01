<?php

namespace App\Http\Controllers;

use App\ModelPelicula;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Validator;
use Illuminate\Support\Facades\Log;

class ControllerPelicula extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $peliculas = ModelPelicula::where('isDeleted','<>',1)->orderBy('cod_pelicula','asc')->paginate();;
        return view('backend.peliculas.index')->withPeliculas($peliculas);
            
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.peliculas.create');
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
        
        Log::info('Una pelicula ha sido agregada: '.$new_pelicula->cod_pelicula);
        
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
    public function edit($codPelicula)
    {
        $pelicula = ModelPelicula::findOrFail($codPelicula);
   
        return view('backend.peliculas.edit')
            ->withPelicula($pelicula);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelPelicula  $modelPelicula
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $codPelicula)
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
        $pelicula = ModelPelicula::findOrFail($codPelicula);
        $pelicula->titulo = $request->titulo;
        $pelicula->categoria = $request->categoria;
        $pelicula->isUpdated = 1;
        $pelicula->isSynced = 0;
        $pelicula->save();

        if (! $pelicula) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('La pelicula Cod#'.$pelicula->cod_pelicula.' ha sido actualizada.');
        
        return Redirect::route('admin.peliculas')
            ->withFlashInfo('La pelicula ha sido actualizada.');
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelPelicula  $modelPelicula
     * @return \Illuminate\Http\Response
     */
    public function destroy($cod_pelicula)
    {
        //$this->roleRepository->deleteById($role->id);

        
        $pelicula = ModelPelicula::find($cod_pelicula);
        $pelicula->isDeleted = 1;
        $pelicula->save();
        
        
        Log::info('La siguiente pelicula has sido eliminada: '.$pelicula->titulo);

        return redirect()->route('admin.peliculas')->withFlashSuccess('La pelicula ha sido eliminada.');
    }
}
