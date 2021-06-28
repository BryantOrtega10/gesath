@extends('layouts.admin')
@section('title', 'Nómina por empresa')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection
@section('contenido')
<div class="row">
    <div class="col-9">
        <h1 class="granAzul">Nómina empresa</h1>
    </div>
    @if (in_array("110",$dataUsu->permisosUsuario))
    <div class="col-3 text-right">
        <a class="btn btnAzulGen btnGeneral text-center" href="#" id="addNominaEmpresa" dataId = "{{ request()->route()->parameters['idNomina'] }}">Agregar Nómina</a>
    </div>
    @endif
</div>
<div class="cajaGeneral">
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "nominas">
        <thead>
            <tr>
                <th>ID Nómina</th>
                <th>Nombre</th>
                <th>Periodo</th>
                <th>D&iacute;as cesantias</th>
                <th>ID Nómina</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($nominaEmpresa as $nom)
            <tr>
                <td>{{ $nom->idNomina }}</td>
                <td>{{ $nom->nombre }}</td>
                <td>{{ $nom->periodo }}</td>
                <td>{{ $nom->diasCesantias }}</td>
                <td>{{ $nom->id_uni_nomina }}</td>
                <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            @if (in_array("109",$dataUsu->permisosUsuario))
                            <a dataId ="{{ $nom->idNomina }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver Nómina</a>
                            @endif
                            @if (in_array("111",$dataUsu->permisosUsuario))
                            <a dataId ="{{ $nom->idNomina }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar Nómina</a>
                            @endif
                            @if (in_array("112",$dataUsu->permisosUsuario))
                            <a dataId ="{{ $nom->idNomina }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar Nómina</a>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
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
