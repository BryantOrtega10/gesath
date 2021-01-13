@extends('layouts.admin')
@section('title', 'Agregar Solicitud')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Agregar Solicitud</h1>
    <div class="cajaGeneral">
        <form action="/nomina/insertarSolicitud" method="POST" class="formGeneral" id="formAgregarSolicitud" autocomplete="off">
            @csrf
            <div class="alert alert-danger print-error-msg-Liquida" style="display:none">
                <ul></ul>
            </div>
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
                        <label for="tipoliquidacion" class="control-label">Tipo liquidacion:</label>
                        <select class="form-control" id="tipoliquidacion" name="tipoliquidacion">
                            <option value=""></option>
                            @foreach ($tiposliquidaciones as $tipoliquidacion)
                                <option value="{{$tipoliquidacion->idTipoLiquidacion}}">{{$tipoliquidacion->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="fecha" class="control-label">Fecha pago:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"/>
                    </div>
                </div>
                
            </div>
            <div class="fechaProximoPeriodo">
                <input type="hidden" name="periodo" id="periodo" />
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaInicio" class="control-label">Fecha Inicio:</label>
                            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio"/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaFin" class="control-label">Fecha Fin:</label>
                            <input type="date" class="form-control" id="fechaFin" name="fechaFin" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaInicioProx" class="control-label">Fecha Proxima Inicio:</label>
                            <input type="date" class="form-control" id="fechaInicioProx" name="fechaInicioProx"/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaFinProx" class="control-label">Fecha Proxima Fin:</label>
                            <input type="date" class="form-control" id="fechaFinProx" name="fechaFinProx"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="respNomina"></div>
        </form>
    </div>
    
    <script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.numeric.extensions.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/nomina/agregarSolicitud.js') }}"></script>
@endsection