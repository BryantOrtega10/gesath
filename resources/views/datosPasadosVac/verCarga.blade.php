@extends('layouts.admin')
@section('title', 'Carga datos pasados  VAC/LRN ')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Carga datos pasados  VAC/LRN </h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            
            <input type="hidden" value="@if ($cargaDatoPasado->fkEstado == "3") 1 @else 0 @endif" id="realizarConsulta" />
            <div class="row">
                <div class="col-3">
                    <b># Carga: </b>
                    <span>{{$cargaDatoPasado->idCargaDatosPasados}}</span>
                </div>
                <div class="col-6">
                    <b>Fecha Carga: </b>
                    <span>{{$cargaDatoPasado->fechaCarga}}</span>
                </div>
                <div class="col-3">
                    <b>Estado: </b>
                    <span>{{$cargaDatoPasado->nombre}}</span>
                </div>
            </div>
            <form method="POST" autocomplete="off" class="formGeneral" action="/datosPasadosVac/eliminarRegistros">
                @csrf
                <input type="hidden" value="{{$cargaDatoPasado->idCargaDatosPasados}}" id="idCargaDatosPasados" name="idCargaDatosPasados" />
            <br>
            <div class="progress" style="height: 40px;">
                <div class="progress-bar" role="progressbar" style="width: {{ ceil(($cargaDatoPasado->numActual / $cargaDatoPasado->numRegistros)*100)}}%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">{{ ceil(($cargaDatoPasado->numActual / $cargaDatoPasado->numRegistros)*100)}}%</div>
            </div>
            <br>
            <div class="row">
                
                    @if ($cargaDatoPasado->fkEstado == '15')
                    <div class="col-3">        
                        <div class="text-center"><input type="submit" value="Eliminar seleccionados" class="btnSubmitGen" /></div><br>
                    </div>
                    
                    <div class="col-3 text-center">
                        <a href="/datosPasadosVac/aprobarCarga/{{$cargaDatoPasado->idCargaDatosPasados}}" class="btnSubmitGen">Aprobar Subida</a><br>
                    </div>
                    @endif
                    <div class="col-3 text-center">
                        <a href="/datosPasadosVac/cancelarCarga/{{$cargaDatoPasado->idCargaDatosPasados}}" class="btnSubmitGen">Cancelar Subida</a><br>
                    </div><br>
                
            </div>
            <table class="table table-hover table-striped">
                <tr>
                    <th></th>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Dias</th>
                    <th>Estado Subida</th>
                    <th></th>
                </tr>
                <tbody id="datosCargados">
                    @foreach ($datosPasados as $index => $datoPasado)
                        <tr>
                            <th>@if(isset($datoPasado->primerApellido) && $cargaDatoPasado->fkEstado == "15") 
                                <input type="checkbox" name="idDatosPasados[]" value="{{$datoPasado->idDatosPasados}}" /> 
                            @endif</th>
                            <td>{{$index + 1}}</td>
                            <th>{{$datoPasado->tipo}}</th>
                            <td>{{$datoPasado->numeroIdentificacion}}</td>
                            <td>{{$datoPasado->primerApellido}} {{$datoPasado->segundoApellido}} {{$datoPasado->primerNombre}} {{$datoPasado->segundoNombre}}</td>
                            <td>
                                <span class="mostrarModificacion" data-id="{{$datoPasado->idDatosPasados}}">{{$datoPasado->fecha}}</span>
                                <input type="date" class="ocultoModificacion" value="{{$datoPasado->fecha}}" id="fecha_{{$datoPasado->idDatosPasados}}" data-id="{{$datoPasado->idDatosPasados}}" />
                            </td>
                            <td>
                                <span class="mostrarModificacion" data-id="{{$datoPasado->idDatosPasados}}">{{$datoPasado->fechaInicial}}</span>
                                <input type="date" class="ocultoModificacion" value="{{$datoPasado->fechaInicial}}" id="fechaInicial_{{$datoPasado->idDatosPasados}}" data-id="{{$datoPasado->idDatosPasados}}" />
                            </td>
                            <td>
                                <span class="mostrarModificacion" data-id="{{$datoPasado->idDatosPasados}}">{{$datoPasado->fechaFinal}}</span>
                                <input type="date" class="ocultoModificacion" value="{{$datoPasado->fechaFinal}}" id="fechaFinal_{{$datoPasado->idDatosPasados}}" data-id="{{$datoPasado->idDatosPasados}}" />
                            </td>
                            <td>
                                <span class="mostrarModificacion" data-id="{{$datoPasado->idDatosPasados}}">{{$datoPasado->dias}}</span>
                                <input type="text" class="ocultoModificacion" value="{{$datoPasado->dias}}" id="dias_{{$datoPasado->idDatosPasados}}" data-id="{{$datoPasado->idDatosPasados}}" />
                            </td>
                            <td>{{$datoPasado->estado}}</td>
                            <td>
                                <a href="#" class="modificar mostrarModificacion" data-id="{{$datoPasado->idDatosPasados}}">Modificar</a>

                                <a href="#" class="modificarEnvio btnSubmitGen ocultoModificacion" data-id="{{$datoPasado->idDatosPasados}}">Modificar</a>
                                <br><br>
                                <a href="#" class="cancelar btnSubmitGen ocultoModificacion" data-id="{{$datoPasado->idDatosPasados}}">Cancelar</a>
                            
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </form>
            <form method="POST" autocomplete="off" id="formMod" action="/datosPasadosVac/modificarRegistro">
                @csrf
                <input type="hidden" id="idDatoPasado" name="idDatoPasado" />
                <input type="hidden" id="fecha" name="fecha" />
                <input type="hidden" id="fechaInicial" name="fechaInicial" />
                <input type="hidden" id="fechaFinal" name="fechaFinal" />
                <input type="hidden" id="dias" name="dias" />
            </form>

        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/datosPasadosVac/cargaDatos.js') }}"></script>
@endsection