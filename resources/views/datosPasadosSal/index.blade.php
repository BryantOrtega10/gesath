@extends('layouts.admin')
@section('title', 'Carga saldos')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<form method="POST" id="" autocomplete="off" class="formGeneral" action="/datosPasadosSal/subirArchivo" enctype="multipart/form-data">
                    @csrf
    <div class="row">
        <div class="col-8">
            <h1 class="granAzul">Carga saldos</h1>
        </div>
        <div class="col-2">
            <div class="seleccionarArchivo gris">
                <label for="archivoCSV">Seleccione un archivo CSV</label>
                <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
            </div>
        </div>
        <div class="col-2">
            <div class="text-center"><input type="submit" value="Cargar csv" class="btnSubmitGen btnAzulGen" /></div>
        </div>
    </div>
</form>
<div class="cajaGeneral">
<div class="row">
    <div class="col-12">
        <table class="table table-hover table-striped ">
            <tr>
                <th># Carga</th>
                <th>Fecha Carga</th>
                <th>Porcentaje</th>
                <th>Estado</th>
                <th></th>
            </tr>
            @foreach ($cargasDatosPasados as $cargaDatoPasado)
                <tr>
                    <td>{{$cargaDatoPasado->idCargaDatosPasados}}</td>
                    <td>{{$cargaDatoPasado->fechaCarga}}</td>
                    <td>{{ ceil(($cargaDatoPasado->numActual / $cargaDatoPasado->numRegistros)*100)}}%</td>
                    <td>{{$cargaDatoPasado->nombre}}</td>
                    <td><a href="/datosPasadosSal/verCarga/{{$cargaDatoPasado->idCargaDatosPasados}}">Ver carga</a></td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
</div>

@endsection
