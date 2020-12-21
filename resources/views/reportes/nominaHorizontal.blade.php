@extends('layouts.admin')
@section('title', 'Reporte nomina horizontal')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Reporte nomina horizontal</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formCierre" autocomplete="off" class="formGeneral" action="/reportes/documentoNominaHorizontalFechas">
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
                        <div class="form-group">
                            <label for="fechaInicio" class="control-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaFin" class="control-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin" name="fechaFin" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Generar reporte" class="btnSubmitGen" /></div>
                    </div>
                </div>                
            </form>
        </div>
    </div>
</div>

@endsection