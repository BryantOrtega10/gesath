@extends('layouts.admin')
@section('title', 'Carga masiva Empleados')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Carga masiva Empleados</h1>
    </div>
</div>
<div class="cajaGeneral">
    <div class="row">
        <div class="col-12">
            
                <form method="POST" id="" autocomplete="off" class="formGeneral" action="/empleado/cargaMasivaEmpleados" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-3">
                            <div class="seleccionarArchivo">
                                <label for="archivoCSV">Seleccione un archivo CSV</label>
                                <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
                            </div>
                        </div>    
                        <div class="col-3">
                            <div class="text-center"><input type="submit" value="Cargar Empleados" class="btnSubmitGen secundario" /></div>
                        </div>
                    </div>
                    
                    
                </form>
            
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
</div>
<script type="text/javascript" src="{{ URL::asset('js/empleado/empleado.js') }}"></script>
@endsection
