@extends('layouts.admin')
@section('title', 'Ver Novedades')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Ver Novedad</h1>
    <div class="cajaGeneral">
        <div class="subTitulo">
            <h2>Incapacidad</h2>
            <hr />
        </div>
        <form action="/" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
            @csrf
            <input type="hidden" name="fkTipoNovedad" value="{{$novedad->fkTipoNovedad }}" />
            <input type="hidden" name="idNovedad" value="{{$novedad->idNovedad }}" />
            <input type="hidden" name="idIncapacidad" value="{{$novedad->fkIncapacidad }}" />
            <input type="hidden" name="fkNomina" value="{{$novedad->fkNomina}}" id="nomina" />
            <div class="row">
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="nomina" class="control-label">N&oacute;mina:</label>
                        <input type="text" id="nomina" value="{{$novedad->nombreNomina}}" readonly/>
                    </div>
                    <div class="respTipoNomina">
                        {{$novedad->periodoNomina." ".$novedad->tipoPeriodoNomina}}
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="fecha" class="control-label">Fecha novedad:</label>
                        <input type="date" class="form-control" id="fecha" readonly name="fecha" value="{{$novedad->fechaRegistro}}"/>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="Mtipo_novedad" class="control-label">Tipo novedad:</label>
                        <input type="text" id="Mtipo_novedad" value="{{$novedad->tipoNovedadNombre}}" readonly/>
                        <input type="hidden" id="tipo_novedad" value="{{$novedad->fkTipoNovedad}}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="nombreEmpleado" class="control-label">Empleado:</label>
                        <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado"
                            value="{{$novedad->primerNombre." ".$novedad->segundoNombre." ".$novedad->primerApellido." ".$novedad->segundoApellido}}" />
                        <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" value="{{$novedad->fkEmpleado}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="concepto" class="control-label">Concepto:</label>
                        <input type="text" id="concepto" value="{{$novedad->nombreConcepto}}" readonly/>
                    </div>
                </div>
                <div class="col-3 ">
                    <div class="form-group hasText">
                        <label for="fechaInicial" class="control-label">Fecha Inicial:</label>
                        <input type="date" class="form-control" id="fechaInicial" name="fechaInicial" readonly value="{{$incapacidad->fechaInicial}}" />
                    </div>
                </div>
                <div class="col-3 ">
                    <div class="form-group hasText">
                        <label for="dias" class="control-label">Dias:</label>
                        <input type="text" class="form-control" id="dias" name="dias" readonly value="{{$incapacidad->numDias}}" />
                    </div>
                </div>
            </div>
            <div class="row">
                
                <div class="col-3 ">
                    <div class="form-group hasText">
                        <label for="fechaFinal" class="control-label">Fecha Final:</label>
                        <input type="date" class="form-control" id="fechaFinal" name="fechaFinal" readonly value="{{$incapacidad->fechaFinal}}"  />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group @isset($incapacidad->fechaRealI) hasText @endisset">
                        <label for="fechaRealI" class="control-label">Fecha Eps Inicio:</label>
                        <input type="date" class="form-control" id="fechaRealI" name="fechaRealI" readonly value="{{$incapacidad->fechaRealI}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group @isset($incapacidad->fechaRealF) hasText @endisset">
                        <label for="fechaRealF" class="control-label">Fecha Eps Fin:</label>
                        <input type="date" class="form-control" id="fechaRealF" name="fechaRealF" readonly value="{{$incapacidad->fechaRealF}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText" >
                        <label for="codigoDiagnostico" class="control-label">C&oacute;digo de diagnostico:</label>
                        <input type="text" readonly class="form-control" id="codigoDiagnostico" name="codigoDiagnostico" value="{{$incapacidad->nmCodDiagnostico}}"/>
                        <input type="hidden" class="form-control" id="idCodigoDiagnostico" name="idCodigoDiagnostico"  value="{{$incapacidad->fkCodDiagnostico}}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group @isset($incapacidad->numIncapacidad) hasText @endisset">
                        <label for="numIncapacidad" class="control-label">N&uacute;mero incapacidad:</label>
                        <input type="text" class="form-control" id="numIncapacidad" name="numIncapacidad" readonly  value="{{$incapacidad->numIncapacidad}}"/>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="pagoTotal" class="control-label">Pago total:</label>
                        <input type="text" id="pagoTotal" value="{{$novedad->nombreConcepto}}" value="@if($incapacidad->pagoTotal == "1") SI @endif @if($incapacidad->pagoTotal == "0") NO @endif" readonly/>
                    </div>
                </div>  
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="tipoAfiliacion" class="control-label">Tipo Entidad:</label>
                        @if(!isset($incapacidad->fkTipoAfilicacion))
                            <input type="text" readonly value="Administradora de Riesgos Profesionales" id="tipoAfiliacion" />
                        @endif
                        @foreach ($tiposAfiliacion as $tipoAfiliacion)
                            @if($tipoAfiliacion->idTipoAfiliacion == $incapacidad->fkTipoAfilicacion) 
                                <input type="text" readonly value="{{$tipoAfiliacion->nombre}}" id="tipoAfiliacion" />
                            @endif
                        @endforeach
                        
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText" >
                        <label for="terceroEntidad" class="control-label">Entidad:</label>
                        <input type="text" readonly class="form-control" id="terceroEntidad" name="terceroEntidad" value="{{$incapacidad->nmTercero}}" />
                        <input type="hidden" class="form-control" id="idTerceroEntidad" name="idTerceroEntidad" value="{{$incapacidad->fkTercero}}"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="naturaleza" class="control-label">Naturaleza:</label>
                        <input type="text" value="{{$incapacidad->naturaleza}}" id="naturaleza" />                        
                    </div>
                </div>
            
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="tipo" class="control-label">Tipo:</label>
                        <input type="text" value="{{$incapacidad->tipoIncapacidad}}" id="tipo" />                        
                    </div>
                </div>
                
            </div>
            <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
                <ul></ul>
            </div>
        </form>
    </div>
    <div class="modal fade" id="errorNominaModal" tabindex="-1" role="dialog" aria-labelledby="errorNominaModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="cerrarPop" data-dismiss="modal"></div>
                    <div id="respError"></div>                
                    <div class="text-center">
                        <a data-dismiss="modal" class="btn btn-secondary" href="#">Aceptar</a>
                    </div>                    
                    
                </div>
            </div>
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
    <div class="modal fade" id="busquedaCodDiagnosticoModal" tabindex="-1" role="dialog" aria-labelledby="ubicacionModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="cerrarPop" data-dismiss="modal"></div>
                    <div class="resFormBusCodDiagnostico"></div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.numeric.extensions.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/novedades/modificarNovedades.js') }}"></script>
    
@endsection