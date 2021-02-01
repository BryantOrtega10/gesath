@extends('layouts.admin')
@section('title', 'Agregar Embargo')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-10">
        <h1>Datos Prestamo</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        
        <form method="POST" autocomplete="off" class="formGeneral formGen" action="/prestamos/crearEmbargo">
            @csrf
            <div class="cajaGeneral">
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="infoEmpresa" class="control-label">Empresa</label>
                            <select class="form-control" id="infoEmpresa" required name="empresa">
                                <option value=""></option>        
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}">{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="nomina" class="control-label">N&oacute;mina:</label>
                            <select class="form-control" id="infoNomina" required name="infoNomina">
                                <option value=""></option>
                            </select>
                        </div>               
                    </div>
                    <div class="col-3">
                        <div class="form-group busquedaPop" id="busquedaEmpleado">
                            <label for="nombreEmpleado" class="control-label">Empleado:</label>
                            <input type="text" readonly class="form-control" required id="nombreEmpleado" name="nombreEmpleado" />
                            <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="periocidad" class="control-label">Periocidad:</label>
                            <select class="form-control" id="periocidad" required name="periocidad">
                                <option value=""></option>
                            </select>
                        </div>               
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="montoInicial" class="control-label">Monto Inicial:</label>
                            <input type="text" class="form-control separadorMiles" required id="montoInicial" name="montoInicial" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="saldoActual" class="control-label">Saldo actual:</label>
                            <input type="text" class="form-control separadorMiles" required id="saldoActual" name="saldoActual" value="0"/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaInicio" class="control-label">Fecha inicio descuento:</label>
                            <input type="date" class="form-control" required id="fechaInicio" name="fechaInicio" />
                        </div>
                    </div>
                   
                </div>
                <div class="row">                    
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="tipoDesc" class="control-label">Tipo de descuento:</label>
                            <select class="form-control" id="tipoDesc" required name="tipoDesc">
                                <option value="2">Valor Fijo</option>
                                <option value="3">Porcentaje</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group presValor activo">
                            <label for="valorFijo" class="control-label">Valor Fijo:</label>
                            <input type="text" class="form-control separadorMiles" id="valorFijo" name="valorFijo" />
                        </div>
                        <div class="form-group presPorcentaje">
                            <label for="presPorcentaje" class="control-label">Porcentaje:</label>
                            <input type="text" class="form-control" id="presPorcentaje" name="presPorcentaje" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group presPorcentaje">
                            <label for="grupoConceptoPorcentaje" class="control-label">Grupo Concepto Porcentaje:</label>
                            <select class="form-control" id="grupoConceptoPorcentaje" name="grupoConceptoPorcentaje">
                                <option value=""></option>        
                                @foreach ($gruposConcepto as $grupoConcepto)
                                    <option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText presPorcentaje">
                            <label for="hastaSalarioMinimo" class="control-label">Hasta salario minimo:</label>
                            <select class="form-control" id="hastaSalarioMinimo" name="hastaSalarioMinimo">
                                <option value="1">SI</option>
                                <option value="0" selected>NO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="claseCuota" class="control-label">Clase cuota:</label>
                            <select class="form-control" id="claseCuota" required name="claseCuota">
                                <option value=""></option>
                                @foreach ($conceptos as $concepto)
                                    <option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="pignoracion" class="control-label">Pignoracion:</label>
                            <select class="form-control" id="pignoracion" name="pignoracion">
                                <option value="1">SI</option>
                                <option value="0" selected>NO</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>    
            <br>
            <h1>Datos Embargo</h1>
            <div class="cajaGeneral">
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="numeroEmbargo" class="control-label">Número Embargo:</label>
                            <input type="text" class="form-control" id="numeroEmbargo" name="numeroEmbargo" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="numeroOficio" class="control-label">Número Oficio:</label>
                            <input type="text" class="form-control" id="numeroOficio" name="numeroOficio" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="numeroProceso" class="control-label">Número proceso:</label>
                            <input type="text" class="form-control" id="numeroProceso" name="numeroProceso" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaCargaOficio" class="control-label">Fecha Carga Oficio:</label>
                            <input type="date" class="form-control" id="fechaCargaOficio" name="fechaCargaOficio" />
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaRecepcionCarta" class="control-label">Fecha Recepción Carta:</label>
                            <input type="date" class="form-control" id="fechaRecepcionCarta" name="fechaRecepcionCarta" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="depto" class="control-label">Departamento</label>
                            <select class="form-control" id="depto" name="depto">
                                <option value=""></option>      
                                @foreach ($deptos as $depto)
                                    <option value="{{$depto->idubicacion}}">{{$depto->nombre}}</option>
                                @endforeach                          
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="ciudad" class="control-label">Ciudad</label>
                            <select class="form-control" id="ciudad" name="ciudad">
                                <option value=""></option>                                
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fkTerceroJuzgado" class="control-label">Juzgado</label>
                            <select class="form-control" id="fkTerceroJuzgado" name="fkTerceroJuzgado">
                                <option value=""></option>          
                                @foreach ($tercerosJuzgado as $terceroJuzgado)
                                    <option value="{{$terceroJuzgado->idTercero}}">{{$terceroJuzgado->razonSocial}}</option>
                                @endforeach                      
                            </select>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fkTerceroDemandante" class="control-label">Demandante</label>
                            <select class="form-control" id="fkTerceroDemandante" name="fkTerceroDemandante">
                                <option value=""></option>          
                                @foreach ($tercerosDemandante as $terceroDemandante)
                                    <option value="{{$terceroDemandante->idTercero}}">@if($terceroDemandante->naturalezaTributaria == "Juridica")    
                                        {{$terceroDemandante->razonSocial}}
                                    @else
                                        {{$terceroDemandante->primerApellido}} {{$terceroDemandante->segundoApellido}} {{$terceroDemandante->primerNombre}} {{$terceroDemandante->segundoNombre}}
                                    @endif</option>
                                @endforeach                      
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="numeroCuentaJudicial" class="control-label">Número cuenta judicial:</label>
                            <input type="text" class="form-control" id="numeroCuentaJudicial" name="numeroCuentaJudicial" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="numeroCuentaDemandante" class="control-label">Número cuenta demandante:</label>
                            <input type="text" class="form-control" id="numeroCuentaDemandante" name="numeroCuentaDemandante" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="valorTotalEmbargo" class="control-label">Valor Total Embargo:</label>
                            <input type="text" class="form-control separadorMiles" id="valorTotalEmbargo" readonly name="valorTotalEmbargo" />
                        </div>
                    </div>
                    
                </div>

                <div class="text-center"><input type="submit" value="Agregar Embargo" class="btnSubmitGen" /></div>
            </div>    
        </form>
        
        
    </div>
    <div class="modal fade" id="busquedaEmpleadoModal" tabindex="-1" role="dialog" aria-labelledby="empleadoModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="cerrarPop" data-dismiss="modal"></div>
                    <div class="resFormBusEmpleado"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.numeric.extensions.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/prestamo.js') }}"></script>

@endsection
