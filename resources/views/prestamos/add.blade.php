@extends('layouts.admin')
@section('title', 'Agregar Prestamo')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-10">
        <h1 class="granAzul">Agregar Prestamo</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" autocomplete="off" class="formGeneral formGen" action="/prestamos/crear">
                @csrf
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
                        <div class="form-group hasText">
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
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaDesembolso" class="control-label">Fecha desembolso:</label>
                            <input type="date" class="form-control" required id="fechaDesembolso" name="fechaDesembolso" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="tipoDesc" class="control-label">Tipo de descuento:</label>
                            <select class="form-control" id="tipoDesc" required name="tipoDesc">
                                <option value="1">Cuotas</option>
                                <option value="2">Valor Fijo</option>
                                <option value="3">Porcentaje</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group presCuotas activo">
                            <label for="cuotas" class="control-label">Cuotas:</label>
                            <input type="text" class="form-control" id="cuotas" name="cuotas" />
                        </div>
                        <div class="form-group presValor">
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
                            <label for="hastaSalarioMinimo" class="control-label">Desde salario minimo:</label>
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
                            <label for="codPrestamo" class="control-label">Codigo Prestamo:</label>
                            <input type="text" class="form-control" id="codPrestamo" name="codPrestamo" />
                        </div>
                    </div>   
                    <div class="col-3">
                        <div class="form-group">
                            <label for="motivoPrestamo" class="control-label">Motivo Prestamo:</label>
                            <input type="text" class="form-control" id="motivoPrestamo" name="motivoPrestamo" />
                        </div>
                    </div>
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
                <div class="text-center"><input type="submit" value="Agregar Prestamo" class="btnSubmitGen" /></div>
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
