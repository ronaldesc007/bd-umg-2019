<?php

namespace App\Http\Controllers;

use App\ModelSincro;
use App\ModelPelicula;
use App\ModelActor;
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
        $this->syncActores($motorbd,$motorbd2); 
        
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
     * Actualizar los actores.
     *
     * @return \Illuminate\Http\Response
     */
    private function syncActores($motorbd,$motorbd2)
    {
        
        Log::alert('INICIANDO PROCESO DE SINCRONIZACION DE ACTORES '.$motorbd.' A '.$motorbd2);
        
        $actores_bd1 = DB::connection($motorbd)->table('actor')->where('isDeleted','<>',1)->get();
        Log::alert('Actores Activos en BD1 '.$motorbd.': '.$actores_bd1->count());
        
        $actores_bd2 = DB::connection($motorbd2)->table('actor')->where('isDeleted','<>',1)->get();
        Log::alert('Actores Activos en BD2 '.$motorbd2.': '.$actores_bd2->count());
        
        // Actores Nuevos
        $actores_nuevos = ModelActor::where('isSynced','=',0)->where('isDeleted','=',0)->where('isUpdated','=',0)->get('cod_actor');
        Log::alert('Sincronizando Actores Nuevos: '.$actores_nuevos->count());
        
        foreach($actores_nuevos as $anuevo) {
            
            if ($actores_bd2->contains('cod_actor', '=', $anuevo->cod_actor)) {
                
                Log::alert('El siguiente codigo de actor ya existe en BD2 : '.$anuevo->cod_actor .'');
                
                // buscando modelo en bd2
                $actorbd2 = ModelActor::on($motorbd2)->find($anuevo->cod_actor);
                
                // buscando modelo en bd1
                $actorbd1 = ModelActor::find($anuevo->cod_actor);
                
                // comparando modelo bd1 vs modelo bd2
                if ($actorbd2->created_at >= $actorbd1->created_at ) {
                        
                    Log::alert('El registo en BD1 es mas antiguo, actualizando BD2 : '.$anuevo->cod_actor .'');
                    
                    // el registro en bd1 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $actorbd2->nombre = $actorbd1->nombre;
                    $actorbd2->fecha_nacimiento = $actorbd1->fecha_nacimiento;
                    
                    //marcando ambos modelos como sincronizados
                    $actorbd1->isSynced = 1;
                    $actorbd2->isSynced = 1;

                    // guardando los cambios
                    $actorbd1->save();
                    $actorbd2->save();
                    
                    if (!$actorbd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$actorbd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas antiguo, actualizando BD1 : '.$anuevo->cod_actor .'');
                    // el registro en bd2 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $actorbd1->nombre = $actorbd2->nombre;
                    $actorbd1->fecha_nacimiento = $actorbd2->fecha_nacimiento;
                    
                    //marcando ambos modelos como sincronizados
                    $actorbd1->isSynced = 1;
                    $actorbd2->isSynced = 1;

                    // guardando los cambios
                    $actorbd1->save();
                    $actorbd2->save();
                    
                    if (!$actorbd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$actorbd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            } else {
                Log::alert('La pelicula no existe, se agrega : '.$anuevo->cod_actor);
                
                // buscando la el modelo existente
                $actorbd1 = ModelPelicula::find($anuevo->cod_actor);
                // creando una copia del modelo existente
                $actorbd2 = $actorbd1->replicate();
                // asignando el ID del modelo en bd2
                $actorbd2->cod_actor = $anuevo->cod_actor;
                // cambiando la conexion de bd
                $actorbd2->setConnection($motorbd2);
                
                //marcando ambos modelos como sincronizados
                $actorbd1->isSynced = 1;
                $actorbd2->isSynced = 1;
                
                $actorbd1->save();
                $actorbd2->save();
                
                if (!$actorbd2) {
                    DB::rollback(); //Rollback Transaction
                    return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                }
                
            }           
            
        } 
        
        // actualizando secuencia de Postgresql
        if($motorbd=='mysql') {
            $max = DB::table('actor')->max('cod_actor') + 1; 
            DB::connection($motorbd2)->statement("ALTER SEQUENCE actor_cod_actor_seq RESTART WITH $max");
        } 
                
        
        // Actores Actualizados
        $actores_editados = ModelActor::where('isUpdated','=',1)->where('isSynced','=',0)->where('isDeleted','=',0)->get('cod_actor');
        Log::alert('Sincronizando Actores Editados: '.$actores_editados->count());
        
        foreach($actores_editados as $aeditado) {
            
            if ($actorbd2->contains('cod_actor', '=', $aeditado->cod_actor)) {
                
                Log::alert('El siguiente codigo de actor ya existe en BD2 : '.$aeditado->cod_actor .'');
                
                // buscando modelo en bd2
                $actorbd2 = ModelActor::on($motorbd2)->find($aeditado->cod_actor);
                
                // buscando modelo en bd1
                $actorbd1 = ModelActor::find($aeditado->cod_actor);
                
                // comparando modelo bd1 vs modelo bd2
                if ($actorbd2->updated_at <= $actorbd1->updated_at ) {
                        
                    Log::alert('El registo en BD1 es mas reciente, actualizando BD2 : '.$aeditado->cod_actor .'');
                    
                    // el registro en bd1 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $actorbd2->titulo = $actorbd1->titulo;
                    $actorbd2->categoria = $actorbd1->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $actorbd1->isUpdated = 0;
                    $actorbd1->isSynced = 1;
                    $actorbd2->isUpdated = 0;
                    $actorbd2->isSynced = 1;
                    

                    // guardando los cambios
                    $actorbd1->save();
                    $actorbd2->save();
                    
                    if (!$actorbd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$actorbd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas reciente, actualizando BD1 : '.$anueva->cod_actor .'');
                    // el registro en bd2 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $actorbd1->nombre = $actorbd2->titulo;
                    $actorbd1->fecha_nacimiento = $actorbd2->fecha_nacimiento;
                    
                    //marcando ambos modelos como sincronizados
                    $actorbd1->isUpdated = 0;
                    $actorbd1->isSynced = 1;
                    $actorbd2->isUpdated = 0;
                    $actorbd2->isSynced = 1;
                    
                    // guardando los cambios
                    $actorbd1->save();
                    $actorbd2->save();
                    
                    if (!$actorbd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$actorbd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            }
            
            
        } 
        
        // Peliculas Eliminadas
        $actores_eliminados = ModelActor::where('isDeleted','=',1)->get();
        Log::alert('Sincronizando Actores Eliminados: '.$actores_eliminados->count());
        
        foreach($actores_eliminados as $adel) {
            
            if ($actorbd2->contains('cod_actor', '=', $adel->cod_actor)) {
                Log::alert('Borrando Actor de ambas BD: '.$adel->cod_actor);
                DB::connection($motorbd2)->table('actor')->where('cod_actor','=',$adel->cod_actor)->delete(); 
                DB::connection($motorbd)->table('actor')->where('cod_actor','=',$adel->cod_actor)->delete(); 
            } else {
                Log::alert('Borrando solo de BD1: '.$adel->cod_actor);
                DB::connection($motorbd)->table('actor')->where('cod_actor','=',$adel->cod_actor)->delete(); 
            }        
            
        }  
        
        Log::alert('Sincronizacion de Actores Completa: '.$actores_eliminados->count());
        
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
