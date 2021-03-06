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
                    
                    <form method="post" action="{{ route ('admin.repartos.update',$reparto['cod_reparto'])}}">
                    @csrf        
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="cod_pelicula">Pelicula:</label>
                            
                            @if(old('cod_pelicula'))
                                <select name="cod_pelicula" id="cod_pelicula" class="form-control">
                                    <option selected disabled>Seleccione</option>
                                    @foreach ($peliculas->all() as $item)
                                        <option value="{{$item->cod_pelicula}}" {{ old('cod_pelicula') == $item->cod_pelicula ? 'selected' : '' }}>{{$item->cod_pelicula}} - {{$item->titulo}}</option>
                                    @endforeach 
                                </select>
                            @else
                                <select name="cod_pelicula" id="cod_pelicula" class="form-control">
                                    <option selected disabled>Seleccione</option>
                                    @foreach ($peliculas->all() as $item)
                                        <option value="{{$item->cod_pelicula}}" {{ $reparto['pelicula_cod_pelicula'] == $item->cod_pelicula ? 'selected' : '' }}>{{$item->cod_pelicula}} - {{$item->titulo}}</option>
                                    @endforeach 
                                </select>
                            @endif
                            
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="cod_actor">Codigo actor:</label>
                            @if(old('cod_actor'))
                                <select name="cod_actor" id="cod_actor" class="form-control">
                                    <option selected disabled>Seleccione</option>
                                    @foreach ($actores->all() as $item)
                                        <option value="{{$item->cod_actor}}" {{ old('cod_actor') == $item->cod_actor ? 'selected' : '' }}>{{$item->cod_actor}} - {{$item->nombre}}</option>
                                    @endforeach 
                                </select>
                            @else
                                <select name="cod_actor" id="cod_actor" class="form-control">
                                    <option selected disabled>Seleccione</option>
                                    @foreach ($actores->all() as $item)
                                        <option value="{{$item->cod_actor}}" {{ $reparto['actor_cod_actor'] == $item->cod_actor ? 'selected' : '' }}>{{$item->cod_actor}} - {{$item->nombre}}</option>
                                    @endforeach 
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
