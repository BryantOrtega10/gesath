@extends('layouts.admin')
@section('title', 'Grupo concepto')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<h1>Grupo conceptos</h1>
<a class="btn btn-primary" href="#" id="addGrupoConcepto">Agregar grupo concepto</a>
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "grupo_concepto">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grupos as $grupo)
            <tr>
                <th scope="row">{{ $grupo->idgrupoConcepto }}</th>
                <td>{{ $grupo->nombre }}</td>
                <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a dataId ="{{ $grupo->idgrupoConcepto }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver detalle</a>
                            <a dataId ="{{ $grupo->idgrupoConcepto }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar</a>
                            <a dataId ="{{ $grupo->idgrupoConcepto }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar</a>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="grupoConceptoModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='grupoConcepto'></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/grupoConcepto.js') }}"></script>
@endsection
