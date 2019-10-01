<?php

namespace App\Http\Controllers;

use App\ModelCliente;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Validator;
use Illuminate\Support\Facades\Log;

class ControllerCliente extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $clientes = ModelCliente::where('isDeleted','<>',1)->orderBy('no_membresia','asc')->paginate();;
        return view('backend.clientes.index')->withClientes($clientes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('backend.clientes.create');
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
            'nombre' => 'required|max:30',
            'apellido' => 'required|max:30',
            'direccion' => 'required|max:45',
            'telefono' => 'required|max:30',
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
        $new_cliente = new ModelCliente;
        $new_cliente->nombre = $request->nombre;
        $new_cliente->apellido = $request->apellido;
        $new_cliente->direccion = $request->direccion;
        $new_cliente->telefono = $request->telefono;
        $new_cliente->save();

        if (! $new_cliente) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('Un cliente ha sido agregado: '.$new_cliente->no_membresia);
        
        return Redirect::route('admin.clientes')
            ->withFlashInfo('Nuevo Cliente Agregado');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ModelCliente  $modelCliente
     * @return \Illuminate\Http\Response
     */
    public function show(ModelCliente $modelCliente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModelCliente  $modelCliente
     * @return \Illuminate\Http\Response
     */
    public function edit( $codCliente)
    {
        //
        $cliente = ModelCliente::findOrFail($codCliente);
   
        return view('backend.clientes.edit')
            ->withCliente($cliente);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelCliente  $modelCliente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $codCliente)
    {
        // validate
        $rules = [
            'nombre' => 'required|max:30',
            'apellido' => 'required|max:30',
            'direccion' => 'required|max:45',
            'telefono' => 'required|max:30',
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
        $cliente = ModelCliente::findOrFail($codCliente);
        $cliente->nombre = $request->nombre;
        $cliente->apellido = $request->apellido;
        $cliente->direccion = $request->direccion;
        $cliente->telefono = $request->telefono;
        $cliente->isUpdated = 1;
        $cliente->save();

        if (! $cliente) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('El cliente Cod#'.$cliente->no_membresia.' ha sido actualizado.');
        
        return Redirect::route('admin.clientes')
            ->withFlashInfo('El cliente ha sido actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelCliente  $modelCliente
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModelCliente $modelCliente)
    {
        //$this->roleRepository->deleteById($role->id);

        
        $cliente = ModelActor::find($cod_actor);
        $cliente->isDeleted = 1;
        $cliente->save();
        
        
        Log::info('El siguiente cliente has sido eliminada: '.$cliente->nombre);

        return redirect()->route('admin.clientes')->withFlashSuccess('El cliente ha sido eliminado.');
    }
}
