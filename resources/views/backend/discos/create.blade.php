@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('strings.backend.dashboard.title'))

@section('content')
    <div class="row">
        <div class="col">
            <div class="card mt-2">
                <div class="card-header">
                    <strong>DISCOS</strong>
                </div><!--card-header-->
                <div class="card-body">
                    
                    <form method="post" action="{{ route ('admin.discos.guardar')}}">
                    @csrf                                            
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="no_copias">Numero de copias:</label>
                            <input type="text" name="no_copias" id="no_copias" class="form-control" placeholder="Ingrese el numero" value="{{old('no_copias')}}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="pelicula_cod_pelicula">Codigo pelicula:</label>
                            <input type="text" name="pelicula_cod_pelicula" id="pelicula_cod_pelicula" class="form-control" placeholder="Ingrese el codigo" value="{{old('pelicula_cod_pelicula')}}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="formato">Formato:</label>
                            <select name="formato" id="formato" class="form-control">
                                <option selected disabled>Seleccione</option>
                                <option value="DVD" {{ old('formato') == 'DVD' ? 'selected' : '' }}>DVD</option>
                                <option value="Blu-Ray" {{ old('formato') == 'Blu-Ray' ? 'selected' : '' }}>Blu-Ray</option>
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
