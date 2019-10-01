<?php

namespace App\Http\Controllers;

use App\ModelRenta;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ControllerRenta extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $rentas = ModelRenta::where('isDeleted','<>',1)->orderBy('cod_renta','asc')->paginate();;
        return view('backend.rentas.index')->withRentas($rentas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('backend.rentas.create');
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
            'fecha_renta' => 'required',
            'dias_renta' => 'required|integer',
            'valor_renta' => 'required',
            'cod_cliente' => 'required',
            'cod_disco' => 'required',
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
        $new_renta = new ModelRenta;
        $new_renta->fecha_renta = $request->fecha_renta;
        $nueva_fecha=Carbon::createFromDate($request->fecha_renta);
        $new_renta->fecha_devolucion = $nueva_fecha->addDays($request->dias_renta);
        $new_renta->valor_renta = $request->valor_renta;
        $new_renta->cliente_no_membresia = $request->cod_cliente;
        $new_renta->disco_cod_disco = $request->cod_disco;
        $new_renta->save();

        if (! $new_renta) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('Una renta se ha registrado: '.$new_renta->cod_renta);
        
        return Redirect::route('admin.rentas')
            ->withFlashInfo('Nueva renta registrada');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ModelRenta  $modelRenta
     * @return \Illuminate\Http\Response
     */
    public function show(ModelRenta $modelRenta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ModelRenta  $modelRenta
     * @return \Illuminate\Http\Response
     */
    public function edit( $codRenta)
    {
        //
        $renta = ModelActor::findOrFail($codRenta);
   
        return view('backend.rentas.edit')
            ->withActor($renta);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModelRenta  $modelRenta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $modelRenta)
    {
        //
        $rules = [
            'fecha_renta',
            'fecha_devolucion' ,
            'valor_renta',
            'cod_cliente' => 'required',
            'cod_disco' => 'required',
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
        $renta = ModelRenta::findOrFail($codRenta);
        $renta->fecha_renta = $request->fecha_renta;
        $renta->fecha_devolucion = $request->fecha_devolucion;
        $renta->valor_renta = $request->valor_renta;
        $renta->cliente_no_membresia = $request->cod_cliente;
        $renta->disco_cod_disco = $request->cod_disco;
        $renta->isUpdated = 1;
        $renta->save();

        if (! $renta) {
            DB::rollback(); //Rollback Transaction
            return Redirect::back()->withInput()->withFlashDanger('DB::Error');
        }
        
        DB::commit(); // Commit if no error
        
        Log::info('La renta Cod#'.$renta->cod_renta.' ha sido actualizada.');
        
        return Redirect::route('admin.rentas')
            ->withFlashInfo('La renta ha sido actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ModelRenta  $modelRenta
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModelRenta $modelRenta)
    {
        //
        $renta = ModelRenta::find($cod_renta);
        $renta->isDeleted = 1;
        $renta->save();
        
        
        Log::info('La siguiente renta ha sido eliminada: '.$renta->nombre);

        return redirect()->route('admin.rentas')->withFlashSuccess('La renta ha sido eliminada.');
    }
}
