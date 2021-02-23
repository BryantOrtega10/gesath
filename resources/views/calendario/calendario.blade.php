@extends('layouts.admin')
@section('title', 'Calendarios')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<h1 class="granAzul">Calendario festivos</h1>
<a class="btn btn-primary" href="#" id="addCalendario">Agregar calendario</a>	<div class="row">
    <div class="col">
        <a class="btn btn-primary" href="#" id="verCalendario">Ver fechas</a>
    </div>
    <div class="col">
        <a class="btn btn-primary" href="#" id="editCalendario">Editar fechas</a>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover table-striped" id = "calendarios">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Fecha</th>
                <th scope="col">Fecha Inicio Semana</th>
                <th scope="col">Fecha Fin Semana</th>
                {{-- <th scope="col"></th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($calendarios as $calendario)
            <tr>
                <th scope="row">{{ $calendario->idCalendario }}</th>
                <td>{{ $calendario->fecha }}</td>
                <td>{{ $calendario->fechaInicioSemana }}</td>
                <td>{{ $calendario->fechaFinSemana }}</td>
                {{-- <td>
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a dataId ="{{ $calendario->idCalendario }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver detalle</a>
                            <a dataId ="{{ $calendario->idCalendario }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar</a>
                            <a dataId ="{{ $calendario->idCalendario }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar</a>
                        </div>
                    </div>
                </td> --}}
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="calendariosModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='calendarios'></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/calendarios.js') }}"></script>
@endsection
