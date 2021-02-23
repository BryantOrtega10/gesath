@extends('layouts.admin')
@section('title', 'Subir fotos empleados masivamente')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Subir fotos empleados masivamente</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="" autocomplete="off" class="formGeneral" action="/empleado/cargaMasivaFotosEmpleados" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="seleccionarArchivo">
                            <label for="archivoZip">Seleccione un archivo Zip</label>
                            <input type="file" name="archivoZip" id="archivoZip" required  accept=".zip"/>
                        </div>
                    </div>    
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Cargar Fotos Empleados" class="btnSubmitGen secundario" /></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
   
</div>
<script type="text/javascript" src="{{ URL::asset('js/empleado/empleado.js') }}"></script>
@endsection
