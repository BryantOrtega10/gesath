@extends('layouts.admin')
@section('title', 'Subir catalogo contable por archivo plano')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Subir catalogo contable por archivo plano</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="" autocomplete="off" class="formGeneral" action="/catalogo-contable/subirArchivoPlano" enctype="multipart/form-data">
                @csrf
                <label for="archivoCSV">Seleccione el archivo</label> <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
                <div class="text-center"><input type="submit" value="Cargar datos" class="btnSubmitGen" /></div>
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
            @foreach ($cargas as $carga)
                <tr>
                    <td>{{$carga->idCarga}}</td>
                    <td>{{$carga->fechaCarga}}</td>
                    <td>{{ ceil(($carga->numActual / $carga->numRegistros)*100)}}%</td>
                    <td>{{$carga->nombre}}</td>
                    <td><a href="/catalogo-contable/verCarga/{{$carga->idCarga}}">Ver carga</a></td>
                </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection
