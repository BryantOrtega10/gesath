@extends('layouts.admin')
@section('title', 'Carga masiva novedades')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Carga masiva novedades</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formNovedades" autocomplete="off" class="formGeneral" action="/novedades/cargaMasivaNovedades" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="nomina" class="control-label">N&oacute;mina:</label>
                            <select class="form-control" id="nomina" name="fkNomina">
                                <option value=""></option>
                                @foreach ($nominas as $nomina)
                                    <option value="{{$nomina->idNomina}}">{{$nomina->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="seleccionarArchivo">
                            <label for="archivoCSV">Seleccione un archivo CSV</label>
                            <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
                        </div>
                    </div>
                    <div  class="col-3">
                        <div class="text-center"><input type="submit" value="Cargar novedades" class="btnSubmitGen" /></div>
                    </div>
                </div>
            </form>
            <table class="table table-hover table-striped">
                <tr>
                    <th>#</th>
                    <th>Fecha subida</th>
                    <th>
                </tr>
                @foreach ($cargas as $carga)
                    <tr>
                        <td>{{$carga->idCargaNovedad}}</td>    
                        <td>{{$carga->fechaHora}}</td>    
                        <td><a href="/novedades/verCarga/{{$carga->idCargaNovedad}}">Ver novedades</a></td>
                    </tr> 
                @endforeach

            </table>
        </div>
    </div>
</div>

@endsection