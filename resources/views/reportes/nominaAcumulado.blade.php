@extends('layouts.admin')
@section('title', 'Reporte nomina horizontal')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Reporte nomina acumulado</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formCierre" autocomplete="off" class="formGeneral" action="/reportes/documentoNominaFechas">
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
                            <label for="nomina" class="control-label">N&oacute;mina:</label>
                            <select class="form-control" id="infoNomina" name="infoNomina">
                                <option value=""></option>
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
                </div>        
                <div class="row">
                    <div class="col-3">
                        <div class="form-group busquedaPop" id="busquedaEmpleado">
                            <label for="nombreEmpleado" class="control-label">Empleado:</label>
                            <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado" />
                            <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label>Concepto:</label>
                            <div class="contConceptos">
                                @foreach ($conceptos as $concepto)
                                    <div class="row">
                                        <div class="col-2"><input type="checkbox" id="concepto_{{$concepto->idconcepto}}" name="concepto[]" value="{{$concepto->idconcepto}}"/></div>
                                        <div class="col-10 text-left"><label for="concepto_{{$concepto->idconcepto}}">{{$concepto->nombre}}</label></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="tipoReporte" class="control-label">Tipo Reporte</label>
                            <select class="form-control" id="tipoReporte" name="tipoReporte">
                                <option value="Nominas">Nominas</option>        
                                <option value="Mensual">Mensual</option>        
                                
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Generar reporte" class="btnSubmitGen" /></div>
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
<script type="text/javascript" src="{{ URL::asset('js/nomina/nominaAcumulado.js')}}"></script>
@endsection