@extends('backend.layouts.app')

@section('title', app_name() . ' | '. __('labels.backend.access.roles.management'))

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    Rentas
                </h4>
            </div><!--col-->

            <div class="col-sm-7 pull-right">
                @include('backend.rentas.botones')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>CODIGO RENTA</th>
                            <th>FECHA RENTA</th>
                            <th>FECHA DEVOLUCION</th>
                            <th>VALOR RENTA</th>
                            <th>CODIGO CLIENTE</th>
                            <th>NOMBRE CLIENTE</th>
                            <th>CODIGO DISCO</th>
                            <th>TITULO PELICULA</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($rentas as $renta)
                                <tr>
                                    <td>{{ $renta->cod_renta }}</td>
                                    <td>
                                        {{ $renta->fecha_renta }}
                                    </td>
                                    <td>
                                        {{ $renta->fecha_devolucion }}
                                    </td>
                                    <td>{{ $renta->valor_renta}}</td>
                                    <td>{{ $renta->cliente_no_membresia}}</td>
                                    <td>{{ $renta->nombre}}</td>
                                    <td>{{ $renta->disco_cod_disco }}</td>
                                    <td>{{ $renta->titulo }}</td>
                                    <td>@include('backend.rentas.includes.actions', ['cod_renta' => $renta->cod_renta ])</td>
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
                    {!! $rentas->total() !!} Registros
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $rentas->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
