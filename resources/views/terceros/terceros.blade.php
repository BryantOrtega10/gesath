@extends('layouts.admin')
@section('title', 'Tercero')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-8">
        <h1 class="granAzul">Terceros</h1>
    </div>
    <div class="col-2 text-right">
        <a class="btn btnAzulGen btnGeneral text-center" href="/terceros/exportar"> <i class="fas fa-download"></i> Exportar lista terceros</a>
    </div>
    <div class="col-2 text-right">
        <a class="btn btnAzulGen btnGeneral text-center" href="#" id="addTercero">Agregar tercero</a>
    </div>
</div>
<div class="cajaGeneral">
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "terceros">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nombre</th>
                <th scope="col">Documento</th>
                <th scope="col">Correo</th>
                <th scope="col">Teléfono</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($terceros as $tercero)
            <tr>
                <th scope="row">{{ $tercero->idTercero }}</th>
                <td>
                    @if($tercero->naturalezaTributaria === 'Juridico')
                        {{ $tercero->razonSocial }}
                    @else
                        {{ $tercero->primerNombre }} {{ $tercero->primerApellido }}
                    @endif 
                </td>
                <td>{{ $tercero->numeroIdentificacion }}</td>
                <td>{{ $tercero->correo }}</td>
                <td>{{ $tercero->telefono }}</td>
                <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a dataId ="{{ $tercero->idTercero }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver detalle</a>
                            <a dataId ="{{ $tercero->idTercero }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar</a>
                            <a dataId ="{{ $tercero->idTercero }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar</a>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
<div class="modal fade" id="tercerosModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='terceros'></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/terceros.js') }}"></script>
@endsection
