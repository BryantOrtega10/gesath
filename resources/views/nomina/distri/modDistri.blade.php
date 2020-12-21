@extends('layouts.admin')
@section('title', 'Modificar distibucion centro de costos')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Modificar distibucion centro de costos</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formModDistri" autocomplete="off" class="formGeneral formGen" action="/nomina/distri/modificarDistribucion">
                <input type="hidden" class="form-control" name="id_distri_centro_costo" value="{{$distri->id_distri_centro_costo}}" />
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="fkNomina" class="control-label">Nomina *</label>
                            <input type="text" class="form-control" id="fkNomina" name="fkNomina" readonly value="{{$distri->nombre}}" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="fechaInicio" class="control-label">Fecha inicio *</label>
                            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" value="{{$distri->fechaInicio}}" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="fechaFin" class="control-label">Fecha fin *</label>
                            <input type="date" class="form-control" id="fechaFin" name="fechaFin" value="{{$distri->fechaFin}}"/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Modificar" class="btnSubmitGen" /></div>
                    </div>
                </div>
            </form>
            <table class="table table-hover table-striped">
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    @foreach ($centrosCostoGen as $centroCostoG)
                        <th>
                            {{$centroCostoG->nombre}}
                            <br>
                            {{$centroCostoG->id_uni_centro}}
                        </th>
                    @endforeach
                    <th>Total</th>
                    <th></th>
                </tr>
                @foreach ($empleados as $empleado)
                    <tr>
                        <th>
                            {{$empleado->tipoDocumento }}
                        </th>
                        <th>
                            {{$empleado->numeroIdentificacion }}
                        </th>
                        <td class="text-left">
                            {{$empleado->primerApellido}} {{$empleado->segundoApellido}} {{$empleado->primerNombre}} {{$empleado->segundoNombre}} 
                        </td>
                        
                        @php
                            $total = 0;
                        @endphp

                        @foreach ($centrosCostoGen as $centroCostoG)
                            @php
                                $valid = 0;
                            @endphp
                            @foreach ($arrEmpleadoCC[$empleado->idempleado] as $arrCC)
                                @if ($arrCC["centroCosto"] == $centroCostoG->idcentroCosto)
                                    <td>{{$arrCC["porcentaje"]}} %</td>
                                    @php
                                        $valid = 1;
                                        $total = $total + $arrCC["porcentaje"];
                                    @endphp
                                @endif
                            @endforeach
                            @if ($valid == 0)
                                <td>0%</td>
                            @endif
                        @endforeach
                        <td>{{$total}}%</td>
                        <td>
                            <a href="/nomina/distri/editarDistriEm/{{$empleado->idempleado}}/{{$distri->id_distri_centro_costo}}" class="editarDistriEm">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        
    </div>
</div>
<div class="modal fade" id="distriModal" tabindex="-1" role="dialog" aria-labelledby="distriModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/distri.js')}}"></script>
@endsection