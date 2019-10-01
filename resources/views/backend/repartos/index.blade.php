@extends('backend.layouts.app')

@section('title', app_name() . ' | '. __('labels.backend.access.roles.management'))

@section('content')
<div class="card mt-2">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    Repartos
                </h4>
            </div><!--col-->

            <div class="col-sm-7 pull-right">
                @include('backend.repartos.botones')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>CODIGO</th>
                            <th>CODIGO PELICULA</th>
                            <th>CODIGO ACTOR</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($repartos as $reparto)
                                <tr>
                                    <td>{{ $reparto->cod_reparto }}</td>
                                    <td>
                                        {{ $reparto->pelicula_cod_pelicula }}
                                    </td>
                                    <td>{{ $reparto->actor_cod_actor }}</td>
                                    <td>@include('backend.repartos.includes.actions', ['cod_reparto' => $reparto->cod_reparto ])</td>
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
                    {!! $repartos->total() !!} Registros
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $repartos->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
