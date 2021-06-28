@extends('layouts.admin')
@section('title', 'Reporte novedades')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Reporte novedades</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" autocomplete="off" class="formGeneral" action="/reportes/generarNovedades">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="empresa" class="control-label">Empresa</label>
                            <select class="form-control" id="empresa" name="empresa">
                                <option value=""></option>        
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}">{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>    
                    <div class="col-3">
                        <div class="form-group">
                            <label for="nomina" class="control-label">N&oacute;mina:</label>
                            <select class="form-control" id="nomina" name="nomina">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>                
                    <div class="col-2">
                        <div class="form-group">
                            <label for="fechaIni" class="control-label">Fecha Inicio:</label>
                            <input type="date" class="form-control" id="fechaIni" name="fechaIni" />
                        </div>
                    </div>   
                    <div class="col-2">
                        <div class="form-group">
                            <label for="fechaFin" class="control-label">Fecha Fin:</label>
                            <input type="date" class="form-control" id="fechaFin" name="fechaFin" />
                        </div>
                    </div>   
                    <div class="col-2">
                        <div class="text-center"><input type="submit" value="Generar" class="btnSubmitGen" /></div>
                    </div>
                </div>    

            </form>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/reportes/novedades.js')}}"></script>
@endsection