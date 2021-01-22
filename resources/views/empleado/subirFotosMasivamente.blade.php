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
        <h1>Subir fotos empleados masivamente</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="" autocomplete="off" class="formGeneral" action="/empleado/cargaMasivaFotosEmpleados" enctype="multipart/form-data">
                @csrf
                <label for="archivoZip">Seleccione el archivo Zip</label> 
                <input type="file" name="archivoZip" id="archivoZip" required  accept=".zip"/>
                <div class="text-center"><input type="submit" value="Cargar Fotos Empleados" class="btnSubmitGen" /></div>
            </form>
        </div>
    </div>
   
</div>
<script type="text/javascript" src="{{ URL::asset('js/empleado/empleado.js') }}"></script>
@endsection
