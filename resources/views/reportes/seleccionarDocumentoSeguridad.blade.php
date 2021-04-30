@extends('layouts.admin')
@section('title', 'Descargar archivo seguridad social')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Descargar archivo seguridad social</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formDocumentoSS" autocomplete="off" class="formGeneral" action="/reportes/documentoSSTxt">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="infoEmpresa" class="control-label">Empresa</label>
                            <select class="form-control" id="infoEmpresa" name="empresa">
                                <option value=""></option>        
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}">{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>    
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaDocumento" class="control-label">Fecha Informe</label>
                            <input type="date" class="form-control" id="fechaDocumento" name="fechaDocumento" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="button" id="enviarDocumento" value="DESCARGAR" class="btnSubmitGen" /></div>
                    </div>
                </div>                
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="liquidacionPendienteModal" tabindex="-1" role="dialog" aria-labelledby="liquidacionPendiente" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="camposVaciosText">
                    <h4>Aun cuenta con liquidaciones sin terminar, desea continuar?</h4>
                    <div class="text-center">
                        <a href="#" data-accion="" data-form="" id="btnContinuarLiqPen" class="btn btn-secondary">Continuar</a>
                        <a data-dismiss="modal" class="btn btn-primary" href="#">Volver</a>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function cargando() {
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
    }
    $(document).ready(function() {

        $("#enviarDocumento").click(function(){
            cargando();
            const idEmpresa = $("#infoEmpresa").val();
            const fechaDocumento = $("#fechaDocumento").val();
            $.ajax({
                type:'GET',
                url: "/reportes/verificarSiPendientes/" + idEmpresa + "/" + fechaDocumento,
                success:function(data){
                    $("#cargando").css("display", "none");
                    if(data.success){
                        $("#formDocumentoSS").submit();
                    }
                    else{
                        $("#liquidacionPendienteModal").modal("show");
                    }
                    
                },
                error: function(data){
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
        $("#btnContinuarLiqPen").click(function(){
            $("#formDocumentoSS").submit();
        });
        
    });
    
    </script>
@endsection