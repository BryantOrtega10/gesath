@extends('layouts.admin')
@section('title', 'Administrar formulario 220')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="cajaGeneral">
    <h1 class="granAzul">Administrar Formulario 220</h1>
    @if (in_array("135",$dataUsu->permisosUsuario))
    <a class="btn btn-primary" href="#" id="addFormulario220">Agregar Año</a>
    @endif
    <div class="table-responsive">
        <table class="table table-hover table-striped" id = "formularios">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Año</th>
                    <th scope="col">Imagen</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($formularios as $formulario)
                    <tr>
                        <td>{{$formulario->idFormulario220}}</td>
                        <td>{{$formulario->anio}}</td>
                        <td><img src="{{ Storage::url($formulario->rutaImagen) }}" class="formulario" /></td>
                        <td>
                            @if (in_array("136",$dataUsu->permisosUsuario))
                            <a href="/formulario220/getForm/edit/{{$formulario->idFormulario220}}" class="editar">Editar</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="formulario220Modal" tabindex="-1" role="dialog" aria-labelledby="formulario220Modal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/formulario220.js') }}"></script>
@endsection
