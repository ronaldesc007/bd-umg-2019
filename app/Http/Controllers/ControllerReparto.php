<?php

namespace App\Http\Controllers;

use App\ModelReparto;
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
        $repartos = ModelReparto::where('isDeleted','<>',1)->orderBy('cod_reparto','asc')->paginate();;
        return view('backend.repartos.index')->withRepartos($reparto);
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
        $new_reparto = new ModelReparto;
        $new_reparto->titulo = $request->titulo;
        $new_reparto->categoria = $request->categoria;
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
    public function edit(ModelReparto $modelReparto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelReparto  $modelReparto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModelReparto $modelReparto)
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
        $reparto = ModelReparto::findOrFail($codReparto);
        $reparto->titulo = $request->titulo;
        $reparto->categoria = $request->categoria;
        $reparto->isUpdated = 1;
        $reparto->isSynced = 0;
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
    public function destroy(ModelReparto $modelReparto)
    {
        //$this->roleRepository->deleteById($role->id);

        
        $reparto = ModelReparto::find($cod_reparto);
        $reparto->isDeleted = 1;
        $reparto->save();
        
        
        Log::info('El siguiente reparto has sido eliminado: '.$reparto->titulo);

        return redirect()->route('admin.repartos')->withFlashSuccess('El reparto ha sido eliminada.');
    }
}
