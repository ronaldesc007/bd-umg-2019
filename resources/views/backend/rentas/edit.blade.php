@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('strings.backend.dashboard.title'))

@section('content')
    <div class="row">
        <div class="col">
            <div class="card mt-2">
                <div class="card-header">
                    <strong>Renta</strong>
                </div><!--card-header-->
                <div class="card-body">
                    
                    <form method="post" action="{{ route ('admin.rentas.update',$renta['cod_renta'])}}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="fecha_renta">Fecha renta:</label>
                            <input type="date" name="fecha_renta" id="fecha_renta" class="form-control" placeholder="Ingrese el fecha de nacimiento" value="{{ old('fecha_renta') ? old('fecha_renta') : $renta['fecha_renta'] }}">                        
                        </div>
                    </div>        
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="valor_renta">Valor renta:</label>
                            <input type="text" name="valor_renta" id="valor_renta" class="form-control" placeholder="Ingrese el valor" value="{{ old('valor_renta') ? old('nombre') : $renta['valor_renta'] }}">
                        </div>
                    </div>        
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="cod_cliente">Codigo cliente:</label>
                            <input type="text" name="cod_cliente" id="cod_cliente" class="form-control" placeholder="Ingrese el nombre" value="{{ old('cod_cliente') ? old('cod_cliente') : $renta['cod_cliente'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="cod_disco">Codigo disco:</label>
                            <input type="text" name="cod_disco" id="cod_disco" class="form-control" placeholder="Ingrese el codigo de disco" value="{{ old('cod_disco') ? old('cod_disco') : $renta['cod_disco'] }}">
                        </div>
                    </div>
            
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block btn-flat btn-lg">Guardar</button>
                    </div>
        </div>
        
        
      </form>
                    
                    
                    
                    
                    
                </div><!--card-body-->
            </div><!--card-->
        </div><!--col-->
    </div><!--row-->
@endsection
