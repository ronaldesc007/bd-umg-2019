<?php

namespace App\Http\Controllers;

use App\ModelSincro;
use App\ModelPelicula;
use App\ModelActor;
use App\ModelReparto;
use App\ModelCliente;
use App\ModelDisco;
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
        $this->syncRepartos($motorbd,$motorbd2); 
        $this->syncClientes($motorbd,$motorbd2); 
        $this->syncDiscos($motorbd,$motorbd2); 
        
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
                Log::alert('El actor no existe, se agrega : '.$anuevo->cod_actor);
                
                // buscando la el modelo existente
                $actorbd1 = ModelActor::find($anuevo->cod_actor);
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
            
            if ($actores_bd2->contains('cod_actor', '=', $aeditado->cod_actor)) {
                
                Log::alert('El siguiente codigo de actor ya existe en BD2 : '.$aeditado->cod_actor .'');
                
                // buscando modelo en bd2
                $actorbd2 = ModelActor::on($motorbd2)->find($aeditado->cod_actor);
                
                // buscando modelo en bd1
                $actorbd1 = ModelActor::find($aeditado->cod_actor);
                
                // comparando modelo bd1 vs modelo bd2
                if ($actorbd2->updated_at <= $actorbd1->updated_at ) {
                        
                    Log::alert('El registo en BD1 es mas reciente, actualizando BD2 : '.$aeditado->cod_actor .'');
                    
                    // el registro en bd1 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $actorbd2->nombre = $actorbd1->nombre;
                    $actorbd2->fecha_nacimiento = $actorbd1->fecha_nacimiento;
                    
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
            
            if ($actores_bd2->contains('cod_actor', '=', $adel->cod_actor)) {
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
     * Actualizar reparto.
     *
     * @return \Illuminate\Http\Response
     */
    private function syncRepartos($motorbd,$motorbd2)
    {
        
        Log::alert('INICIANDO PROCESO DE SINCRONIZACION DE '.$motorbd.' A '.$motorbd2);
        
        $repartos_bd1 = DB::connection($motorbd)->table('reparto')->where('isDeleted','<>',1)->get();
        Log::alert('Reparto Activo en BD1 '.$motorbd.': '.$repartos_bd1->count());
        
        $repartos_bd2 = DB::connection($motorbd2)->table('reparto')->where('isDeleted','<>',1)->get();
        Log::alert('Reparto Activo en BD2 '.$motorbd2.': '.$repartos_bd2->count());
        
        // Reparto Nuevo
        $reparto_nuevo = ModelReparto::where('isSynced','=',0)->where('isDeleted','=',0)->where('isUpdated','=',0)->get('cod_reparto');
        Log::alert('Sincronizando Reparto Nuevo: '.$reparto_nuevo->count());
        
        foreach($reparto_nuevo as $rnuevo) {
            
            if ($repartos_bd2->contains('cod_reparto', '=', $rnuevo->cod_reparto)) {
                
                Log::alert('El siguiente codigo de reparto ya existe en BD2 : '.$rnuevo->cod_reparto .'');
                
                // buscando modelo en bd2
                $repartobd2 = ModelReparto::on($motorbd2)->find($rnuevo->cod_reparto);
                
                // buscando modelo en bd1
                $repartobd1 = ModelReparto::find($rnuevo->cod_reparto);
                
                // comparando modelo bd1 vs modelo bd2
                if ($repartobd2->created_at >= $repartobd1->created_at ) {
                        
                    Log::alert('El registo en BD1 es mas antiguo, actualizando BD2 : '.$rnuevo->cod_reparto .'');
                    
                    // el registro en bd1 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $repartobd2->pelicula_cod_pelicula = $repartobd1->pelicula_cod_pelicula;
                    $repartobd2->actor_cod_actor = $repartobd1->actor_cod_actor;
                    
                    //marcando ambos modelos como sincronizados
                    $repartobd1->isSynced = 1;
                    $repartobd2->isSynced = 1;

                    // guardando los cambios
                    $repartobd1->save();
                    $repartobd2->save();
                    
                    if (!$repartobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$repartobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas antiguo, actualizando BD1 : '.$rnuevo->cod_reparto .'');
                    // el registro en bd2 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $repartobd1->pelicula_cod_pelicula = $repartobd2->pelicula_cod_pelicula;
                    $repartobd1->actor_cod_actor = $repartobd2->actor_cod_actor;
                    
                    //marcando ambos modelos como sincronizados
                    $repartobd1->isSynced = 1;
                    $repartobd2->isSynced = 1;

                    // guardando los cambios
                    $repartobd1->save();
                    $repartobd2->save();
                    
                    if (!$repartobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$repartobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            } else {
                Log::alert('El reparto no existe, se agrega : '.$rnuevo->cod_reparto);
                
                // buscando la el modelo existente
                $repartobd1 = ModelReparto::find($rnuevo->cod_reparto);
                // creando una copia del modelo existente
                $repartobd2 = $repartobd1->replicate();
                // asignando el ID del modelo en bd2
                $repartobd2->cod_reparto = $rnuevo->cod_reparto;
                // cambiando la conexion de bd
                $repartobd2->setConnection($motorbd2);
                
                //marcando ambos modelos como sincronizados
                $repartobd1->isSynced = 1;
                $repartobd2->isSynced = 1;
                
                $repartobd1->save();
                $repartobd2->save();
                
                if (!$repartobd2) {
                    DB::rollback(); //Rollback Transaction
                    return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                }
                
            }           
            
        } 
        
        // actualizando secuencia de Postgresql
        if($motorbd=='mysql') {
            $max = DB::table('reparto')->max('cod_reparto') + 1; 
            DB::connection($motorbd2)->statement("ALTER SEQUENCE reparto_cod_reparto_seq RESTART WITH $max");
        } 
                
        
        // Reparto Actualizado
        $reparto_editado = ModelReparto::where('isUpdated','=',1)->where('isSynced','=',0)->where('isDeleted','=',0)->get('cod_reparto');
        Log::alert('Sincronizando Reparto Editado: '.$reparto_editado->count());
        
        foreach($reparto_editado as $reditado) {
            
            if ($repartos_bd2->contains('cod_reparto', '=', $reditado->cod_reparto)) {
                
                Log::alert('El siguiente codigo de reparto ya existe en BD2 : '.$reditado->cod_reparto .'');
                
                // buscando modelo en bd2
                $repartobd2 = ModelReparto::on($motorbd2)->find($reditado->cod_reparto);
                
                // buscando modelo en bd1
                $repartobd1 = ModelReparto::find($reditado->cod_reparto);
                
                // comparando modelo bd1 vs modelo bd2
                if ($repartobd2->updated_at <= $repartobd1->updated_at ) {
                        
                    Log::alert('El registo en BD1 es mas reciente, actualizando BD2 : '.$reditado->cod_reparto .'');
                    
                    // el registro en bd1 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $repartobd2->titulo = $repartobd1->titulo;
                    $repartobd2->categoria = $repartobd1->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $repartobd1->isUpdated = 0;
                    $repartobd1->isSynced = 1;
                    $repartobd2->isUpdated = 0;
                    $repartobd2->isSynced = 1;
                    

                    // guardando los cambios
                    $repartobd1->save();
                    $repartobd2->save();
                    
                    if (!$repartobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$repartobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas reciente, actualizando BD1 : '.$rnuevo->cod_reparto .'');
                    // el registro en bd2 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $repartobd1->pelicula_cod_pelicula = $repartobd2->pelicula_cod_pelicula;
                    $repartobd1->actor_cod_actor = $repartobd2->actor_cod_actor;
                    
                    //marcando ambos modelos como sincronizados
                    $repartobd1->isUpdated = 0;
                    $repartobd1->isSynced = 1;
                    $repartobd2->isUpdated = 0;
                    $repartobd2->isSynced = 1;
                    
                    // guardando los cambios
                    $repartobd1->save();
                    $repartobd2->save();
                    
                    if (!$repartobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$repartobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            }
            
            
        } 
        
        // Reparto Eliminado
        $reparto_eliminado = ModelReparto::where('isDeleted','=',1)->get();
        Log::alert('Sincronizando Reparto Eliminado: '.$reparto_eliminado->count());
        
        foreach($reparto_eliminado as $pdel) {
            
            if ($repartos_bd2->contains('cod_reparto', '=', $pdel->cod_reparto)) {
                Log::alert('Borrando Reparto de ambas BD: '.$pdel->cod_reparto);
                DB::connection($motorbd2)->table('reparto')->where('cod_reparto','=',$pdel->cod_reparto)->delete(); 
                DB::connection($motorbd)->table('reparto')->where('cod_reparto','=',$pdel->cod_reparto)->delete(); 
            } else {
                Log::alert('Borrando solo de BD1: '.$pdel->cod_reparto);
                DB::connection($motorbd)->table('reparto')->where('cod_reparto','=',$pdel->cod_reparto)->delete(); 
            }        
            
        }  
        
        Log::alert('Sincronizacion de Reparto Completa: '.$reparto_eliminado->count());
        
        return true;
        
    }
    
    
    /**
     * Actualizar los clientes.
     *
     * @return \Illuminate\Http\Response
     */
    private function syncClientes($motorbd,$motorbd2)
    {
        
        Log::alert('INICIANDO PROCESO DE SINCRONIZACION DE '.$motorbd.' A '.$motorbd2);
        
        $clientes_bd1 = DB::connection($motorbd)->table('cliente')->where('isDeleted','<>',1)->get();
        Log::alert('Clientes Activos en BD1 '.$motorbd.': '.$clientes_bd1->count());
        
        $clientes_bd2 = DB::connection($motorbd2)->table('cliente')->where('isDeleted','<>',1)->get();
        Log::alert('Clientes Activos en BD2 '.$motorbd2.': '.$clientes_bd2->count());
        
        // Clientes Nuevos
        $clientes_nuevos = ModelCliente::where('isSynced','=',0)->where('isDeleted','=',0)->where('isUpdated','=',0)->get('no_membresia');
        Log::alert('Sincronizando Clientes Nuevos: '.$clientes_nuevos->count());
        
        foreach($clientes_nuevos as $cnuevo) {
            
            if ($clientes_bd2->contains('no_membresia', '=', $cnuevo->no_membresia)) {
                
                Log::alert('La siguiente mebresia de cliente ya existe en BD2 : '.$cnuevo->no_membresia .'');
                
                // buscando modelo en bd2
                $clientebd2 = ModelCliente::on($motorbd2)->find($cnuevo->no_membresia);
                
                // buscando modelo en bd1
                $clientebd1 = ModelCliente::find($cnuevo->no_membresia);
                
                // comparando modelo bd1 vs modelo bd2
                if ($clientebd2->created_at >= $clientebd1->created_at ) {
                        
                    Log::alert('El registo en BD1 es mas antiguo, actualizando BD2 : '.$cnuevo->no_membresia .'');
                    
                    // el registro en bd1 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $clientebd2->nombre = $clientebd1->nombre;
                    $clientebd2->apellido = $clientebd1->apellido;
                    $clientebd2->direccion = $clientebd1->direccion;
                    $clientebd2->telefono = $clientebd1->telefono;
                    
                    //marcando ambos modelos como sincronizados
                    $clientebd1->isSynced = 1;
                    $clientebd2->isSynced = 1;

                    // guardando los cambios
                    $clientebd1->save();
                    $clientebd2->save();
                    
                    if (!$clientebd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$clientebd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas antiguo, actualizando BD1 : '.$cnuevo->no_membresia .'');
                    // el registro en bd2 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $clientebd1->nombre = $clientebd2->nombre;
                    $clientebd1->apellido = $clientebd2->apellido;
                    $clientebd1->direccion = $clientebd2->direccion;
                    $clientebd1->telefono = $clientebd2->telefono;
                    
                    //marcando ambos modelos como sincronizados
                    $clientebd1->isSynced = 1;
                    $clientebd2->isSynced = 1;

                    // guardando los cambios
                    $clientebd1->save();
                    $clientebd2->save();
                    
                    if (!$clientebd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$clientebd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            } else {
                Log::alert('El cliente no existe, se agrega : '.$cnuevo->no_membresia);
                
                // buscando la el modelo existente
                $clientebd1 = ModelCliente::find($cnuevo->no_membresia);
                // creando una copia del modelo existente
                $clientebd2 = $clientebd1->replicate();
                // asignando el ID del modelo en bd2
                $clientebd2->no_membresia = $cnuevo->no_membresia;
                // cambiando la conexion de bd
                $clientebd2->setConnection($motorbd2);
                
                //marcando ambos modelos como sincronizados
                $clientebd1->isSynced = 1;
                $clientebd2->isSynced = 1;
                
                $clientebd1->save();
                $clientebd2->save();
                
                if (!$clientebd2) {
                    DB::rollback(); //Rollback Transaction
                    return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                }
                
            }           
            
        } 
        
        // actualizando secuencia de Postgresql
        if($motorbd=='mysql') {
            $max = DB::table('cliente')->max('no_membresia') + 1; 
            DB::connection($motorbd2)->statement("ALTER SEQUENCE cliente_no_membresia_seq RESTART WITH $max");
        } 
                
        
        // Clientes Actualizados
        $clientes_editados = ModelCliente::where('isUpdated','=',1)->where('isSynced','=',0)->where('isDeleted','=',0)->get('no_membresia');
        Log::alert('Sincronizando Clientes Actualizados: '.$clientes_editados->count());
        
        foreach($clientes_editados as $ceditado) {
            
            if ($clientes_bd2->contains('no_membresia', '=', $ceditado->no_membresia)) {
                
                Log::alert('La siguiente mebresia de cliente ya existe en BD2 : '.$ceditado->no_membresia .'');
                
                // buscando modelo en bd2
                $clientebd2 = ModelCliente::on($motorbd2)->find($ceditado->no_membresia);
                
                // buscando modelo en bd1
                $clientebd1 = ModelCliente::find($ceditado->no_membresia);
                
                // comparando modelo bd1 vs modelo bd2
                if ($clientebd2->updated_at <= $clientebd1->updated_at ) {
                        
                    Log::alert('El registo en BD1 es mas reciente, actualizando BD2 : '.$ceditado->no_membresia .'');
                    
                    // el registro en bd1 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $clientebd2->titulo = $clientebd1->titulo;
                    $clientebd2->categoria = $clientebd1->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $clientebd1->isUpdated = 0;
                    $clientebd1->isSynced = 1;
                    $clientebd2->isUpdated = 0;
                    $clientebd2->isSynced = 1;
                    

                    // guardando los cambios
                    $clientebd1->save();
                    $clientebd2->save();
                    
                    if (!$clientebd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$clientebd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas reciente, actualizando BD1 : '.$cnuevo->no_membresia .'');
                    // el registro en bd2 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $clientebd1->titulo = $clientebd2->titulo;
                    $clientebd1->categoria = $clientebd2->categoria;
                    
                    //marcando ambos modelos como sincronizados
                    $clientebd1->isUpdated = 0;
                    $clientebd1->isSynced = 1;
                    $clientebd2->isUpdated = 0;
                    $clientebd2->isSynced = 1;
                    
                    // guardando los cambios
                    $clientebd1->save();
                    $clientebd2->save();
                    
                    if (!$clientebd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$clientebd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            }
            
            
        } 
        
        // Clientes Eliminados
        $clientes_eliminados = ModelCliente::where('isDeleted','=',1)->get();
        Log::alert('Sincronizando Clientes Eliminados: '.$clientes_eliminados->count());
        
        foreach($clientes_eliminados as $cdel) {
            
            if ($clientes_bd2->contains('no_membresia', '=', $cdel->no_membresia)) {
                Log::alert('Borrando Cliente de ambas BD: '.$cdel->no_membresia);
                DB::connection($motorbd2)->table('cliente')->where('no_membresia','=',$cdel->no_membresia)->delete(); 
                DB::connection($motorbd)->table('cliente')->where('no_membresia','=',$cdel->no_membresia)->delete(); 
            } else {
                Log::alert('Borrando solo de BD1: '.$cdel->no_membresia);
                DB::connection($motorbd)->table('cliente')->where('no_membresia','=',$cdel->no_membresia)->delete(); 
            }        
            
        }  
        
        Log::alert('Sincronizacion de Clientes Completa: '.$clientes_eliminados->count());
        
        return true;
        
    }
   
     /**
     * Actualizar los discos.
     *
     * @return \Illuminate\Http\Response
     */
    private function syncDiscos($motorbd,$motorbd2)
    {
        
        Log::alert('INICIANDO PROCESO DE SINCRONIZACION DE '.$motorbd.' A '.$motorbd2);
        
        $discos_bd1 = DB::connection($motorbd)->table('disco')->where('isDeleted','<>',1)->get();
        Log::alert('Discos Activos en BD1 '.$motorbd.': '.$discos_bd1->count());
        
        $discos_bd2 = DB::connection($motorbd2)->table('disco')->where('isDeleted','<>',1)->get();
        Log::alert('Discos Activos en BD2 '.$motorbd2.': '.$discos_bd2->count());
        
        // Discos Nuevos
        $discos_nuevos = ModelDisco::where('isSynced','=',0)->where('isDeleted','=',0)->where('isUpdated','=',0)->get('cod_disco');
        Log::alert('Sincronizando Discos Nuevos: '.$discos_nuevos->count());
        
        foreach($discos_nuevos as $dnuevo) {
            
            if ($discos_bd2->contains('cod_disco', '=', $dnuevo->cod_disco)) {
                
                Log::alert('El siguiente codigo de disco ya existe en BD2 : '.$dnuevo->cod_disco .'');
                
                // buscando modelo en bd2
                $discobd2 = ModelDisco::on($motorbd2)->find($dnuevo->cod_disco);
                
                // buscando modelo en bd1
                $discobd1 = ModelDisco::find($dnuevo->cod_disco);
                
                // comparando modelo bd1 vs modelo bd2
                if ($discobd2->created_at >= $discobd1->created_at ) {
                        
                    Log::alert('El registo en BD1 es mas antiguo, actualizando BD2 : '.$dnuevo->cod_disco .'');
                    
                    // el registro en bd1 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $discobd2->no_copias = $discobd1->titulo;
                    $discobd2->formato = $discobd1->formato;
                    $discobd2->pelicula_cod_pelicula = $discobd1->pelicula_cod_pelicula;
                    
                    //marcando ambos modelos como sincronizados
                    $discobd1->isSynced = 1;
                    $discobd2->isSynced = 1;

                    // guardando los cambios
                    $discobd1->save();
                    $discobd2->save();
                    
                    if (!$discobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$discobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas antiguo, actualizando BD1 : '.$dnuevo->cod_disco .'');
                    // el registro en bd2 es mas antiguo, actualizando el registro en bd2 con los datos de bd1
                    $discobd1->no_copias = $discobd2->no_copias;
                    $discobd1->formato = $discobd2->formato;
                    $discobd1->pelicula_cod_pelicula = $discobd2->pelicula_cod_pelicula;
                    
                    //marcando ambos modelos como sincronizados
                    $discobd1->isSynced = 1;
                    $discobd2->isSynced = 1;

                    // guardando los cambios
                    $discobd1->save();
                    $discobd2->save();
                    
                    if (!$discobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$discobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            } else {
                Log::alert('El disco no existe, se agrega : '.$dnuevo->cod_disco);
                
                // buscando la el modelo existente
                $discobd1 = ModelDisco::find($dnuevo->cod_disco);
                // creando una copia del modelo existente
                $discobd2 = $discobd1->replicate();
                // asignando el ID del modelo en bd2
                $discobd2->cod_disco = $dnuevo->cod_disco;
                // cambiando la conexion de bd
                $discobd2->setConnection($motorbd2);
                
                //marcando ambos modelos como sincronizados
                $discobd1->isSynced = 1;
                $discobd2->isSynced = 1;
                
                $discobd1->save();
                $discobd2->save();
                
                if (!$discobd2) {
                    DB::rollback(); //Rollback Transaction
                    return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                }
                
            }           
            
        } 
        
        // actualizando secuencia de Postgresql
        if($motorbd=='mysql') {
            $max = DB::table('disco')->max('cod_disco') + 1; 
            DB::connection($motorbd2)->statement("ALTER SEQUENCE disco_cod_disco_seq RESTART WITH $max");
        } 
                
        
        // Discos Actualizadas
        $discos_editados = ModelDisco::where('isUpdated','=',1)->where('isSynced','=',0)->where('isDeleted','=',0)->get('cod_disco');
        Log::alert('Sincronizando Discos Editados: '.$discos_editados->count());
        
        foreach($discos_editados as $deditado) {
            
            if ($discos_bd2->contains('cod_disco', '=', $deditado->cod_disco)) {
                
                Log::alert('El siguiente codigo de disco ya existe en BD2 : '.$deditado->cod_disco .'');
                
                // buscando modelo en bd2
                $discobd2 = ModelDisco::on($motorbd2)->find($deditado->cod_disco);
                
                // buscando modelo en bd1
                $discobd1 = ModelDisco::find($deditado->cod_disco);
                
                // comparando modelo bd1 vs modelo bd2
                if ($discobd2->updated_at <= $discobd1->updated_at ) {
                        
                    Log::alert('El registo en BD1 es mas reciente, actualizando BD2 : '.$deditado->cod_disco .'');
                    
                    // el registro en bd1 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $discobd2->no_copias = $discobd1->no_copias;
                    $discobd2->formato = $discobd1->formato;
                    $discobd2->pelicula_cod_pelicula = $discobd1->pelicula_cod_pelicula;
                    
                    //marcando ambos modelos como sincronizados
                    $discobd1->isUpdated = 0;
                    $discobd1->isSynced = 1;
                    $discobd2->isUpdated = 0;
                    $discobd2->isSynced = 1;
                    

                    // guardando los cambios
                    $discobd1->save();
                    $discobd2->save();
                    
                    if (!$discobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$discobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                else {
                    
                    Log::alert('El registo en BD2 es mas reciente, actualizando BD1 : '.$dnuevo->cod_disco .'');
                    // el registro en bd2 es mas reciente, actualizando el registro en bd2 con los datos de bd1
                    $discobd1->no_copias = $discobd2->no_copias;
                    $discobd1->formato = $discobd2->formato;
                    $discobd1->pelicula_cod_pelicula = $discobd2->pelicula_cod_pelicula;
                    
                    //marcando ambos modelos como sincronizados
                    $discobd1->isUpdated = 0;
                    $discobd1->isSynced = 1;
                    $discobd2->isUpdated = 0;
                    $discobd2->isSynced = 1;
                    
                    // guardando los cambios
                    $discobd1->save();
                    $discobd2->save();
                    
                    if (!$discobd1) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                    if (!$discobd2) {
                        DB::rollback(); //Rollback Transaction
                        return Redirect::back()->withInput()->withFlashDanger('DB::Error');
                    }
                    
                }
                    
            }
            
            
        } 
        
        // Discos Eliminados
        $discos_eliminados = ModelDisco::where('isDeleted','=',1)->get();
        Log::alert('Sincronizando Discos Eliminados: '.$discos_eliminados->count());
        
        foreach($discos_eliminados as $ddel) {
            
            if ($discos_bd2->contains('cod_disco', '=', $ddel->cod_disco)) {
                Log::alert('Borrando Disco de ambas BD: '.$ddel->cod_disco);
                DB::connection($motorbd2)->table('disco')->where('cod_disco','=',$ddel->cod_disco)->delete(); 
                DB::connection($motorbd)->table('disco')->where('cod_disco','=',$ddel->cod_disco)->delete(); 
            } else {
                Log::alert('Borrando solo de BD1: '.$ddel->cod_disco);
                DB::connection($motorbd)->table('disco')->where('cod_disco','=',$ddel->cod_disco)->delete(); 
            }        
            
        }  
        
        Log::alert('Sincronizacion de Discos Completa: '.$discos_eliminados->count());
        
        return true;
        
    }
    
    
    
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
