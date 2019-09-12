@extends('backend.layouts.app')

@section('title', app_name() . ' | '. __('labels.backend.access.roles.management'))

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    Mantenimiento
                </h4>
            </div><!--col-->

            <div class="col-sm-7 pull-right">
                @include('backend.peliculas.botones')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>CODIGO</th>
                            <th>TITULO</th>
                            <th>CATEGORIA</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($peliculas as $pelicula)
                                <tr>
                                    <td>{{ $pelicula->cod_pelicula }}</td>
                                    <td>
                                        {{ $pelicula->titulo }}
                                    </td>
                                    <td>{{ $pelicula->categoria }}</td>
                                    <td> </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div><!--col-->
        </div><!--row-->
        <div class="row">
            <div class="col-7">
                <div class="float-left">
                    {!! $peliculas->total() !!} Registros
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $peliculas->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
