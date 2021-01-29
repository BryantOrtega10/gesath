@extends('layouts.admin')
@section('title', 'Cargar Novedades')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Lista Novedades</h1>
    <div class="cajaGeneral">

        <form autocomplete="off" action="/novedades/listaNovedades/" method="GET" class="formGeneral" id="filtrar">
            @csrf    
            <div class="row">
                <div class="col-2">
                    <div class="form-group @isset($req->fechaInicio) hasText @endisset">
                        <label for="fechaInicio" class="control-label">Fecha inicio:</label>
                        <input type="date" name="fechaInicio" class="form-control" placeholder="Fecha Inicio" @isset($req->fechaInicio) value="{{$req->fechaInicio}}" @endisset/>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group @isset($req->fechaFin) hasText @endisset">
                        <label for="fechaFin" class="control-label">Fecha Fin:</label>
                        <input type="date" name="fechaFin" class="form-control" placeholder="Fecha Fin" @isset($req->fechaFin) value="{{$req->fechaFin}}" @endisset/>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group @isset($req->nomina) hasText @endisset">
                        <label for="fechaInicio" class="control-label">Nomina:</label>
                        <select class="form-control" name="nomina">
                            <option value=""></option>
                            @foreach($nominas as $nomina)
                                <option value="{{$nomina->idNomina}}" @isset($req->nomina) @if ($req->nomina == $nomina->idNomina) selected @endif @endisset>{{$nomina->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group @isset($req->tipoNovedad) hasText @endisset">
                        <label for="tipoNovedad" class="control-label">Tipo:</label>
                        <select class="form-control" name="tipoNovedad" id="tipoNovedad">
                            <option value=""></option>
                            @foreach($tiposnovedades as $tiponovedad)
                                <option value="{{$tiponovedad->idtipoNovedad}}" @isset($req->tipoNovedad) @if ($req->tipoNovedad == $tiponovedad->idtipoNovedad) selected @endif @endisset>{{$tiponovedad->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
    
                <div class="col-3"  ><input type="submit" value="Consultar"/><input type="reset" class="recargar recargarPage" value="" /> </div>
            </div>        
        </form>

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