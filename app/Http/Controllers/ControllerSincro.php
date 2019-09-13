<?php

namespace App\Http\Controllers;

use App\ModelSincro;
use App\ModelPelicula;
use Illuminate\Http\Request;
use Log;
use Session;
use Illuminate\Support\Facades\DB;

class ControllerSincro extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $peliculas_eliminadas = ModelPelicula::where('isDeleted','=',1)->get();
        Log::info('Sincronizando Peliculas Eliminadas: '.$peliculas_eliminadas->count());
        
        $motorbd = session('motorbd');
        if($motorbd=='mysql') {
            $motorbd2 = 'pgsql';
        } elseif($motorbd=='pgsql') {
            $motorbd2 = 'mysql';
        }
        
        $peliculas_bd2 = DB::connection($motorbd2)->table('pelicula')->where('isDeleted','<>',1)->get();
        Log::info('Peliculas Activas en BD2 PGSQL: '.$peliculas_bd2->count());
       
        foreach($peliculas_eliminadas as $pdel) {
            
            if ($peliculas_bd2->contains('cod_pelicula', '=', $pdel->cod_pelicula)) {
                Log::info('Borrando Pelicula de ambas BD: '.$pdel->cod_pelicula);
                DB::connection($motorbd2)->table('pelicula')->where('cod_pelicula','=',$pdel->cod_pelicula)->delete(); 
                DB::connection($motorbd)->table('pelicula')->where('cod_pelicula','=',$pdel->cod_pelicula)->delete(); 
            }           
            
        }   
            
        Log::info('Sincronizacion de Peliculas Eliminadas Completa: '.$peliculas_eliminadas->count());

        return redirect()->route('admin.dashboard')->withFlashSuccess('Sincronizacion Completa');
        
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
     * @param  \App\ModelSincro  $modelSincro
     * @return \Illuminate\Http\Response
     */
    public function show(ModelSincro $modelSincro)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModelSincro  $modelSincro
     * @return \Illuminate\Http\Response
     */
    public function edit(ModelSincro $modelSincro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelSincro  $modelSincro
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModelSincro $modelSincro)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelSincro  $modelSincro
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModelSincro $modelSincro)
    {
        //
    }
}