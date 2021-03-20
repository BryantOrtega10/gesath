@extends('layouts.admin')
@section('title', 'Modificar Novedades')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Modificar Novedad</h1>
    <div class="cajaGeneral">
        <div class="subTitulo">
            <h2>Incapacidad</h2>
            <hr />
        </div>
        <form action="/novedades/modificarNovedadIncapacidad" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
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
                        <input type="date" class="form-control" id="fecha" name="fecha" value="{{$novedad->fechaRegistro}}"/>
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
                    <div class="form-group busquedaPop hasText" id="busquedaEmpleado">
                        <label for="nombreEmpleado" class="control-label">Empleado:</label>
                        <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado"
                            value="{{$novedad->primerNombre." ".$novedad->segundoNombre." ".$novedad->primerApellido." ".$novedad->segundoApellido}}" />
                        <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" value="{{$novedad->fkEmpleado}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="concepto" class="control-label">Concepto:</label>
                        <select class="form-control" id="concepto" name="concepto">
                            <option value=""></option>
                            @foreach ($conceptos as $concepto)
                                <option value="{{$concepto->idconcepto}}" @if($concepto->idconcepto == $novedad->fkConcepto) selected @endif>{{$concepto->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3 ">
                    <div class="form-group hasText">
                        <label for="fechaInicial" class="control-label">Fecha Inicial:</label>
                        <input type="date" class="form-control" id="fechaInicial" name="fechaInicial" value="{{$incapacidad->fechaInicial}}" />
                    </div>
                </div>
                <div class="col-3 ">
                    <div class="form-group hasText">
                        <label for="dias" class="control-label">Dias:</label>
                        <input type="text" class="form-control" id="dias" name="dias" value="{{$incapacidad->numDias}}" />
                    </div>
                </div>
            </div>
            <div class="row">
                
                <div class="col-3 ">
                    <div class="form-group hasText">
                        <label for="fechaFinal" class="control-label">Fecha Final:</label>
                        <input type="date" class="form-control" id="fechaFinal" name="fechaFinal" value="{{$incapacidad->fechaFinal}}"  />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group @isset($incapacidad->fechaRealI) hasText @endisset">
                        <label for="fechaRealI" class="control-label">Fecha Eps Inicio:</label>
                        <input type="date" class="form-control" id="fechaRealI" name="fechaRealI" value="{{$incapacidad->fechaRealI}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group @isset($incapacidad->fechaRealF) hasText @endisset">
                        <label for="fechaRealF" class="control-label">Fecha Eps Fin:</label>
                        <input type="date" class="form-control" id="fechaRealF" name="fechaRealF" value="{{$incapacidad->fechaRealF}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group busquedaPop hasText"  id="busquedaCodDiagnostico">
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
                        <input type="text" class="form-control" id="numIncapacidad" name="numIncapacidad"  value="{{$incapacidad->numIncapacidad}}"/>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="pagoTotal" class="control-label">Pago total:</label>
                        <select class="form-control" id="pagoTotal" name="pagoTotal">
                            <option value=""></option>
                            <option value="1" @if($incapacidad->pagoTotal == "1") selected @endif>SI</option>
                            <option value="0" @if($incapacidad->pagoTotal == "0") selected @endif>NO</option>
                        </select>
                    </div>
                </div>  
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="tipoAfiliacion" class="control-label">Tipo Entidad:</label>
                        <select class="form-control" id="tipoAfiliacion" name="tipoAfiliacion">
                            <option value=""></option>
                            <option value="-1" @if(!isset($incapacidad->fkTipoAfilicacion)) selected @endif>Administradora de Riesgos Profesionales</option>
                            @foreach ($tiposAfiliacion as $tipoAfiliacion)
                                <option value="{{$tipoAfiliacion->idTipoAfiliacion}}" @if($tipoAfiliacion->idTipoAfiliacion == $incapacidad->fkTipoAfilicacion) selected @endif>{{$tipoAfiliacion->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group busquedaPop hasText"  id="busquedaEntidad">
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
                        <select class="form-control" id="naturaleza" name="naturaleza">
                            <option value=""></option>
                            <option value="Accidente de trabajo" @if($incapacidad->naturaleza == "Accidente de trabajo") selected @endif>Accidente de trabajo</option>
                            <option value="Enfermedad General o Maternidad" @if($incapacidad->naturaleza == "Enfermedad General o Maternidad") selected @endif>Enfermedad General o Maternidad</option>
                            <option value="Enfermedad Profesional" @if($incapacidad->naturaleza == "Enfermedad Profesional") selected @endif>Enfermedad Profesional</option>
                        </select>
                    </div>
                </div>
            
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="tipo" class="control-label">Tipo:</label>
                        <select class="form-control" id="tipo" name="tipo">
                            <option value=""></option>
                            <option value="Ambulatoria" @if($incapacidad->tipoIncapacidad == "Ambulatoria") selected @endif>Ambulatoria</option>
                            <option value="Hospitalaria" @if($incapacidad->tipoIncapacidad == "Hospitalaria") selected @endif>Hospitalaria</option>
                            <option value="Maternidad" @if($incapacidad->tipoIncapacidad == "Maternidad") selected @endif>Maternidad</option>
                            <option value="Paternidad" @if($incapacidad->tipoIncapacidad == "Paternidad") selected @endif>Paternidad</option>
                            <option value="Prorroga" @if($incapacidad->tipoIncapacidad == "Prorroga") selected @endif>Prorroga</option>
                        </select>
                    </div>
                </div>
                
            </div>
            <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
                <ul></ul>
            </div>
            <div class="text-center"><input type="submit" value="MODIFICAR" class="btnSubmitGen" /></div>
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