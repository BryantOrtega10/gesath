@extends('layouts.admin')
@section('title', 'Códigos diagnósticos')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-9">
        <h1 class="granAzul">Códigos diagnósticos</h1>
    </div>
    <div class="col-3 text-right">
        <a class="btn btnAzulGen btnGeneral text-center" href="#" id="addCodigo">Agregar código diagnóstico</a> 
    </div>
</div>
<div class="cajaGeneral">
    <div class="table-responsive">
        <table class="table table-hover table-striped" id = "codigos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="codigosModal" tabindex="-1" role="dialog" aria-labelledby="codigosModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='codigos'></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/codigos.js') }}"></script>
@endsection
