@extends('layouts.admin')
@section('title', 'Cargar Novedades')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Lista Novedades</h1>
    <div class="cajaGeneral">

        <form autocomplete="off" action="/novedades/listaNovedades/" method="GET" class="formGeneral" id="filtrar">
            
            <div class="row">
                <div class="col-3">
                    <div class="form-group @isset($req->fechaInicio) hasText @endisset">
                        <label for="fechaInicio" class="control-label">Fecha inicio:</label>
                        <input type="date" name="fechaInicio" class="form-control" placeholder="Fecha Inicio" @isset($req->fechaInicio) value="{{$req->fechaInicio}}" @endisset/>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group @isset($req->fechaFin) hasText @endisset">
                        <label for="fechaFin" class="control-label">Fecha Fin:</label>
                        <input type="date" name="fechaFin" class="form-control" placeholder="Fecha Fin" @isset($req->fechaFin) value="{{$req->fechaFin}}" @endisset/>
                    </div>
                </div>
                <div class="col-3">
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
                <div class="col-3">
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
            </div>        
            <div class="row">
                <div class="col-3">
                    <div class="form-group @isset($req->estado) hasText @endisset">
                        <label for="estado" class="control-label">Estado:</label>
                        <select class="form-control" name="estado" id="estado">
                            <option value=""></option>
                            @foreach($estados as $estado)
                                <option value="{{$estado->idestado}}" @isset($req->estado) @if ($req->estado == $estado->idestado) selected @endif @endisset>{{$estado->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group @isset($req->nombre) hasText @endisset">
                        <label for="nombre" class="control-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" @isset($req->nombre) value="{{$req->nombre}}" @endisset/>
                    </div>               
                </div>
                <div class="col-3">
                    <div class="form-group @isset($req->numDoc) hasText @endisset">
                        <label for="numDoc" class="control-label">Número Identificación:</label>
                        <input type="text" class="form-control" id="numDoc" name="numDoc" @isset($req->numDoc) value="{{$req->numDoc}}" @endisset/>
                    </div>               
                </div>
                <div class="col-3"  ><input type="submit" value="Consultar"/> <input type="reset" class="recargar" value="" style="margin-left: 5px;"/>  </div>
            </div>
        </form>

        <form action="/novedades/eliminarSeleccionados" method="POST" class="formGeneral" id="formEliminarNovedades" autocomplete="off">
            @csrf
            <div class="row">
                <div class="col-3 text-left">
                    <input type="submit" class="secundarioVerdadero" value="Eliminar seleccionados" />
                </div>
            </div><br>            
            <table class="table table-hover table-striped">
                <tr>
                    <th></th>
                    <th>#</th>
                    <th>Documento</th>
                    <th>Empleado</th>
                    <th>Empresa</th>
                    <th>Nomina</th>
                    <th>Concepto</th>
                    <th>Tipo</th>
                    <th>Fecha</th>                    
                    <th>Estado</th>
                    
                    <th></th>
                </tr>
                @foreach ($novedades as $novedad)
                    <tr>
                        <td><input type="checkbox" name="idNovedad[]" value="{{$novedad->idNovedad}}" /></td>
                        <td>{{$novedad->idNovedad}}</td>
                        <td>{{$novedad->tipoDocumento}} - {{$novedad->numeroIdentificacion}}</td>
                        <td>{{$novedad->primerApellido}} {{$novedad->segundoApellido}} {{$novedad->primerNombre}} {{$novedad->segundoNombre}}</td>
                        <td>{{$novedad->nombreEmpresa}}</td>
                        <td>{{$novedad->nombreNomina}}</td>
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
                        <td>{{$novedad->nombreEstado}}</td>                        
                        <td>
                            <div class="btn-group">
                                <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                              
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="/novedades/modificarNovedad/{{ $novedad->idNovedad }}" class="editar dropdown-item"><i class="fas fa-edit"></i> Modificar</a>
                                    <a href="/novedades/verNovedad/{{ $novedad->idNovedad }}" class="ver dropdown-item"><i class="fas fa-eye"></i> Ver</a>
                                    <a href="#" data-id="{{ $novedad->idNovedad }}" class="eliminar dropdown-item"><i class="fas fa-trash"></i> Eliminar</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
            {{$novedades->appends($arrConsulta)->links()}}
        </form>
    </div>
    <script type="text/javascript" src="{{ URL::asset('js/novedades/cargarNovedades.js') }}"></script>
@endsection