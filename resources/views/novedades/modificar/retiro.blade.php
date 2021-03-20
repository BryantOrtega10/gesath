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
            <h2>Retiro</h2>
            <hr />
        </div>
        <form action="/novedades/modificarNovedadRetiro" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
            @csrf
            <input type="hidden" name="fkTipoNovedad" value="{{$novedad->fkTipoNovedad }}" />
            <input type="hidden" name="idNovedad" value="{{$novedad->idNovedad }}" />
            <input type="hidden" name="idRetiro" value="{{$novedad->fkRetiro}}" />
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
                        <label for="tipo_novedad" class="control-label">Tipo novedad:</label>
                        <input type="text" id="tipo_novedad" value="{{$novedad->tipoNovedadNombre}}" readonly/>
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
                        <label for="fechaRetiro" class="control-label">Fecha Retiro:</label>
                        <input type="date" class="form-control" id="fechaRetiro" name="fechaRetiro" value="{{$retiro->fecha}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="fechaRetiroReal" class="control-label">Fecha Real:</label>
                        <input type="date" class="form-control" id="fechaRetiroReal" name="fechaRetiroReal" value="{{$retiro->fechaReal}}" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="motivoRetiro" class="control-label">Motivo Retiro:</label>
                        <select class="form-control" id="motivoRetiro" name="motivoRetiro">
                            <option value=""></option>
                            @foreach ($motivosRetiro as $motivoRetiro)
                                <option value="{{$motivoRetiro->idMotivoRetiro}}" @if($motivoRetiro->idMotivoRetiro == $retiro->fkMotivoRetiro) selected @endif>{{$motivoRetiro->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group hasText">
                        <label for="indemnizacion" class="control-label">Indemnizacion:</label>
                        <select class="form-control" id="indemnizacion" name="indemnizacion">
                            <option value=""></option>
                            <option value="1" @if($retiro->indemnizacion == "1") selected @endif> SI</option>
                            <option value="0" @if($retiro->indemnizacion == "0") selected @endif>NO</option>                   
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