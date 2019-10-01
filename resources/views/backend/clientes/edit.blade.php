@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('strings.backend.dashboard.title'))

@section('content')
    <div class="row">
        <div class="col">
            <div class="card mt-2">
                <div class="card-header">
                    <strong>Cliente</strong>
                </div><!--card-header-->
                <div class="card-body">
                    
                    <form method="post" action="{{ route ('admin.clientes.update',$cliente['no_membresia'])}}">
                    @csrf        
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="nombre">Nombre:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ingrese el nombre" value="{{ old('nombre') ? old('nombre') : $cliente['nombre'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="apellido">Apellido:</label>
                            <input type="text" name="apellido" id="apellido" class="form-control" placeholder="Ingrese el apellido" value="{{ old('apellido') ? old('apellido') : $cliente['apellido'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="direccion">Direccion:</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Ingrese la direccion" value="{{ old('direccion') ? old('direccion') : $cliente['direccion'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="telefono">Telefono:</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" placeholder="Ingrese el telefono" value="{{ old('telefono') ? old('telefono') : $cliente['telefono'] }}">
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
