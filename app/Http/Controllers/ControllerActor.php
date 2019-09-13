<?php

namespace App\Http\Controllers;

use App\ModelActor;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Validator;
use Illuminate\Support\Facades\Log;

class ControllerActor extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $actor = ModelActor::orderBy('cod_actor','asc')->paginate();;
        return view('backend.actor.index')->withActor($actor);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('backend.actor.create');
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
            'nombre' => 'required|max:45',
            'fecha_nacimiento' => 'required',
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
        $new_actor = new ModelActor;
        $new_actor->nombre = $request->nombre;
        $new_actor->fecha_nacimiento = $request->fecha_nacimiento;
        $new_actor->save();

        if (! $new_actor) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('Un actor ha sido agregado: '.$new_actor->cod_actor);
        
        return Redirect::route('admin.actor')
            ->withFlashInfo('Nuevo Actor Agregado');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ModelActor  $modelActor
     * @return \Illuminate\Http\Response
     */
    public function show(ModelActor $modelActor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModelActor  $modelActor
     * @return \Illuminate\Http\Response
     */
    public function edit(ModelActor $modelActor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelActor  $modelActor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModelActor $modelActor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelActor  $modelActor
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModelActor $modelActor)
    {
        //
    }
}
