@extends('layouts.admin')
@section('title', 'Descargar catalogo contable')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Descargar catalogo contable</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="" autocomplete="off" class="formGeneral" action="/catalogo-contable/descargarArchivoxEmpresa" enctype="multipart/form-data">
                @csrf
                <div class="form-group @isset($req->idempresa) hasText @endisset">
                    <label for="idempresa" class="control-label">Empresa:</label>
                    <select class="form-control" name="idempresa" required id="idempresa">
                        <option value=""></option>
                        @foreach($empresas as $empresa)
                            <option value="{{$empresa->idempresa}}">{{$empresa->razonSocial}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center"><input type="submit" value="Descargar" class="btnSubmitGen" /></div>
            </form>
        </div>
    </div>
</div>

@endsection
