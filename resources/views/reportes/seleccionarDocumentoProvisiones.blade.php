@extends('layouts.admin')
@section('title', 'Descargar archivo provisiones')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Descargar archivo provisiones</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formDocumentoProvisiones" autocomplete="off" class="formGeneral" action="/reportes/documentoProv">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="infoEmpresa" class="control-label">Empresa</label>
                            <select class="form-control" id="infoEmpresa" name="empresa">
                                <option value=""></option>        
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}">{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>    
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="provision" class="control-label">Provision</label>
                            <select class="form-control" id="provision" name="provision">
                                <option value="Consolidado">Consolidado</option>
                                <option value="73">Prima</option>
                                <option value="71">Cesantias</option>
                                <option value="72">Intereses de cesantias</option>
                                <option value="74">Vacaciones</option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaDocumento" class="control-label">Fecha Informe</label>
                            <input type="date" class="form-control" id="fechaDocumento" name="fechaDocumento" />
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="DESCARGAR" class="btnSubmitGen" /></div>
                    </div>
                </div>                
            </form>
        </div>
    </div>
</div>

@endsection