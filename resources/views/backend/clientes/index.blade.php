@extends('backend.layouts.app')

@section('title', app_name() . ' | '. __('labels.backend.access.roles.management'))

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    Clientes
                </h4>
            </div><!--col-->

            <div class="col-sm-7 pull-right">
                @include('backend.clientes.botones')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>MEMBRESIA</th>
                            <th>NOMBRE</th>
                            <th>APELLIDO</th>
                            <th>DIRECCION</th>
                            <th>TELEFONO</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->no_membresia }}</td>
                                    <td>
                                        {{ $cliente->nombre }}
                                    </td>
                                    <td>{{ $cliente->apellido }}</td>
                                    <td>{{ $cliente->direccion }}</td>
                                    <td>{{ $cliente->telefono }}</td>
                                    <td>@include('backend.clientes.includes.actions', ['no_membresia' => $cliente->no_membresia ])</td>
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
                    {!! $clientes->total() !!} Registros
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $clientes->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
