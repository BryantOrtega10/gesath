@extends('layouts.admin')
@section('title', 'Cargos')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<h1 class="granAzul">Cargos</h1>
<a class="btn btn-primary" href="#" id="addCargo">Agregar cargo</a>
<a class="btn btn-primary" href="/cargos/subirPlano" id="addCargo">Subir archivo plano</a>
<div class="cajaGeneral">
    <div class="table-responsive">
        <table class="table table-hover table-striped" id = "cargos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cargos as $cargo)
                <tr>
                    <td>{{ $cargo->idCargo }}</td>
                    <td>{{ $cargo->nombreCargo }}</td>
                    <td>
                        <div class="dropdown">
                            <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                            <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                                <a dataId ="{{ $cargo->idCargo }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver Cargo</a>
                                <a dataId ="{{ $cargo->idCargo }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar Cargo</a>
                                <a dataId ="{{ $cargo->idCargo }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar Cargo</a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="cargosModal" tabindex="-1" role="dialog" aria-labelledby="cargosModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='cargos'></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/cargos.js') }}"></script>
@endsection
