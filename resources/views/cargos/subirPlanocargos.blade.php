@extends('layouts.admin')
@section('title', 'Subir cargos archivo CSV')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Subir cargos archivo CSV</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="" autocomplete="off" class="formGeneral" action="/cargos/subirArchivo" enctype="multipart/form-data">
                @csrf
                <label for="archivoCSV">Seleccione el archivo</label> <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
                <div class="text-center"><input type="submit" value="Subir cargos" class="btnSubmitGen" /></div>
            </form>
        </div>
    </div>
    
</div>
@endsection
