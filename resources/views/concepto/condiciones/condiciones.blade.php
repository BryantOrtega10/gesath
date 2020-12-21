@extends('layouts.admin')
@section('title', 'Condiciones')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<h1>Condiciones</h1>
    <a class="btn btn-primary" href="#" id="addCondicion">Agregar condicion</a>
    <input type="hidden" id="idConc" value="{{$idConcepto}}" />
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Descripcion</th>
                <th scope="col">Tipo Condicion</th>
                <th scope="col">Tipo Resultado</th>
                <th scope="col">Mensaje mostrar</th>
                <th scope="col"></th>
            </tr>
            @foreach ($condiciones as $condicion)
            <tr>
                <th scope="row">{{ $condicion->idcondicion }}</th>
                <td>{{ $condicion->descripcion }}</td>
                <td>{{ $condicion->tipoCondicion }}</td>
                <td>{{ $condicion->tipoResultado }}</td>
                <td>{{ $condicion->mensajeMostrar }}</td>
                <td></td>
            </tr>
            @endforeach
        </table>
    </div>
    <div class="modal fade" id="condicionModal" tabindex="-1" role="dialog" aria-labelledby="condicionModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="cerrarPop" data-dismiss="modal"></div>
                    <div class="respForm" data-para='condicion'></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="itemCondicionModal" tabindex="-1" role="dialog" aria-labelledby="itemCondicionModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="cerrarPop" data-dismiss="modal"></div>
                    <div class="respForm" data-para='itemCondicion'></div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('js/condicion.js') }}"></script>
@endsection