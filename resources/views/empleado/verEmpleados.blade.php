@extends('layouts.admin')
@section('title', 'Seleccionar empleado')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Crear - Consultar - Modificar Empleados</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <div class="row">
                <div class="col-10"><h2>Filtros de B&uacute;squeda</h2></div>
                <div class="col-2">
                    <a href="/empleado/formCrear/1" class="btnGeneral">Crear Empleado</a>
                </div>
            </div>
            <hr>
            <form autocomplete="off" action="/empleado" method="GET" id="filtrarEmpleado" class="formGeneral">
                @csrf
                <div class="row">
                    <div class="col-2">
                        <div class="form-group @isset($req->nombre) hasText @endisset">
                            <label for="nombre" class="control-label">Nombre:</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" @isset($req->nombre) value="{{$req->nombre}}" @endisset/>
                        </div>               
                    </div>
                    <div class="col-2">
                        <div class="form-group @isset($req->numDoc) hasText @endisset">
                            <label for="numDoc" class="control-label">Número Identificación:</label>
                            <input type="text" class="form-control" id="numDoc" name="numDoc" @isset($req->numDoc) value="{{$req->numDoc}}" @endisset/>
                        </div>               
                    </div>
                    <div class="col-2">
                        <div class="form-group @isset($req->empresa) hasText @endisset">
                            <label for="infoEmpresa" class="control-label">Empresa</label>
                            <select class="form-control" id="infoEmpresa" name="empresa">
                                <option value=""></option>        
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}" @isset($req->empresa) @if ($req->empresa == $empresa->idempresa) selected @endif @endisset>{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group @isset($req->centroCosto) hasText @endisset">
                            <label for="centroCosto" class="control-label">Centro de costo:</label>                            
                            <select name="centroCosto" class="form-control" id="centroCosto">
                                <option value=""></option>
                                @foreach ($centrosDeCosto as $centroDeCosto)
                                    <option value="{{$centroDeCosto->idcentroCosto}}"  @isset($req->centroCosto) @if ($req->centroCosto == $centroDeCosto->idcentroCosto) selected @endif @endisset>{{$centroDeCosto->nombre}}</option>   
                                @endforeach
                            </select>
                        </div>               
                    </div>
                    <div class="col-2">
                        <div class="form-group @isset($req->estado) hasText @endisset">
                            <label for="estado" class="control-label">Estado:</label>                            
                            <select name="estado" class="form-control" id="estado">
                                <option value=""></option>
                                @foreach ($estados as $estado)
                                    <option value="{{$estado->idestado}}"  @isset($req->estado) @if ($req->estado == $estado->idestado) selected @endif @endisset>{{$estado->nombre}}</option>   
                                @endforeach
                            </select>
                        </div>               
                    </div>
                    <div class="col-2">
                        <input type="submit" value="Consultar"/><input type="reset" class="recargar" value="" /> 
                    </div>
                </div>
                
                
                
                <!--<select name="tipoPersona">
                    <option value="">Tipo Persona</option>
                    <option value="empleado"  @isset($req->numDoc) @if ($req->numDoc == "empleado") selected @endif @endisset>Empleado</option>
                    <option value="contratista" @isset($req->numDoc) @if ($req->numDoc == "contratista") selected @endif @endisset>Contratista</option>
                    <option value="aspirante"  @isset($req->numDoc) @if ($req->numDoc == "aspirante") selected @endif @endisset>Aspirante</option>
                </select>
                <select name="ciudad">
                    <option value="">Ciudad Donde Labora</option>
                    @foreach ($ciudades as $ciudad)
                        <option value="{{$ciudad->idubicacion}}"  @isset($req->ciudad) @if ($req->ciudad == $ciudad->idubicacion) selected @endif @endisset>{{$ciudad->nombre}}</option>   
                    @endforeach
                </select>-->
                
                
               
            </form>
        </div>

    </div>
    <div class="col-12">
        <div class="cajaGeneral">
            <h2>Resultado B&uacute;squeda</h2>
            <hr>
            <h3>Se encontraron {{$numResultados}} resultados.</h3>
            <table class="table table-hover table-striped">
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Numero Documento</th>
                    <th scope="col">Ciudad</th>
                    <th scope="col">Nomina</th>
                    <th scope="col">Centro costo</th>
                    <th scope="col">Estado</th>
                    <th scope="col"></th>
                </tr>
                @foreach ($empleados as $empleado)
                <tr>
                    <th class="text-left">{{ $empleado->primerApellido." ".$empleado->segundoApellido." ".$empleado->primerNombre." ".$empleado->segundoNombre}}</th>
                    <td>{{ $empleado->numeroIdentificacion }}</td>
                    <td>{{ $empleado->ciudad }}</td>
                    <td>{{ $empleado->nombreNomina }}</td>
                    <td>{{ $empleado->centroCosto }}</td>
                    <td><div class="estdoEmp{{ $empleado->claseEstado }}">{{ $empleado->estado }}</div></td>
                    <td>
                        <a href="/empleado/formModificar/{{ $empleado->idempleado }}" class="editar"><i class="fas fa-edit"></i></a>
                        <a href="/empleado/formVer/{{ $empleado->idempleado }}" class="ver"><i class="fas fa-eye"></i></a>
                        <a href="/empleado/mostrarPorqueFalla/{{ $empleado->idempleado }}" class="verPorqueFalla"><i class="fas fa-question-circle"></i></a>
                        @if ($empleado->fkEstado == "2")
                            <a href="/empleado/formReintegro/{{ $empleado->idempleado }}" data-id="{{ $empleado->idempleado }}"><i class="fas fa-redo"></i></a>
                            <a href="#" class="eliminarDefUsuario" data-id="{{ $empleado->idempleado }}"><i class="fas fa-trash"></i></a>
                        @else
                            <a href="#" class="eliminarUsuario" data-id="{{ $empleado->idempleado }}"><i class="fas fa-user-minus"></i></a>    
                        @endif
                        
                    </td>
                </tr>
                @endforeach
            </table>
            {{ $empleados->appends($arrConsulta)->links() }}
        </div>
    </div>
</div>
<div class="modal fade" id="mostrarPorqueFalla" tabindex="-1" role="dialog" aria-labelledby="mostrarPorqueFalla" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div id="respMensaje"></div>                
                <div class="text-center">
                    <a data-dismiss="modal" class="btn btn-secondary" href="#">Aceptar</a>
                </div>                
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/empleado/empleado.js') }}"></script>
@endsection
