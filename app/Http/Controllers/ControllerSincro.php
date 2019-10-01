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
        // definiendo el motor de la base de datos 1 y 2
        $motorbd = session('motorbd');
        if($motorbd=='mysql') {
            $motorbd2 = 'pgsql';
        } elseif($motorbd=='pgsql') {
            $motorbd2 = 'mysql';
        }
        
        // Start transaction!
        DB::beginTransaction();
        
        $this->syncPeliculas($motorbd,$motorbd2); 
        
        DB::commit(); // Commit if no error
        
        
        
        return redirect()->route('admin.dashboard')->withFlashSuccess('Sincronizacion Completa');
        
    }

    
    /**
     * Actualizar las peliculas.
     *
     * @return \Illuminate\Http\Response
     */
    private function syncPeliculas($motorbd,$motorbd2)
    {
        
        Log::alert('INICIANDO PROCESO DE SINCRONIZACION DE '.$motorbd.' A '.$motorbd2);
        
        $peliculas_bd1 = DB::connection($motorbd)->table('pelicula')->where('isDeleted','<>',1)->get();
        Log::alert('Peliculas Activas en BD1 '.$motorbd.': '.$peliculas_bd1->count());
        
        $peliculas_bd2 = DB::connection($motorbd2)->table('pelicula')->where('isDeleted','<>',1)->get();
        Log::alert('Peliculas Activas en BD2 '.$motorbd2.': '.$peliculas_bd2->count());
        
        // Peliculas Nuevas
        $peliculas_nuevas = ModelPelicula::where('isSynced','=',0)->where('isDeleted','=',0)->where('isUpdated','=',0)->get('cod_pelicula');
        Log::alert('Sincronizando Peliculas Nuevas: '.$peliculas_nuevas->count());
        
        foreach($peliculas_nuevas as $pnueva) {
            
            if ($peliculas_bd2->contains('cod_pelicula', '=', $pnueva->cod_pelicula)) {
                
                Log::alert('El siguiente codigo de pelicula ya existe en BD2 : '.$pnueva->cod_pelicula .'');
                
                // buscando modelo en bd2
                $peliculabd2 = ModelPelicula::on($motorbd2)->find($pnueva->cod_pelicula);
                
                // buscando modelo en bd1
                $peliculabd1 = ModelPelicula::find($pnueva->cod_pelicula);
                
                // comparando modelo bd1 vs modelo bd2
                if ($peliculabd2->created_at >= $peliculabd1->created_at ) {
                        
                    Log::alert('El registo en BD1 es mas antiguo, actualizando BD2 : '.$pnueva->cod_pelicula .'');
                    
                    // el registro en bd1 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $peliculabd2->titulo = $peliculabd1->titulo;
                    $peliculabd2->categoria = $peliculabd1->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $peliculabd1->isSynced = 1;
                    $peliculabd2->isSynced = 1;

                    // guardando los cambios
                    $peliculabd1->save();
                    $peliculabd2->save();
                    
                    if (!$peliculabd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$peliculabd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas antiguo, actualizando BD1 : '.$pnueva->cod_pelicula .'');
                    // el registro en bd2 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $peliculabd1->titulo = $peliculabd2->titulo;
                    $peliculabd1->categoria = $peliculabd2->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $peliculabd1->isSynced = 1;
                    $peliculabd2->isSynced = 1;

                    // guardando los cambios
                    $peliculabd1->save();
                    $peliculabd2->save();
                    
                    if (!$peliculabd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$peliculabd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            } else {
                Log::alert('La pelicula no existe, se agrega : '.$pnueva->cod_pelicula);
                
                // buscando la el modelo existente
                $peliculabd1 = ModelPelicula::find($pnueva->cod_pelicula);
                // creando una copia del modelo existente
                $peliculabd2 = $peliculabd1->replicate();
                // asignando el ID del modelo en bd2
                $peliculabd2->cod_pelicula = $pnueva->cod_pelicula;
                // cambiando la conexion de bd
                $peliculabd2->setConnection($motorbd2);
                
                //marcando ambos modelos como sincronizados
                $peliculabd1->isSynced = 1;
                $peliculabd2->isSynced = 1;
                
                $peliculabd1->save();
                $peliculabd2->save();
                
                if (!$peliculabd2) {
                    DB::rollback(); //Rollback Transaction
                    return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                }
                
            }           
            
        } 
        
        // actualizando secuencia de Postgresql
        if($motorbd=='mysql') {
            $max = DB::table('pelicula')->max('cod_pelicula') + 1; 
            DB::connection($motorbd2)->statement("ALTER SEQUENCE pelicula_cod_pelicula_seq RESTART WITH $max");
        } 
                
        
        // Peliculas Actualizadas
        $peliculas_editadas = ModelPelicula::where('isUpdated','=',1)->where('isSynced','=',0)->where('isDeleted','=',0)->get('cod_pelicula');
        Log::alert('Sincronizando Peliculas Editadas: '.$peliculas_editadas->count());
        
        foreach($peliculas_editadas as $peditada) {
            
            if ($peliculas_bd2->contains('cod_pelicula', '=', $peditada->cod_pelicula)) {
                
                Log::alert('El siguiente codigo de pelicula ya existe en BD2 : '.$peditada->cod_pelicula .'');
                
                // buscando modelo en bd2
                $peliculabd2 = ModelPelicula::on($motorbd2)->find($peditada->cod_pelicula);
                
                // buscando modelo en bd1
                $peliculabd1 = ModelPelicula::find($peditada->cod_pelicula);
                
                // comparando modelo bd1 vs modelo bd2
                if ($peliculabd2->updated_at <= $peliculabd1->updated_at ) {
                        
                    Log::alert('El registo en BD1 es mas reciente, actualizando BD2 : '.$peditada->cod_pelicula .'');
                    
                    // el registro en bd1 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $peliculabd2->titulo = $peliculabd1->titulo;
                    $peliculabd2->categoria = $peliculabd1->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $peliculabd1->isUpdated = 0;
                    $peliculabd1->isSynced = 1;
                    $peliculabd2->isUpdated = 0;
                    $peliculabd2->isSynced = 1;
                    

                    // guardando los cambios
                    $peliculabd1->save();
                    $peliculabd2->save();
                    
                    if (!$peliculabd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$peliculabd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas reciente, actualizando BD1 : '.$pnueva->cod_pelicula .'');
                    // el registro en bd2 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $peliculabd1->titulo = $peliculabd2->titulo;
                    $peliculabd1->categoria = $peliculabd2->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $peliculabd1->isUpdated = 0;
                    $peliculabd1->isSynced = 1;
                    $peliculabd2->isUpdated = 0;
                    $peliculabd2->isSynced = 1;
                    
                    // guardando los cambios
                    $peliculabd1->save();
                    $peliculabd2->save();
                    
                    if (!$peliculabd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$peliculabd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            }
            
            
        } 
        
        // Peliculas Eliminadas
        $peliculas_eliminadas = ModelPelicula::where('isDeleted','=',1)->get();
        Log::alert('Sincronizando Peliculas Eliminadas: '.$peliculas_eliminadas->count());
        
        foreach($peliculas_eliminadas as $pdel) {
            
            if ($peliculas_bd2->contains('cod_pelicula', '=', $pdel->cod_pelicula)) {
                Log::alert('Borrando Pelicula de ambas BD: '.$pdel->cod_pelicula);
                DB::connection($motorbd2)->table('pelicula')->where('cod_pelicula','=',$pdel->cod_pelicula)->delete(); 
                DB::connection($motorbd)->table('pelicula')->where('cod_pelicula','=',$pdel->cod_pelicula)->delete(); 
            } else {
                Log::alert('Borrando solo de BD1: '.$pdel->cod_pelicula);
                DB::connection($motorbd)->table('pelicula')->where('cod_pelicula','=',$pdel->cod_pelicula)->delete(); 
            }        
            
        }  
        
        Log::alert('Sincronizacion de Peliculas Completa: '.$peliculas_eliminadas->count());
        
        return true;
        
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
