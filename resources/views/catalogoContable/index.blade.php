@extends('layouts.admin')
@section('title', 'Catalogo contable')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="cajaGeneral">
    <h1>Catalogo contable</h1>
    <a class="btn btn-primary" href="#" id="addCuenta">Agregar cuenta</a>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <tr>
                <th scope="col">Cuenta</th>
                <th scope="col">Descripcion</th>
                <th scope="col">Tipo Tercero</th>
                <th scope="col">Empresa</th>
                <th scope="col">Centro costo</th>
                <th scope="col"></th>
            </tr>
            @foreach ($catalogo as $cata)
            <tr>
                <th scope="row">{{ $cata->cuenta }}</th>
                <th scope="row">{{ $cata->descripcion }}</th>
                <td>{{ $cata->tipoTercero_nm }}</td>
                <td>{{ $cata->empresa_nm }}</td>
                <td>
                    @if ($cata->centroCosto_nm == "")
                        Todos
                    @else
                        {{ $cata->centroCosto_nm }}
                    @endif
                    
                </td>
                <td>
                    <a href="/catalogo-contable/getForm/edit/{{ $cata->idCatalgoContable }}" class="editar"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    {{ $catalogo->links() }}
</div>
<div class="modal fade" id="catalogoModal" tabindex="-1" role="dialog" aria-labelledby="catalogoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/catalogo.js') }}"></script>
@endsection