@extends('layouts.admin')
@section('title', 'Carga masiva empleado')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Carga masiva Empleados</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <input type="hidden" value="{{$cargaEmpleado->idCargaEmpleado}}" id="idCargaEmpleado" />
            <input type="hidden" value="@if ($cargaEmpleado->fkEstado == "3") 1 @else 0 @endif" id="realizarConsulta" />
            <div class="row">
                <div class="col-3">
                    <b># Carga: </b>
                    <span>{{$cargaEmpleado->idCargaEmpleado}}</span>
                </div>
                <div class="col-3">
                    <b>Fecha Carga: </b>
                    <span>{{$cargaEmpleado->fechaCarga}}</span>
                </div>
                <div class="col-3">
                    <b>Estado: </b>
                    <span>{{$cargaEmpleado->nombre}}</span>
                </div>
            </div>
            <br>
            <div class="progress" style="height: 40px;">
                <div class="progress-bar" role="progressbar" style="width: {{ ceil(($cargaEmpleado->numActual / $cargaEmpleado->numRegistros)*100)}}%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">{{ ceil(($cargaEmpleado->numActual / $cargaEmpleado->numRegistros)*100)}}%</div>
            </div>
            <br>
            <table class="table table-hover table-striped">
                <tr>

                    <th>#</th>
                    <th>Num. Documento</th>
                    <th>Empleado</th>
                    <th>Estado Subida</th>
                </tr>
                <tbody id="empleadosCargados">
                    @foreach ($cargaEmpleado_Empleados as $index => $cargaEmpleado_Empleado)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>{{$cargaEmpleado_Empleado->tipoDocumento}} - {{$cargaEmpleado_Empleado->numeroIdentificacion}}</td>
                            <td>{{$cargaEmpleado_Empleado->primerApellido}} {{$cargaEmpleado_Empleado->segundoApellido}} {{$cargaEmpleado_Empleado->primerNombre}} {{$cargaEmpleado_Empleado->segundoNombre}}</td>
                            <td>{{$cargaEmpleado_Empleado->estado}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>



        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/empleado/cargaMasivaEmpleados.js') }}"></script>
@endsection