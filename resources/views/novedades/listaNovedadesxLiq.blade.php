@extends('layouts.admin')
@section('title', 'Cargar Novedades')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
    <h1 class="ordenSuperior">Lista Novedades</h1>
    <div class="cajaGeneral">

        <form autocomplete="off" action="{{ Request::url() }}" method="GET" id="filtrarEmpleado" class="formGeneral">
            <div class="row">
                <div class="col-4">
                    <div class="form-group @isset($req->nombre) hasText @endisset">
                        <label for="nombre" class="control-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" @isset($req->nombre) value="{{$req->nombre}}" @endisset/>
                    </div>               
                </div>
                <div class="col-4">
                    <div class="form-group @isset($req->numDoc) hasText @endisset">
                        <label for="numDoc" class="control-label">Número Identificación:</label>
                        <input type="text" class="form-control" id="numDoc" name="numDoc" @isset($req->numDoc) value="{{$req->numDoc}}" @endisset/>
                    </div>               
                </div>
                <div class="col-4">
                    <input type="submit" value="Consultar"/><input type="reset" class="recargar" data-url="{{Request::url()}}" value=""  style="margin-left: 5px;"/> 
                </div>
            </div>
           
        </form>    
        <table class="table table-hover table-striped">
            <tr>
                
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
                                <a href="/novedades/verNovedad/{{ $novedad->idNovedad }}" class="ver dropdown-item"><i class="fas fa-eye"></i> Ver</a>
                                
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    <script type="text/javascript" src="{{ URL::asset('js/novedades/cargarNovedades.js') }}"></script>
@endsection