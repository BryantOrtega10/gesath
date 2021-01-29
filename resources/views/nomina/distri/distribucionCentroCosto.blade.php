@extends('layouts.admin')
@section('title', 'Distibucion centro de costos')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Distibucion centro de costos</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <a class="btn btn-primary" href="#" id="addDistri">Agregar nueva distribucion</a>
            <table class="table table-hover table-striped">
                <tr>
                    <th>&num;</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Nomina</th>
                    <th></th>
                </tr>
                @foreach ($distris_centro_costo as $distri_centro_costo)
                    <tr>
                        <th>
                            {{$distri_centro_costo->id_distri_centro_costo }}
                        </th>
                        <td>
                            {{$distri_centro_costo->fechaInicio }}
                        </td>
                        <td>
                            {{$distri_centro_costo->fechaFin }}
                        </td>
                        <td>
                            {{$distri_centro_costo->nombre }}
                        </td>
                        <td>
                            <a href="/nomina/distri/modificarDistri/{{$distri_centro_costo->id_distri_centro_costo}}">Modificar</a>
                            <a href="/nomina/distri/copiarDistri/{{$distri_centro_costo->id_distri_centro_costo}}" class="copiarDistri">Copiar</a>
                        </td>
                    </tr>
                @endforeach
            </table>
            {{ $distris_centro_costo->links() }}
        </div>
        
    </div>
</div>
<div class="modal fade" id="distriModal" tabindex="-1" role="dialog" aria-labelledby="distriModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/distri.js')}}"></script>
@endsection