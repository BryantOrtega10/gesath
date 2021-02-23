@extends('layouts.admin')
@section('title', 'Ver solicitud de liquidación')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="cajaGeneral text-left verSolicitud">
    <h1 class="granAzul">Ver solicitud de liquidaci&oacute;n</h1>
    <div class="row">
        <div class="col-3">
            <div class="form-group hasText">
                <label for="fechaLiquida" class="control-label">Fecha Pago:</label>
                <input type="text" class="form-control" id="fechaLiquida" name="fechaLiquida" value="{{$liquidaciones->fechaLiquida}}" readonly/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group hasText">
                <label for="tipoLiquidacion" class="control-label">Tipo Liquidaci&oacute;n:</label>
                <input type="text" class="form-control" id="tipoLiquidacion" name="tipoLiquidacion" readonly value="{{$liquidaciones->tipoLiquidacion}}"/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group hasText">
                <label for="estado" class="control-label">Estado:</label>
                <input type="text" class="form-control" id="estado" name="estado" readonly value="{{$liquidaciones->estado}}"/>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group hasText">
                <label for="razonSocial" class="control-label">Empresa:</label>
                <input type="text" class="form-control" id="razonSocial" name="razonSocial" readonly value="{{$liquidaciones->razonSocial}}"/>
            </div>
        </div>
    </div>
    
    
    <div class="alert alert-danger print-error-msg-Liquida" style="display:none">
        <ul></ul>
    </div>

    <div class="row">
        
        <div class="col-3 text-center">
            <a href="/nomina/reversar/{{$liquidaciones->idLiquidacionNomina}}" class="btnSubmitGen">Reversar nomina</a><br>
        </div>
        <div class="col-3 text-center">
            <a href="/nomina/documentoRetencion/{{$liquidaciones->idLiquidacionNomina}}" class="btnSubmitGen">ReteFuente</a><br>
        </div>
        <div class="col-3">
            @if ($liquidaciones->fkTipoLiquidacion == 8)
                <form action="/nomina/cancelarSolicitud" method="POST" class="formGeneral" id="formModificarSolicitud2" autocomplete="off">
                    @csrf
                    <div class="text-center"><input type="submit" value="Cancelar Liquidación" class="btnSubmitGen" /></div>
                    <input type="hidden" name="idLiquidacion" value="{{$liquidaciones->idLiquidacionNomina}}" />
                </form>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-3 text-center"><br>
            <a href="/reportes/documentoNominaHorizontal/{{$liquidaciones->idLiquidacionNomina}}" class="btnSubmitGen btnAzulGen"><i class="fas fa-download"></i> Nomina horizontal</a><br>
        </div>
        <div class="col-3 text-center"><br>
            <a href="/reportes/comprobantePdfConsolidado/{{$liquidaciones->idLiquidacionNomina}}" class="btnSubmitGen btnAzulGen"><i class="fas fa-download"></i> Comprobantes PDF Consolidado</a><br>
        </div>
        <div class="col-3 text-center"><br>
            <a href="/reportes/comprobantePdfNuevoD/{{$liquidaciones->idLiquidacionNomina}}" class="btnSubmitGen btnAzulGen"><i class="fas fa-download"></i> Reporte Pdf </a><br>
        </div>
        <div class="col-3 text-center"><br>
            <a href="/nomina/envioCorreos/{{$liquidaciones->idLiquidacionNomina}}" class="btnSubmitGen btnAzulGen"><i class="fas fa-envelope"></i>Enviar comprobantes por email</a><br>
        </div>
    </div>  
    <br>
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
    <table class="table ">
        <tr>
            <th>Identificación</th>
            <th>Tipo Identificiacion</th>
            <th>Nombre</th>
            <th>Neto a pagar</th>
            <th>Acciones</th>
        </tr>

        @php 
            $totalNetos = 0;
            $totalPersonas = 0;
        @endphp
    
        @foreach ($bouchers as $boucher)
            <tr class="boucher" data-id="{{$boucher->idBoucherPago}}">
                <td>{{$boucher->numeroIdentificacion}}</td>
                <td>{{$boucher->nombre}}</td>
                <td>{{$boucher->primerApellido." ".$boucher->segundoApellido." ".$boucher->primerNombre." ".$boucher->segundoNombre}}</td>
                <td>$<span class="netoPagar" data-id="{{$boucher->idBoucherPago}}">{{number_format($boucher->netoPagar,0, ",", ".")}}</span>
                @php 
                $totalNetos = $totalNetos + $boucher->netoPagar;
                $totalPersonas++;
                @endphp
                </td>
                <td>
                    <a href="#" class="verDetalle verDetalleCompro" data-id="{{$boucher->idBoucherPago}}"><i class="fas fa-eye"></i></a><br>
                    <div class="btn-group">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="/reportes/comprobantePdf/{{$boucher->idBoucherPago}}" target="_blank" class="dropdown-item">Comprobante de pago</a>
                            <a href="/nomina/envioCorreos/enviarComprobante/{{$boucher->idBoucherPago}}" class="enviarCorreo dropdown-item">Enviar por correo</a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <div class="detalleBoucher" data-id="{{$boucher->idBoucherPago}}"></div>
                </td>
            </tr>
        @endforeach
            <tr>
                <td></td>
                <th></th>
                <th>Total </th>
                <th>$<span id="totalNomina">{{number_format($totalNetos,0, ",", ".")}}</span></th>
            </tr>
    </table>
    <h3>Total empleados: <b>{{$totalPersonas}}</b></h3>
</div>
<div class="modal fade" id="comoCalculoModal" tabindex="-1" role="dialog" aria-labelledby="comoCalculoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="resComoCalculoModal"></div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript" src="{{ URL::asset('js/nomina/verSolicitud.js') }}"></script>
@endsection
