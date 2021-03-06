@extends('layouts.admin')
@section('title', 'Reporteador')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-9">
        <h1 class="granAzul">Reporteador</h1>
    </div>
    <div class="col-3 text-right">
        <a class="btn btnGeneral btnAzulGen text-center" href="#" id="addReporte">Agregar Reporte</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id = "formularios">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Fecha Creación</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportes as $reporte)
                            <tr>
                                <td>{{$reporte->idReporte}}</td>
                                <td>{{$reporte->nombre}}</td>
                                <td>{{$reporte->fechaCreacion}}</td>
                                <td>
                                    <div class="btn-group">
                                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="/reportes/reporteador/getForm/edit/{{$reporte->idReporte}}" class="editar dropdown-item">Editar</a>
                                            <a href="/reportes/reporteador/generarReporte/{{$reporte->idReporte}}" class="generarReporte dropdown-item">Generar Reporte</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="reporteadorModal" tabindex="-1" role="dialog" aria-labelledby="reporteadorModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/reportes/reporteador.js') }}"></script>

@endsection