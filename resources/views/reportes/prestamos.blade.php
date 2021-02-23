@extends('layouts.admin')
@section('title', 'Reporte de prestamos')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Reporte de prestamos</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formReporte" action="/reportes/generarReportePrestamo" autocomplete="off" class="formGeneral" >
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
                            <label for="infoNomina" class="control-label">N&oacute;mina:</label>
                            <select class="form-control" id="infoNomina" name="infoNomina">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group busquedaPop" id="busquedaEmpleado">
                            <label for="nombreEmpleado" class="control-label">Empleado:</label>
                            <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado" />
                            <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="tipoReporte" class="control-label">Tipo:</label>
                            <select class="form-control" id="tipoReporte" name="tipoReporte">
                                <option value="Ambos">Ambos</option>
                                <option value="Solo Prestamos">Solo Prestamos</option>
                                <option value="Solo Embargos">Solo Embargos</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="concepto" class="control-label">Concepto:</label>
                            <select class="form-control" id="concepto" name="concepto">
                                <option value=""></option>
                                @foreach ($conceptos as $concepto)
                                    <option value="{{$concepto->idconcepto}}" >{{$concepto->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" id="generarReporteEm" value="Generar" class="btnSubmitGen" /></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="busquedaEmpleadoModal" tabindex="-1" role="dialog" aria-labelledby="empleadoModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="cerrarPop" data-dismiss="modal"></div>
                    <div class="resFormBusEmpleado"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/reportes/prestamos.js')}}"></script>
@endsection