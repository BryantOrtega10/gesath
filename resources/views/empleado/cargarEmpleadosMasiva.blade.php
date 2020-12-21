@extends('layouts.admin')
@section('title', 'Carga masiva Empleados')
@section('menuLateral')
    @include('layouts.partials.menu')
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
            <form method="POST" id="" autocomplete="off" class="formGeneral" action="/empleado/cargaMasivaEmpleados" enctype="multipart/form-data">
                @csrf
                <label for="archivoCSV">Seleccione el archivo</label> <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
                <div class="text-center"><input type="submit" value="Cargar Empleados" class="btnSubmitGen" /></div>
            </form>
        </div>
    </div>
    <div class="col-12">
        <table class="table table-hover table-striped ">
            <tr>
                <th># Carga</th>
                <th>Fecha Carga</th>
                <th>Porcentaje</th>
                <th>Estado</th>
                <th></th>
            </tr>
            @foreach ($cargaEmpleados as $cargaEmpleado)
                <tr>
                    <td>{{$cargaEmpleado->idCargaEmpleado}}</td>
                    <td>{{$cargaEmpleado->fechaCarga}}</td>
                    <td>{{ ceil(($cargaEmpleado->numActual / $cargaEmpleado->numRegistros)*100)}}%</td>
                    <td>{{$cargaEmpleado->nombre}}</td>
                    <td><a href="/empleado/cargaEmpleados/{{$cargaEmpleado->idCargaEmpleado}}">Ver carga</a></td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/empleado/empleado.js') }}"></script>
@endsection
