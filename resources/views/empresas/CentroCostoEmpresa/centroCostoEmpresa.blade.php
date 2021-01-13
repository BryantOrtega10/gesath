@extends('layouts.admin')
@section('title', 'Centro de costo empresa')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<h1>Centros de costo empresa</h1>
<a class="btn btn-primary" href="#" id="addCentroCosto" dataId = "{{ request()->route()->parameters['idEmpresa'] }}">Agregar Centro de costo</a>
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "centros_costos">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nombre</th>
                <th scope="col"># Centro interno</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($centrosCosto as $cen)
            <tr>
                <th scope="row">{{ $cen->idcentroCosto }}</th>
                <th scope="row">{{ $cen->nombre }}</th>
                <th scope="row">{{ $cen->id_uni_centro }}</th>
                <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a dataid ="{{ $cen->idcentroCosto }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver Centro</a>
                            <a dataid ="{{ $cen->idcentroCosto }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar Centro</a>
                            <a dataid ="{{ $cen->idcentroCosto }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar Centro</a>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="centroCostoModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='centroCosto'></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/centroCostoEmpresa/centroCostoEmpresa.js') }}"></script>
@endsection
