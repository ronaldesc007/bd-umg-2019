@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('strings.backend.dashboard.title'))

@section('content')
    <div class="row">
        <div class="col">
            <div class="card mt-2">
                <div class="card-header">
                    <strong>Reparto</strong>
                </div><!--card-header-->
                <div class="card-body">
                    
                    <form method="post" action="{{ route ('admin.actores.update',$actor['cod_reparto'])}}">
                    @csrf        
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="cod_pelicula">Codigo pelicula:</label>
                            <input type="text" name="cod_pelicula" id="cod_pelicula" class="form-control" placeholder="Ingrese el codigo" value="{{ old('cod_pelicula') ? old('cod_pelicula') : $actor['cod_pelicula'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="cod_actor">Codigo actor:</label>
                            <input type="text" name="cod_actor" id="cod_actor" class="form-control" placeholder="Ingrese el codigo" value="{{ old('cod_actor') ? old('cod_actor') : $actor['cod_actor'] }}">
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
