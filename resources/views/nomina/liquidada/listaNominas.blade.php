@extends('layouts.admin')
@section('title', 'Liquidaciones terminadas')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="cajaGeneral text-left">
    <h1>Liquidaciones terminadas</h1>
    <form autocomplete="off" action="/nomina/nominasLiquidadas/" method="GET" class="formGeneral" id="filtrar">
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
                <div class="form-group @isset($req->tipoLiquidacion) hasText @endisset">
                    <label for="tipoLiquidacion" class="control-label">Tipo:</label>
                    <select class="form-control" name="tipoLiquidacion">
                        <option value=""></option>
                        @foreach($tipoLiquidaciones as $tipoLiquidacion)
                            <option value="{{$tipoLiquidacion->idTipoLiquidacion}}" @isset($req->tipoLiquidacion) @if ($req->tipoLiquidacion == $tipoLiquidacion->idTipoLiquidacion) selected @endif @endisset>{{$tipoLiquidacion->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-3"  ><input type="submit" value="Consultar"/><input type="reset" class="recargar" value="" /> </div>
        </div>        
    </form>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Fecha Liquida</th>
                <th scope="col">Empresa</th>
                <th scope="col">Nómina</th>
                <th scope="col">Tipo</th>
                <th scope="col">Estado</th>
                <th scope="col"></th>
            </tr>
            @foreach ($liquidaciones as $liquidacion)
            <tr>
                <th scope="row">{{ $liquidacion->idLiquidacionNomina }}</th>
                <td>{{ $liquidacion->fechaLiquida }}</td>
                <td>{{ $liquidacion->razonSocial }}</td>
                <td>{{ $liquidacion->nomNombre }}</td>                
                <td>{{ $liquidacion->tipoLiquidacion }}</td>
                <td>{{ $liquidacion->estado }}</td>
                <td>
                    <a href="/nomina/documentoRetencion/{{$liquidacion->idLiquidacionNomina}}">Documento retencion en la fuente</a><br>
                    <a href="/nomina/reversar/{{$liquidacion->idLiquidacionNomina}}">Reversar nomina</a><br>
                    <a href="/nomina/verSolicitudLiquidacionSinEdit/{{$liquidacion->idLiquidacionNomina}}">Ver</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    {{ $liquidaciones->appends($arrConsulta)->links() }}
</div>
<script type="text/javascript">
    $(document).ready(function(e){
        $(".recargar").click(function(){
            window.open("/nomina/solicitudLiquidacion","_self");
        });
    });
</script>
@endsection
