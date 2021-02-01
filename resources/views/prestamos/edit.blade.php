@extends('layouts.admin')
@section('title', 'Modificar Prestamo')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-10">
        <h1>Modificar Prestamo</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" autocomplete="off" class="formGeneral formGen" action="/prestamos/modificar">
                <input type="hidden" name="idPrestamo" value="{{$prestamo->idPrestamo}}" />
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="infoEmpresa" class="control-label">Empresa</label>
                            <select class="form-control" id="infoEmpresa" required name="empresa">
                                <option value=""></option>        
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}" @if ($prestamo->fkEmpresa == $empresa->idempresa)
                                        selected
                                    @endif>{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="nomina" class="control-label">N&oacute;mina:</label>
                            <select class="form-control" id="infoNomina" required name="infoNomina">
                                <option value=""></option>
                                @foreach ($nominas as $nomina)
                                    <option value="{{$nomina->idNomina}}" @if ($prestamo->fkNomina == $nomina->idNomina)
                                        selected
                                    @endif>{{$nomina->nombre}}</option>
                                @endforeach
                            </select>
                        </div>               
                    </div>
                    <div class="col-3">
                        <div class="form-group busquedaPop hasText" id="busquedaEmpleado">
                            <label for="nombreEmpleado" class="control-label">Empleado:</label>
                            <input type="text" readonly class="form-control" required id="nombreEmpleado" name="nombreEmpleado"  value="{{$prestamo->primerNombre." ".$prestamo->segundoNombre." ".$prestamo->primerApellido." ".$prestamo->segundoApellido}}"/>
                            <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" value="{{$prestamo->fkEmpleado}}" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="periocidad" class="control-label">Periocidad:</label>
                            <select class="form-control" id="periocidad" required name="periocidad">
                                <option value=""></option>
                                @foreach ($periocidad as $per)
                                    <option value="{{$per->per_id}}" @if ($per->per_id == $prestamo->fkPeriocidad)
                                        selected
                                    @endif>{{$per->per_nombre}}</option>
                                @endforeach
                            </select>
                        </div>               
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="montoInicial" class="control-label">Monto Inicial:</label>
                            <input type="text" class="form-control separadorMiles" required id="montoInicial" name="montoInicial" value="{{$prestamo->montoInicial}}" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="saldoActual" class="control-label">Saldo actual:</label>
                            <input type="text" class="form-control separadorMiles" required id="saldoActual" name="saldoActual"  value="{{$prestamo->saldoActual}}"/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="fechaInicio" class="control-label">Fecha inicio descuento:</label>
                            <input type="date" class="form-control" required id="fechaInicio" name="fechaInicio"   value="{{$prestamo->fechaInicio}}"/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group @if (isset($prestamo->fechaDesembolso)) hasText @endif">
                            <label for="fechaDesembolso" class="control-label">Fecha desembolso:</label>
                            <input type="date" class="form-control" required id="fechaDesembolso" name="fechaDesembolso" value="{{$prestamo->fechaDesembolso}}" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="tipoDesc" class="control-label">Tipo de descuento:</label>
                            <select class="form-control" id="tipoDesc" required name="tipoDesc">
                                <option value="1" @if ($prestamo->tipoDescuento == "1")
                                    selected
                                @endif>Cuotas</option>
                                <option value="2" @if ($prestamo->tipoDescuento == "2")
                                    selected
                                @endif>Valor Fijo</option>
                                <option value="3" @if ($prestamo->tipoDescuento == "3")
                                    selected
                                @endif>Porcentaje</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group presCuotas @if ($prestamo->tipoDescuento == "1") activo @endif  @if (isset($prestamo->numCuotas)) hasText @endif">
                            <label for="cuotas" class="control-label">Cuotas:</label>
                            <input type="text" class="form-control" id="cuotas" name="cuotas" value="{{$prestamo->numCuotas}}" />
                        </div>
                        <div class="form-group presValor @if ($prestamo->tipoDescuento == "2") activo @endif @if (isset($prestamo->valorCuota)) hasText @endif">
                            <label for="valorFijo" class="control-label">Valor Fijo:</label>
                            <input type="text" class="form-control separadorMiles" id="valorFijo" name="valorFijo"  value="{{$prestamo->valorCuota}}"/>
                        </div>
                        <div class="form-group presPorcentaje @if ($prestamo->tipoDescuento == "3") activo @endif @if (isset($prestamo->porcentajeCuota)) hasText @endif">
                            <label for="presPorcentaje" class="control-label">Porcentaje:</label>
                            <input type="text" class="form-control" id="presPorcentaje" name="presPorcentaje" value="{{$prestamo->porcentajeCuota}}" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group presPorcentaje @if ($prestamo->tipoDescuento == "3") activo @endif @if (isset($prestamo->fkGrupoConcepto)) hasText @endif">
                            <label for="grupoConceptoPorcentaje" class="control-label">Grupo Concepto Porcentaje:</label>
                            <select class="form-control" id="grupoConceptoPorcentaje" name="grupoConceptoPorcentaje">
                                <option value=""></option>        
                                @foreach ($gruposConcepto as $grupoConcepto)
                                    <option value="{{$grupoConcepto->idgrupoConcepto}}" @if ($prestamo->fkGrupoConcepto == $grupoConcepto->idgrupoConcepto)
                                        selected
                                    @endif>{{$grupoConcepto->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText presPorcentaje @if ($prestamo->tipoDescuento == "3") activo @endif">
                            <label for="hastaSalarioMinimo" class="control-label">Desde salario minimo:</label>
                            <select class="form-control" id="hastaSalarioMinimo" name="hastaSalarioMinimo">
                                <option value="1" @if ($prestamo->hastaSalarioMinimo == "1") selected @endif>SI</option>
                                <option value="0" @if ($prestamo->hastaSalarioMinimo == "0") selected @endif>NO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-3">
                        <div class="form-group @if (isset($prestamo->codPrestamo)) hasText @endif">
                            <label for="codPrestamo" class="control-label">Codigo Prestamo:</label>
                            <input type="text" class="form-control" id="codPrestamo" name="codPrestamo" value="{{$prestamo->codPrestamo}}" />
                        </div>
                    </div>   
                    <div class="col-3">
                        <div class="form-group @if (isset($prestamo->motivoPrestamo)) hasText @endif">
                            <label for="motivoPrestamo" class="control-label">Motivo Prestamo:</label>
                            <input type="text" class="form-control" id="motivoPrestamo" name="motivoPrestamo"  value="{{$prestamo->motivoPrestamo}}" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group @if (isset($prestamo->fkConcepto)) hasText @endif">
                            <label for="claseCuota" class="control-label">Clase cuota:</label>
                            <select class="form-control" id="claseCuota" required name="claseCuota">
                                <option value=""></option>
                                @foreach ($conceptos as $concepto)
                                    <option value="{{$concepto->idconcepto}}" @if ($concepto->idconcepto == $prestamo->fkConcepto)
                                        selected
                                    @endif>{{$concepto->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="pignoracion" class="control-label">Pignoracion:</label>
                            <select class="form-control" id="pignoracion" name="pignoracion">
                                <option value="1" @if ($prestamo->pignoracion == "1") selected @endif>SI</option>
                                <option value="0" @if ($prestamo->pignoracion == "0") selected @endif>NO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="text-center"><input type="submit" value="Modificar Prestamo" class="btnSubmitGen" /></div>
            </form>
        </div>
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
