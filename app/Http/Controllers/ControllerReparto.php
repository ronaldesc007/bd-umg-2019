<?php

namespace App\Http\Controllers;

use App\ModelReparto;
use App\ModelPelicula;
use App\ModelActor;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Validator;
use Illuminate\Support\Facades\Log;

class ControllerReparto extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //$repartos = ModelReparto::where('isDeleted','<>',1)->orderBy('cod_reparto','asc')->paginate();;
        //return view('backend.repartos.index')->withRepartos($repartos);
        
        $repartos = ModelReparto::select('reparto.*', 'pelicula.titulo' , 'actor.nombre')
            ->join('pelicula', 'pelicula_cod_pelicula', '=', 'pelicula.cod_pelicula')
            ->join('actor', 'actor_cod_actor', '=', 'actor.cod_actor')
            ->where('reparto.isDeleted','<>',1)
            ->orderBy('cod_reparto','asc')
            ->paginate();;
        return view('backend.repartos.index')->withRepartos($repartos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //se obtienen las peliculas validas y se envian al form en el return
        $peliculas = ModelPelicula::where('isDeleted','<>',1)->get();        
        $actores = ModelActor::where('isDeleted','<>',1)->get();        
        return view('backend.repartos.create')
            ->withPeliculas($peliculas)
            ->withActores($actores);
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
            'cod_pelicula' => 'required|max:45',
            'cod_actor' => 'required',
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
        $new_reparto = new ModelReparto;
        $new_reparto->pelicula_cod_pelicula = $request->cod_pelicula;
        $new_reparto->actor_cod_actor = $request->cod_actor;
        $new_reparto->save();

        if (! $new_reparto) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('Un reparto ha sido agregado: '.$new_reparto->cod_reparto);
        
        return Redirect::route('admin.repartos')
            ->withFlashInfo('Nueva reparto Agregada');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ModelReparto  $modelReparto
     * @return \Illuminate\Http\Response
     */
    public function show(ModelReparto $modelReparto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModelReparto  $modelReparto
     * @return \Illuminate\Http\Response
     */
    public function edit($codReparto)
    {
        //
        $reparto = ModelReparto::findOrFail($codReparto);
        
        //se obtienen las peliculas validas y se envian al form en el return
        $peliculas = ModelPelicula::where('isDeleted','<>',1)->get();
        $actores = ModelActor::where('isDeleted','<>',1)->get();   
        return view('backend.repartos.edit')
            ->withReparto($reparto)
            ->withPeliculas($peliculas)
            ->withActores($actores);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelReparto  $modelReparto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $codReparto)
    {
        // validate
        $rules = [
            'cod_pelicula' => 'required',
            'cod_actor' => 'required',
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
        $reparto = ModelReparto::findOrFail($codReparto);
        $reparto->pelicula_cod_pelicula = $request->cod_pelicula;
        $reparto->actor_cod_actor = $request->cod_actor;
        
        // si la pelicula es nueva y ya se sincronizo se marca como update
        if($reparto->isSynced == 1){
            $reparto->isUpdated = 1;
            $reparto->isSynced = 0;
        } // sino se deja como nuevo registro sin considerar los cambios intermedios.
        else {
            $reparto->isUpdated = 0;
            $reparto->isSynced = 0;    
        }
        
        $reparto->save();

        if (! $reparto) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('el reparto Cod#'.$reparto->cod_reparto.' ha sido actualizado.');
        
        return Redirect::route('admin.repartos')
            ->withFlashInfo('El reparto ha sido actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelReparto  $modelReparto
     * @return \Illuminate\Http\Response
     */
    public function destroy( $cod_reparto)
    {
        //$this->roleRepository->deleteById($role->id);

        
        $reparto = ModelReparto::find($cod_reparto);
        $reparto->isDeleted = 1;
        $reparto->save();
        
        
        Log::info('El siguiente reparto has sido eliminado: '.$reparto->titulo);

        return redirect()->route('admin.repartos')->withFlashSuccess('El reparto ha sido eliminada.');
    }
}
