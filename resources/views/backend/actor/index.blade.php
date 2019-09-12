@extends('backend.layouts.app')

@section('title', app_name() . ' | '. __('labels.backend.access.roles.management'))

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    Actores
                </h4>
            </div><!--col-->

            <div class="col-sm-7 pull-right">
                @include('backend.actor.botones')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>CODIGO</th>
                            <th>NOMBRE</th>
                            <th>FECHA DE NACIMIENTO</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($actor as $actor)
                                <tr>
                                    <td>{{ $actor->cod_actor }}</td>
                                    <td>
                                        {{ $actor->nombre }}
                                    </td>
                                    <td>{{ $actor->fecha_nacimiento }}</td>
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
                    {!! $actor->total() !!} Registros
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $actor->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
