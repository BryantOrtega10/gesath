@extends('layouts.admin')
@section('title', 'Catalogo contable')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="cajaGeneral">
    <h1>Catalogo contable</h1>
    <form autocomplete="off" action="/catalogo-contable/" method="GET"  class="formGeneral" id="filtrar">
        @csrf    
        <div class="row">
            <div class="col-3">
                <div class="form-group @isset($req->descripcion) hasText @endisset">
                    <label for="fechaInicio" class="control-label">Descripcion:</label>
                    <input type="text" name="descripcion" class="form-control" @isset($req->descripcion) value="{{$req->descripcion}}" @endisset/>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group @isset($req->idempresa) hasText @endisset">
                    <label for="idempresa" class="control-label">Empresa:</label>
                    <select class="form-control" name="idempresa" id="idempresa">
                        <option value=""></option>
                        @foreach($empresas as $empresa)
                            <option value="{{$empresa->idempresa}}" @isset($req->idempresa) @if ($req->idempresa == $empresa->idempresa) selected @endif @endisset>{{$empresa->razonSocial}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group hasText">
                    <label for="idcentroCosto" class="control-label">Centro de costo:</label>
                    <select class="form-control" name="idcentroCosto" id="idcentroCosto">
                        <option value="">Todos</option>
                        @foreach($centros_costos as $centro_costo)
                            <option value="{{$centro_costo->idcentroCosto}}" @isset($req->idcentroCosto) @if ($req->idcentroCosto == $centro_costo->idcentroCosto) selected @endif @endisset>{{$centro_costo->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3"><input type="submit" value="Consultar"/><input type="reset" class="recargar" value="" /> </div>
        </div>        
    </form>

    <div class="text-left">
        <a class="btn btn-primary" href="#" id="addCuenta">Agregar cuenta</a>
        <a class="btn btn-primary" href="/catalogo-contable/subirPlano" >Agregar por archivo plano</a>
        <a class="btn btn-primary" href="/catalogo-contable/descargarPlano">Descargar archivo plano</a>

    </div>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <tr>
                <th scope="col">Descripcion</th>
                <th scope="col">Cuenta Debito</th>
                <th scope="col">Cuenta Credito</th>
                <th scope="col">Empresa</th>
                <th scope="col">Centro costo</th>
                <th scope="col"></th>
            </tr>
            
            @foreach ($catalogo as $cata)
            
            
                <tr>
                    <td class="text-left">
                        @if($cata["tablaConsulta"] == 1)
                            Grupo: {{$cata["nombreGrupo"]}}
                        @endif
                        @if ($cata["tablaConsulta"] == "2")
                            Provision: 
                            @if($cata["fkTipoProvision"] == "1")    
                                PRIMA
                            @endif
                            @if($cata["fkTipoProvision"] == "2")    
                                CESANTIAS
                            @endif
                            @if($cata["fkTipoProvision"] == "3")    
                                INTERESES DE CESANTIAS
                            @endif
                            @if($cata["fkTipoProvision"]== "4")    
                                VACACIONES
                            @endif
                        @endif
                        @if ($cata["tablaConsulta"] == "3")
                            Aporte Empleador: 
                            @if($cata["fkTipoAporteEmpleador"] == "1")    
                                PENSION
                            @endif
                            @if($cata["fkTipoAporteEmpleador"] == "2")    
                                SALUD
                            @endif
                            @if($cata["fkTipoAporteEmpleador"] == "3")    
                                ARL
                            @endif
                            @if($cata["fkTipoAporteEmpleador"] == "4")    
                                CCF
                            @endif
                            @if($cata["fkTipoAporteEmpleador"] == "5")    
                                IBCF
                            @endif
                            @if($cata["fkTipoAporteEmpleador"] == "6")    
                                SENA
                            @endif
                        @endif
                        @if ($cata["tablaConsulta"] == "4")
                            Concepto: {{$cata["nombreConcepto"]}}
                        @endif
                    </td>
                    <td>{{$cata["cuentaDebito"]}}</td>
                    <td>{{$cata["cuentaCredito"]}}</td>
                    <td>{{$cata["nombreEmpresa"]}}</td>
                    <td>@if(isset($cata["nombreCC"]))
                        {{$cata["nombreCC"]}}
                    @else
                        TODOS
                    @endif</td>
                    <td>
                        <a href="/catalogo-contable/getForm/edit/{{ $cata["id"] }}" class="editar"><i class="fas fa-edit"></i></a>
                        <a href="/catalogo-contable/eliminar/{{ $cata["id"] }}" class="eliminar"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>

            
            @endforeach
        </table>
    </div>
    {{ $catalogo->appends($arrConsulta)->links() }}
</div>
<div class="modal fade" id="catalogoModal" tabindex="-1" role="dialog" aria-labelledby="catalogoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/catalogo.js') }}"></script>
@endsection