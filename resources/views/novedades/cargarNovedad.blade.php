@extends('layouts.admin')
@section('title', 'Cargar Novedades')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Cargar Novedades</h1>
    <div class="cajaGeneral">
        <form action="/novedades/cargarFormNovedadesxTipo" method="POST" class="formGeneral" id="formCargarNovedades" autocomplete="off">
            @csrf
            
            <input type="hidden" name="idRow" id="idRow" value="0"/>
            <input type="hidden" name="fechaMinima" id="fechaMinima" />
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label for="nomina" class="control-label">N&oacute;mina:</label>
                        <select class="form-control" id="nomina" name="nomina">
                            <option value=""></option>
                            @foreach ($nominas as $nomina)
                                <option value="{{$nomina->idNomina}}">{{$nomina->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="respTipoNomina"></div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="fecha" class="control-label">Fecha novedad:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"/>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="tipo_novedad" class="control-label">Tipo novedad:</label>
                        <select class="form-control" id="tipo_novedad" name="tipo_novedad">
                            <option value=""></option>
                            @foreach ($tipos_novedades as $tipo_novedad)
                                <option value="{{$tipo_novedad->idtipoNovedad}}">{{$tipo_novedad->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3" id="resp_tipoReporte">
                    <div class="form-group">
                        <label for="tipo_reporte" class="control-label">Tipo reporte:</label>
                        <select class="form-control" id="tipo_reporte" name="tipo_reporte">
                            <option value=""></option>
                        </select>
                    </div>

                </div>
            </div>
        </form>
        <div class="row">
            <div class="offset-11 col-1">
                <div class="contMasMenos resetNovedades"><i class="fas fa-redo"></i></div><div class="contMasMenos masNovedades" data-id="0">+</div>
            </div>
        </div>
        <div class="respNovedades">
            
        </div>   
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
    <script type="text/javascript" src="{{ URL::asset('js/novedades/cargarNovedades.js') }}"></script>
    
@endsection