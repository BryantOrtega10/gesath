@extends('layouts.admin')
@section('title', 'Seleccionar empleado')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Crear - Consultar - Modificar Empleados</h1>
    </div>
</div>
<div class="row">
    <div class="col-4">
        <div class="cajaGeneral">
            <h2>Filtros de B&uacute;squeda</h2>
            <hr>
            <form autocomplete="off" action="/empleado" method="GET" id="filtrarEmpleado">
                @csrf
                <input type="text" name="nombre" placeholder="Nombre" @isset($req->nombre) value="{{$req->nombre}}" @endisset/>
                <input type="text" name="numDoc" placeholder="N&uacute;mero Documento" @isset($req->numDoc) value="{{$req->numDoc}}" @endisset/>
                <select name="tipoPersona">
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
                </select>
                <select name="centroCosto">
                    <option value="">Centro de costo</option>
                    @foreach ($centrosDeCosto as $centroDeCosto)
                        <option value="{{$centroDeCosto->idcentroCosto}}"  @isset($req->centroCosto) @if ($req->centroCosto == $centroDeCosto->idcentroCosto) selected @endif @endisset>{{$centroDeCosto->nombre}}</option>   
                    @endforeach
                </select>
                <select name="estado">
                    <option value="">Estado</option>
                    <option value="1"  @isset($req->estado) @if ($req->estado == "1") selected @endif @endisset>ACTIVO</option>
                    <option value="2"  @isset($req->estado) @if ($req->estado == "2") selected @endif @endisset>INACTIVO</option>
                    <option value="3"  @isset($req->estado) @if ($req->estado == "3") selected @endif @endisset>EN CREACIÓN</option>
                </select>
                <input type="submit" value="Consultar"/><input type="reset" class="recargar" value="" /> 
            </form>
        </div>
        <div class="cajaGeneral">
            <h2>Acciones</h2>
            <hr>
            <a href="/empleado/formCrear/1" class="btnGeneral">Crear Empleado</a>
            <a href="/empleado/formCrear/2" class="btnGeneral">Crear Contratista</a>
            <a href="/empleado/formCrear/3"  class="btnGeneral">Crear Aspirante</a>
        
        </div>
    </div>
    <div class="col-8">
        <div class="cajaGeneral">
            <h2>Resultado B&uacute;squeda</h2>
            <hr>
            <h3>Empleados, Aspirantes o Contratistas</h3>
            <table class="table table-hover table-striped">
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Numero Documento</th>
                    <th scope="col">Nomina</th>
                    <th scope="col">Estado</th>
                    <th scope="col"></th>
                </tr>
                @foreach ($empleados as $empleado)
                <tr>
                    <th>{{ $empleado->primerNombre." ".$empleado->segundoNombre." ".$empleado->primerApellido." ".$empleado->segundoApellido}}</th>
                    <td>{{ $empleado->numeroIdentificacion }}</td>
                    <td>{{ $empleado->nombreNomina }}</td>
                    <td><div class="estdoEmp{{ $empleado->claseEstado }}">{{ $empleado->estado }}</div></td>
                    <td>
                        <a href="/empleado/formModificar/{{ $empleado->idempleado }}" class="editar"><i class="fas fa-edit"></i></a>
                        <a href="/empleado/formVer/{{ $empleado->idempleado }}" class="ver"><i class="fas fa-eye"></i></a>
                        <a href="/empleado/mostrarPorqueFalla/{{ $empleado->idempleado }}" class="verPorqueFalla"><i class="fas fa-question-circle"></i></a>
                        @if ($empleado->fkEstado == "2")
                            <a href="#" class="reactivarUsuario" data-id="{{ $empleado->idempleado }}"><i class="fas fa-user-plus"></i></a>
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
