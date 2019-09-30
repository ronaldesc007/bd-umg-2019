@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('strings.backend.dashboard.title'))

@section('content')
    <div class="row">
        <div class="col">
            <div class="card mt-2">
                <div class="card-header">
                    <strong>PELICULAS</strong>
                </div><!--card-header-->
                <div class="card-body">
                    
                    <form method="post" action="{{ route ('admin.peliculas.guardar')}}">
                    @csrf                                            
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="titulo">Titulo de la pelicula:</label>
                            <input type="text" name="titulo" id="titulo" class="form-control" placeholder="Ingrese el titulo" value="{{old('titulo')}}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="categoria">Categoria:</label>
                            <select name="categoria" id="categoria" class="form-control">
                                <option selected disabled>Seleccione</option>
                                <option value="Acción" {{ old('categoria') == 'Acción' ? 'selected' : '' }}>Acción</option>
                                <option value="Aventura" {{ old('categoria') == 'Aventura' ? 'selected' : '' }}>Aventura</option>
                                <option value="Comedia" {{ old('categoria') == 'Comedia' ? 'selected' : '' }}>Comedia</option>
                                <option value="Drama" {{ old('categoria') == 'Drama' ? 'selected' : '' }}>Drama</option>
                                <option value="Terror" {{ old('categoria') == 'Terror' ? 'selected' : '' }}>Terror</option>
                                <option value="Musicales" {{ old('categoria') == 'Musicales' ? 'selected' : '' }}>Musicales</option>
                                <option value="SciFi" {{ old('categoria') == 'SciFi' ? 'selected' : '' }}>SciFi</option>
                                <option value="Guerra" {{ old('categoria') == 'Guerra' ? 'selected' : '' }}>Guerra</option>
                                <option value="Suspenso" {{ old('categoria') == 'Suspenso' ? 'selected' : '' }}>Suspenso</option>
                                <option value="Infantiles" {{ old('categoria') == 'Infantiles' ? 'selected' : '' }}>Infantiles</option>
                          </select>
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
