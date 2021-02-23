@extends('layouts.admin')
@section('title', 'Carga datos pasados')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Carga datos pasados</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="" autocomplete="off" class="formGeneral" action="/datosPasados/subirArchivo" enctype="multipart/form-data">
                @csrf
                <label for="archivoCSV">Seleccione el archivo</label> <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
                <div class="text-center"><input type="submit" value="Cargar datos pasados" class="btnSubmitGen" /></div>
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
            @foreach ($cargasDatosPasados as $cargaDatoPasado)
                <tr>
                    <td>{{$cargaDatoPasado->idCargaDatosPasados}}</td>
                    <td>{{$cargaDatoPasado->fechaCarga}}</td>
                    <td>{{ ceil(($cargaDatoPasado->numActual / $cargaDatoPasado->numRegistros)*100)}}%</td>
                    <td>{{$cargaDatoPasado->nombre}}</td>
                    <td><a href="/datosPasados/verCarga/{{$cargaDatoPasado->idCargaDatosPasados}}">Ver carga</a></td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
