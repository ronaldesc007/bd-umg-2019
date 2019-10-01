@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('strings.backend.dashboard.title'))

@section('content')
    <div class="row">
        <div class="col">
            <div class="card mt-2">
                <div class="card-header">
                    <strong>Disco</strong>
                </div><!--card-header-->
                <div class="card-body">
                    
                    <form method="post" action="{{ route ('admin.discos.update',$disco['cod_disco'])}}">
                    @csrf        
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="no_copias">Numero de copias:</label>
                            <input type="text" name="no_copias" id="no_copias" class="form-control" placeholder="Ingrese el codigo" value="{{ old('no_copias') ? old('no_copias') : $disco['no_copias'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="pelicula_cod_pelicula">Pelicula:</label>
                            
                            @if(old('pelicula_cod_pelicula'))
                                <select name="pelicula_cod_pelicula" id="pelicula_cod_pelicula" class="form-control">
                                    <option selected disabled>Seleccione</option>
                                    @foreach ($peliculas->all() as $item)
                                        <option value="{{$item->cod_pelicula}}" {{ old('pelicula_cod_pelicula') == $item->cod_pelicula ? 'selected' : '' }}>{{$item->titulo}}</option>
                                    @endforeach 
                                </select>
                            @else
                                <select name="pelicula_cod_pelicula" id="pelicula_cod_pelicula" class="form-control">
                                    <option selected disabled>Seleccione</option>
                                    @foreach ($peliculas->all() as $item)
                                        <option value="{{$item->cod_pelicula}}" {{ $disco['pelicula_cod_pelicula'] == $item->cod_pelicula ? 'selected' : '' }}>{{$item->titulo}}</option>
                                    @endforeach 
                                </select>
                            @endif
                            
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="formato">Formato:</label>
                            
                            @if(old('formato'))
                                <select name="formato" id="formato" class="form-control">
                                    <option selected disabled>Seleccione</option>
                                    <option value="DVD" {{ old('formato') == 'DVD' ? 'selected' : '' }}>DVD</option>
                                    <option value="Blu-Ray" {{ old('formato') == 'Blu-Ray' ? 'selected' : '' }}>Blu-Ray</option>
                                </select>
                            @else
                                <select name="formato" id="formato" class="form-control">
                                    <option selected disabled>Seleccione {{ $disco['formato'] }}  </option>
                                    <option value="DVD" {{ $disco['formato'] == 'DVD' ? 'selected' : '' }}>DVD</option>
                                    <option value="Blu-Ray" {{ $disco['formato'] == 'Blu-Ray' ? 'selected' : '' }}>Blu-Ray</option>
                                    </select>
                            @endif
                        
                            
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
