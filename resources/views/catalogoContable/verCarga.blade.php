@extends('layouts.admin')
@section('title', 'Carga catalogo contable')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Carga catalogo contable</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <input type="hidden" value="@if ($cargas->fkEstado == "3") 1 @else 0 @endif" id="realizarConsulta" />
            <div class="row">
                <div class="col-3">
                    <b># Carga: </b>
                    <span>{{$cargas->idCarga}}</span>
                </div>
                <div class="col-6">
                    <b>Fecha Carga: </b>
                    <span>{{$cargas->fechaCarga}}</span>
                </div>
                <div class="col-3">
                    <b>Estado: </b>
                    <span>{{$cargas->nombre}}</span>
                </div>
            </div>
            <form method="POST" autocomplete="off" class="formGeneral" action="/catalogo-contable/eliminarRegistros">
                @csrf
                <input type="hidden" value="{{$cargas->idCarga}}" id="idCarga" name="idCarga" />
            <br>
            <div class="progress" style="height: 40px;">
                <div class="progress-bar" role="progressbar" style="width: {{ ceil(($cargas->numActual / $cargas->numRegistros)*100)}}%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">{{ ceil(($cargas->numActual / $cargas->numRegistros)*100)}}%</div>
            </div>
            <br>
            <div class="row">
                    @if ($cargas->fkEstado == '15')
                        <div class="col-3">        
                            <div class="text-center"><input type="submit" value="Eliminar seleccionados" class="btnSubmitGen" /></div><br>
                        </div>
                        <div class="col-3 text-center">
                            <a href="/catalogo-contable/cancelarCarga/{{$cargas->idCarga}}" class="btnSubmitGen">Cancelar Subida</a><br>
                        </div>
                        <div class="col-3 text-center">
                            <a href="/catalogo-contable/aprobarCarga/{{$cargas->idCarga}}" class="btnSubmitGen">Aprobar Subida</a><br>
                        </div>
                    @endif
                
            </div>
            <table class="table table-hover table-striped">
                <tbody id="datosCargados">
                    @foreach ($datosCuentas as $index => $datoCuenta)
                        <tr>
                            <th>@if($cargas->fkEstado == "15")
                                <input type="checkbox" name="idCartalogoContablePlano[]" value="{{$datoCuenta->idCartalogoContablePlano}}" /> 
                            @endif</th>
                            @if($datoCuenta->tipoRegistro=="1")                            
                                <td>{{($index + 1)}}</td>
                                <td>{{$datoCuenta->cuenta}}</td>
                                <td>{{$datoCuenta->descripcion}}</td>
                                <td>{{$datoCuenta->nombreEmpresa}}</td>
                                <td>{{$datoCuenta->nombreCentroCosto}}</td>
                                <td>{{$datoCuenta->estado}}</td>
                            @else
                                <td>{{($index + 1)}}</td>
                                <td>@if($datoCuenta->tipoConsulta=="4")
                                        @isset($datoCuenta->nombreConcepto) Concepto: {{$datoCuenta->nombreConcepto}} @endisset
                                    @endif
                                </td>
                                <td>@if($datoCuenta->tipoConsulta=="1")
                                        @isset($datoCuenta->nombreGrupoConcepto) Grupo: {{$datoCuenta->nombreGrupoConcepto}} @endisset
                                        @endif
                                </td>
                                <td>
                                    @if($datoCuenta->tipoConsulta == "2")
                                        @if($datoCuenta->fkTipoProvision=="1") PRIMA @endif
                                        @if($datoCuenta->fkTipoProvision=="2") CESANTIAS @endif
                                        @if($datoCuenta->fkTipoProvision=="3") INTERESES DE CESANTIA @endif
                                        @if($datoCuenta->fkTipoProvision=="4") VACACIONES @endif
                                    @endif
                                </td>
                                <td>
                                    @if($datoCuenta->tipoConsulta=="3")
                                        @if($datoCuenta->fkTipoAporteEmpleador=="1") PENSIÓN @endif
                                        @if($datoCuenta->fkTipoAporteEmpleador=="2") SALUD @endif
                                        @if($datoCuenta->fkTipoAporteEmpleador=="3") ARL @endif
                                        @if($datoCuenta->fkTipoAporteEmpleador=="4") CCF @endif
                                        @if($datoCuenta->fkTipoAporteEmpleador=="5") ICBF @endif
                                        @if($datoCuenta->fkTipoAporteEmpleador=="6") SENA @endif
                                    @endif
                                </td>
                                <td>{{$datoCuenta->estado}}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </form>


        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/catalogo-carga.js') }}"></script>
@endsection