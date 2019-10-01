<?php

namespace App\Http\Controllers;

use App\ModelDisco;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Validator;
use Illuminate\Support\Facades\Log;

class ControllerDisco extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //$discos = ModelDisco::where('isDeleted','<>',1)->orderBy('cod_disco','asc')->paginate();;
        //return view('backend.discos.index')->withDiscos($discos);
        
        $discos = ModelDisco::select('disco.*', 'pelicula.titulo')
            ->join('pelicula', 'pelicula_cod_pelicula', '=', 'pelicula.cod_pelicula')
            ->where('disco.isDeleted','<>',1)
            ->orderBy('cod_disco','asc')->paginate();;
        return view('backend.discos.index')->withDiscos($discos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('backend.discos.create');
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
            'no_copias' => 'required|max:45',
            'pelicula_cod_pelicula' => 'required',
            'formato' => 'required',
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
        $new_disco = new ModelDisco;
        $new_disco->no_copias = $request->no_copias;
        $new_disco->pelicula_cod_pelicula = $request->pelicula_cod_pelicula;
        $new_disco->formato = $request->formato;
        $new_disco->save();

        if (! $new_disco) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('Disco(s) ha sido agregado(s): '.$new_disco->cod_disco);
        
        return Redirect::route('admin.discos')
            ->withFlashInfo('Nuevo(s) Disco(s) Agregado(s)');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ModelDisco  $modelDisco
     * @return \Illuminate\Http\Response
     */
    public function show(ModelDisco $modelDisco)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModelDisco  $modelDisco
     * @return \Illuminate\Http\Response
     */
    public function edit( $codDisco)
    {
        //
        $disco = ModelDisco::findOrFail($codDisco);
   
        return view('backend.discos.edit')
            ->withDisco($disco);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelDisco  $modelDisco
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $codDisco)
    {
        // validate
        $rules = [
            'no_copias' => 'required|max:45',
            'pelicula_cod_pelicula' => 'required',
            'formato' => 'required',
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
        $disco = ModelDisco::findOrFail($codDisco);
        $disco->no_copias = $request->no_copias;
        $disco->pelicula_cod_pelicula = $request->pelicula_cod_pelicula;
        $disco->formato = $request->formato;
        $disco->isUpdated = 1;
        $disco->isSynced = 0;
        $disco->save();

        if (! $disco) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('El disco Cod#'.$disco->cod_disco.' ha sido actualizado.');
        
        return Redirect::route('admin.discos')
            ->withFlashInfo('Lo(s) disco(s) ha sido actualizado(s).');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelDisco  $modelDisco
     * @return \Illuminate\Http\Response
     */
    public function destroy ( $cod_disco)
    {
        //$this->roleRepository->deleteById($role->id);

        
        $disco = ModelDisco::find($cod_disco);
        $disco->isDeleted = 1;
        $disco->save();
        
        
        Log::info('El siguiente disco has sido eliminado: '.$disco->titulo);

        return redirect()->route('admin.discos')->withFlashSuccess('El disco ha sido eliminada.');
        
    }
}
