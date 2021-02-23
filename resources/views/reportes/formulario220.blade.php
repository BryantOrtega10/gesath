@extends('layouts.admin')
@section('title', 'Reporte Formulario 220')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Reporte Formulario 220</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" autocomplete="off" class="formGeneral" action="/reportes/generarFormulario220">
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
                        <div class="form-group busquedaPop" id="busquedaEmpleado">
                            <label for="nombreEmpleado" class="control-label">Empleado:</label>
                            <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado" />
                            <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaExp" class="control-label">Fecha Expedicion:</label>
                            <input type="date" class="form-control" id="fechaExp" name="fechaExp" />
                        </div>
                    </div>   
                            
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="anio" class="control-label">Año</label>
                            <select class="form-control" id="anio" name="anio">
                                <option value=""></option>        
                                @foreach ($formularios as $formulario)
                                    <option value="{{$formulario->idFormulario220}}">{{$formulario->anio}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>            
                    <div class="col-3">
                        <div class="form-group">
                            <label for="agenteRetenedor" class="control-label">Agente retenedor:</label>
                            <input type="text" class="form-control" id="agenteRetenedor" name="agenteRetenedor" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="reporte" class="control-label">Reporte</label>
                            <select class="form-control" id="reporte" name="reporte">
                                <option value="PDF">PDF</option>        
                                <option value="EXCEL">EXCEL</option>                                
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Generar" class="btnSubmitGen" /></div>
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
<script type="text/javascript" src="{{ URL::asset('js/reportes/formulario220.js')}}"></script>
@endsection