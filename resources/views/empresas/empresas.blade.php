@extends('layouts.admin')
@section('title', 'Empresa')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<h1 class="granAzul">Empresas</h1>
<a class="btn btn-primary" href="#" id="addEmpresa">Agregar empresa</a>
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "empresas">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Razón social</th>
                <th scope="col">Dirección</th>
                <th scope="col">Teléfono</th>
                <th scope="col">Correo</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($empresas as $empresa)
            <tr>
                <th scope="row">{{ $empresa->idempresa }}</th>
                <td>{{ $empresa->razonSocial }}</td>
                <td>{{ $empresa->direccion }}</td>
                <td>{{ $empresa->telefonoFijo }}</td>
                <td>{{ $empresa->email1 }}</td>
                <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a dataId ="{{ $empresa->idempresa }}" class="dropdown-item centro_costo" href = "/empresa/centroCosto/{{$empresa->idempresa}}"><i class="fas fa-dollar-sign"></i> Centros de costo</a>
                            <a dataId ="{{ $empresa->idempresa }}" class="dropdown-item centro_costo" href = "/empresa/nomina/{{$empresa->idempresa}}"><i class="fas fa-money-bill-alt"></i> Nómina</a>
                            <a dataId ="{{ $empresa->idempresa }}" class="dropdown-item centro_costo" href = "/empresa/smtp/{{$empresa->idempresa}}"><i class="far fa-envelope"></i> Configuración SMTP</a>
                            <a dataId ="{{ $empresa->idempresa }}" class="dropdown-item centroTrabajo" href = "/empresa/centroTrabajo/{{$empresa->idempresa}}"><i class="fas fa-briefcase"></i> Centros de trabajo</a>
                            <a dataId ="{{ $empresa->idempresa }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver Empresa</a>
                            <a dataId ="{{ $empresa->idempresa }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar Empresa</a>
                            <a dataId ="{{ $empresa->idempresa }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar Empresa</a>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="empresasModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='empresas'></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/empresas.js') }}"></script>
@endsection
