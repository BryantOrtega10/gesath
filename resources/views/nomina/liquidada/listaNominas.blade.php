@extends('layouts.admin')
@section('title', 'Liquidaciones terminadas')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="cajaGeneral text-left">
    <h1 class="granAzul">Liquidaciones terminadas</h1>
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

            <div class="col-3"  ><input type="submit" value="Consultar"/> <input type="reset" class="recargar" value="" style="margin-left: 5px;"/>  </div>
        </div>        
    </form>
    <div class="table-responsive">
        <table class="table table-hover table-striped" id="solicitudes_tabla">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Fecha Liquida</th>
                    <th scope="col">Empresa</th>
                    <th scope="col">Nómina</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Estado</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($liquidaciones as $liquidacion)
                <tr>
                    <th scope="row">{{ $liquidacion->idLiquidacionNomina }}</th>
                    <td>{{ $liquidacion->fechaLiquida }}</td>
                    <td>{{ $liquidacion->razonSocial }}</td>
                    <td>{{ $liquidacion->nomNombre }}</td>                
                    <td>{{ $liquidacion->tipoLiquidacion }}</td>
                    <td>{{ $liquidacion->estado }}</td>
                    <td>
                        <div class="btn-group">
                            <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                        
                            <div class="dropdown-menu dropdown-menu-right">
                                @if ($liquidacion->fkTipoLiquidacion == 8 && in_array("141",$dataUsu->permisosUsuario))
                                    <a href="/nomina/cancelarSolicitud" class="dropdown-item cancelarSolicitud" data-id="{{$liquidacion->idLiquidacionNomina}}">Cancelar liquidación</a>
                                @endif
                                @if (in_array("70",$dataUsu->permisosUsuario))
                                <a href="/nomina/documentoRetencion/{{$liquidacion->idLiquidacionNomina}}" class="dropdown-item">Documento retencion en la fuente</a>
                                @endif
                                @if (in_array("71",$dataUsu->permisosUsuario))
                                <a href="/nomina/reversar/{{$liquidacion->idLiquidacionNomina}}" class="dropdown-item">Reversar nomina</a>
                                @endif
                                <a href="/nomina/verSolicitudLiquidacionSinEdit/{{$liquidacion->idLiquidacionNomina}}" class="dropdown-item">Ver</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $liquidaciones->appends($arrConsulta)->links() }}
</div>
<script type="text/javascript">
    $(document).ready(function(e){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#solicitudes_tabla").DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json'
            }
        });
        function cargando() {
            if (typeof $("#cargando")[0] !== 'undefined') {
                $("#cargando").css("display", "flex");
            } else {
                $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
            }
        }
        $(".recargar").click(function(){
            window.open("/nomina/nominasLiquidadas","_self");
        });

        $("body").on("click", ".cancelarSolicitud", function(e) {
            e.preventDefault();
            const dataId = $(this).attr("data-id");
            cargando();
            var formdata = new FormData();
            formdata.append('idLiquidacion', dataId);
            $.ajax({
                type: 'POST',
                url: $(this).attr("href"),
                cache: false,
                processData: false,
                contentType: false,
                data: formdata,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    if (data.success) {
                        alert("Liquidación eliminada correctamente");
                        window.location.reload();
                    } else {
                        alert(data.mensaje);
                    }
                },
                error: function(data) {
                    $("#cargando").css("display", "none");
                    retornarAlerta(
                        data.responseJSON.exception,
                        data.responseJSON.message + ", en la linea: " + data.responseJSON.line,
                        'error',
                        'Aceptar'
                    );
                    console.log("error");
                    console.log(data);
                }
            });
        }); 

    });
</script>
@endsection
