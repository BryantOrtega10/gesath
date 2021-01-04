@extends('layouts.admin')
@section('title', 'Catalogo contable')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="cajaGeneral">
    <h1>Catalogo contable</h1>
    <form autocomplete="off" action="/catalogo-contable/" method="GET"  class="formGeneral" id="filtrar">
        @csrf    
        <div class="row">
            <div class="col-3">
                <div class="form-group @isset($req->descripcion) hasText @endisset">
                    <label for="fechaInicio" class="control-label">Descripcion:</label>
                    <input type="text" name="descripcion" class="form-control" @isset($req->descripcion) value="{{$req->descripcion}}" @endisset/>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group @isset($req->idempresa) hasText @endisset">
                    <label for="idempresa" class="control-label">Empresa:</label>
                    <select class="form-control" name="idempresa" id="idempresa">
                        <option value=""></option>
                        @foreach($empresas as $empresa)
                            <option value="{{$empresa->idempresa}}" @isset($req->idempresa) @if ($req->idempresa == $empresa->idempresa) selected @endif @endisset>{{$empresa->razonSocial}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group @isset($req->idcentroCosto) hasText @endisset">
                    <label for="idcentroCosto" class="control-label">Centro de costo:</label>
                    <select class="form-control" name="idcentroCosto" id="idcentroCosto">
                        <option value=""></option>
                        @foreach($centros_costos as $centro_costo)
                            <option value="{{$centro_costo->idcentroCosto}}" @isset($req->idcentroCosto) @if ($req->idcentroCosto == $centro_costo->idcentroCosto) selected @endif @endisset>{{$centro_costo->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3"><input type="submit" value="Consultar"/><input type="reset" class="recargar" value="" /> </div>
        </div>        
    </form>

    <div class="text-left">
        <a class="btn btn-primary" href="#" id="addCuenta">Agregar cuenta</a>
        <a class="btn btn-primary" href="/catalogo-contable/subirPlano" id="addCuenta">Agregar por archivo plano</a>

    </div>
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
    {{ $catalogo->appends($arrConsulta)->links() }}
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