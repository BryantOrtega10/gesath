@extends('layouts.admin')
@section('title', 'Prestamos')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-8">
        <h1>Prestamos</h1>
    </div>
    <div class="col-2 text-right">
        <a href="/prestamos/agregar" class="btn btn-primary" id="addPrestamo">Agregar Prestamo</a>
    </div>
    <div class="col-2 text-right">
        <a href="/prestamos/agregarEmbargo" class="btn btn-primary" id="addEmbargo">Agregar Embargo</a>
    </div>        
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <table class="table table-hover table-striped">
                <tr>
                    <th>#</th>
                    <th>Identificación</th>
                    <th>Nombres</th>
                    <th>Clase cuota</th>
                    <th>Monto Inicial</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
                @foreach ($prestamos as $prestamo)
                <tr>
                    <td>{{$prestamo->idPrestamo}}</td>
                    <td>{{$prestamo->numeroIdentificacion}}</td>
                    <td>{{$prestamo->primerApellido." ".$prestamo->segundoApellido." ".$prestamo->primerNombre." ".$prestamo->segundoNombre}}</td>
                    <td>{{$prestamo->nombreConcepto}}</td>
                    <td>${{number_format($prestamo->montoInicial, 0, ",", ".")}}</td>
                    <td>${{number_format($prestamo->saldoActual, 0, ",", ".")}}</td>
                    <td>{{$prestamo->nombreEstado}}</td>
                    <td>
                        <a class="modificarPrestamo" href="/prestamos/getForm/edit/{{$prestamo->idPrestamo}}"><i class="fas fa-edit"></i></a>
                        <a class="eliminarPrestamo" href="/prestamos/eliminar/{{$prestamo->idPrestamo}}"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="prestamoModal" tabindex="-1" role="dialog" aria-labelledby="prestamoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.numeric.extensions.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/prestamo.js') }}"></script>
@endsection
