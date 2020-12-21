@extends('layouts.admin')
@section('title', 'Reporte contable')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Reporte contable</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formReporteNomina" autocomplete="off" class="formGeneral" action="/catalogo-contable/generarReporteNomina">
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
                            <label for="fechaReporte" class="control-label">Fecha Reporte</label>
                            <input type="date" class="form-control" id="fechaReporte" name="fechaReporte" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Generar Reporte" class="btnSubmitGen" /></div>
                    </div>
                </div>                
            </form>
        </div>
    </div>
</div>

@endsection