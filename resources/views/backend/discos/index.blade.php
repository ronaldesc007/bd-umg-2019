@extends('backend.layouts.app')

@section('title', app_name() . ' | '. __('labels.backend.access.roles.management'))

@section('content')
<div class="card mt-2">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    Discos
                </h4>
            </div><!--col-->

            <div class="col-sm-7 pull-right">
                @include('backend.discos.botones')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>CODIGO</th>
                            <th>NUMERO DE COPIAS</th>
                            <th>CODIGO PELICULA</th>
                            <th>TITULO PELICULA</th>
                            <th>FORMATO</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($discos as $disco)
                                <tr>
                                    <td>{{ $disco->cod_disco }}</td>
                                    <td>
                                        {{ $disco->no_copias }}
                                    </td>
                                    <td>{{ $disco->pelicula_cod_pelicula }}</td>
                                    <td>{{ $disco->titulo }}</td>
                                    <td>{{ $disco->formato }}</td>
                                    <td>@include('backend.discos.includes.actions', ['cod_disco' => $disco->cod_disco ])</td>
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
                    {!! $discos->total() !!} Registros
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $discos->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
