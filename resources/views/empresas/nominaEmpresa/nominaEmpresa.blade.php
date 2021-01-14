@extends('layouts.admin')
@section('title', 'Nómina por empresa')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection
@section('contenido')
<h1>Nómina empresa</h1>
<a class="btn btn-primary" href="#" id="addNominaEmpresa" dataId = "{{ request()->route()->parameters['idNomina'] }}">Agregar Nómina</a>
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "nominas">
        <thead>
            <tr>
                <th>ID Nómina</th>
                <th>Nombre</th>
                <th>Tipo Periodo</th>
                <th>Periodo</th>
                <th>ID Nomiina</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($nominaEmpresa as $nom)
            <tr>
                <td>{{ $nom->idNomina }}</td>
                <td>{{ $nom->nombre }}</td>
                <td>{{ $nom->tipoPeriodo }}</td>
                <td>{{ $nom->periodo }}</td>
                <td>{{ $nom->id_uni_nomina }}</td>
                <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a dataId ="{{ $nom->idNomina }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver Nómina</a>
                            <a dataId ="{{ $nom->idNomina }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar Nómina</a>
                            <a dataId ="{{ $nom->idNomina }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar Nómina</a>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="nominaEmpresaModal" tabindex="-1" role="dialog" aria-labelledby="nominaEmpresaModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='nominaEmpresa'></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/nomina/nominaEmpresa/nominaEmpresa.js') }}"></script>
@endsection
