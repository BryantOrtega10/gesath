@extends('layouts.admin')
@section('title', 'Subir cargos archivo CSV')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
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
                <div class="row">
                    <div class="col-3">
                        <div class="seleccionarArchivo">
                            <label for="archivoCSV">Seleccione un archivo CSV</label>
                            <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Subir cargos" class="btnSubmitGen" /></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
</div>
@endsection
