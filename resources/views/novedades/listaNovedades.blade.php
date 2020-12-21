@extends('layouts.admin')
@section('title', 'Cargar Novedades')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Lista Novedades</h1>
    <div class="cajaGeneral">
        <form action="/novedades/eliminarSeleccionados" method="POST" class="formGeneral" id="formEliminarNovedades" autocomplete="off">
            @csrf
            <div class="row">
                <div class="col-3 text-left">
                    <input type="submit" value="Eliminar seleccionados" />
                </div>
            </div><br>            
            <table class="table table-hover table-striped">
                <tr>
                    <th></th>
                    <th>#</th>
                    <th>Concepto</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Empresa</th>
                    <th>Nomina</th>
                    <th>Estado</th>
                    <th>Documento</th>
                    <th>Empleado</th>
                    <th></th>
                </tr>
                @foreach ($novedades as $novedad)
                    <tr>
                        <td><input type="checkbox" name="idNovedad[]" value="{{$novedad->idNovedad}}" /></td>
                        <td>{{$novedad->idNovedad}}</td>
                        <td>{{$novedad->nombreConcepto}}</td>
                        <td>
                            @isset($novedad->fkAusencia)
                                Ausencia
                            @endisset
                            @isset($novedad->fkIncapacidad)
                                Incapacidad
                            @endisset
                            @isset($novedad->fkLicencia)
                                Licencia
                            @endisset
                            @isset($novedad->fkHorasExtra)
                                Horas extra
                            @endisset
                            @isset($novedad->fkRetiro)
                                Retiro
                            @endisset
                            @isset($novedad->fkVacaciones)
                                Vacaciones
                            @endisset
                            @isset($novedad->fkOtros)
                                Otros
                            @endisset
                        </td>
                        <td>{{$novedad->fechaRegistro}}</td>
                        <td>{{$novedad->nombreEmpresa}}</td>
                        <td>{{$novedad->nombreNomina}}</td>
                        <td>{{$novedad->nombreEstado}}</td>
                        <td>{{$novedad->tipoDocumento}} - {{$novedad->numeroIdentificacion}}</td>
                        <td>{{$novedad->primerApellido}} {{$novedad->segundoApellido}} {{$novedad->primerNombre}} {{$novedad->segundoNombre}}</td>
                        <td><a href="/novedades/modificarNovedad/{{ $novedad->idNovedad }}" class="editar"><i class="fas fa-edit"></i></a>
                            <a href="#" data-id="{{ $novedad->idNovedad }}" class="eliminar"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </form>
    </div>
    <script type="text/javascript" src="{{ URL::asset('js/novedades/cargarNovedades.js') }}"></script>
    
@endsection