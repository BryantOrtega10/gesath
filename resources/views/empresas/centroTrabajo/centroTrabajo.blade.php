@extends('layouts.admin')
@section('title', 'Centro Trabajo')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<h1>Centro Trabajo</h1>
<a class="btn btn-primary" href="#" id="addCentroTrabajo" data-id = "{{ $idEmpre }}">Agregar centro trabajo</a>
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "centroTrabajo">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Código</th>
                <th scope="col">Nombre</th>
                <th scope="col">Nivel ARL</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($centroTrabajos as $centro)
            <tr>
                <th scope="row">{{ $centro->idCentroTrabajo }}</th>
                <td>{{ $centro->codigo }}</td>
                <td>{{ $centro->nombre }}</td>
                <td>{{ $centro->fkNivelArl }}</td>
                <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a dataId ="{{ $centro->idCentroTrabajo }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver detalle</a>
                            <a dataId ="{{ $centro->idCentroTrabajo }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar</a>
                            <a dataId ="{{ $centro->idCentroTrabajo }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar</a>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="centroTrabajoModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='centroTrabajo'></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/centroTrabajo.js') }}"></script>
@endsection
