@extends('layouts.admin')
@section('title', 'Envio de correos')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Envio de correos</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" autocomplete="off" class="formGeneral" action="/reportes/generarColaCorreo">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="infoEmpresa" class="control-label">Empresa</label>
                            <select class="form-control" required id="infoEmpresa" name="empresa">
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
                            <select class="form-control" id="infoNomina" name="infoNomina">
                                <option value=""></option>
                            </select>
                        </div>               
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="centroCosto" class="control-label">Centro de costo:</label>
                            <select class="form-control" id="centroCosto" name="centroCosto">
                                <option value=""></option>
                            </select>
                        </div>               
                    </div>
                    <div class="col-3">
                        <div class="form-group busquedaPop" id="busquedaEmpleado">
                            <label for="nombreEmpleado" class="control-label">Empleado:</label>
                            <input type="text" readonly class="form-control" id="nombreEmpleado"  name="nombreEmpleado" />
                            <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" />
                        </div>
                    </div>
                    
                    
                </div>        
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaInicio" class="control-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" required name="fechaInicio" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaFin" class="control-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin" required name="fechaFin" />
                        </div>
                    </div>
                   
                    <div class="col-3">
                        <div class="form-group">
                            <label for="mensaje" class="control-label">Mensaje</label>
                            <select class="form-control" required id="mensaje" name="mensaje">
                                <option value=""></option>
                                @foreach ($mensajes as $mensaje)
                                    <option value="{{$mensaje->idMensaje}}">{{$mensaje->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Generar cola" class="btnSubmitGen" /></div>
                    </div>

                </div>    

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
<script type="text/javascript">
function cargando(){
	if(typeof $("#cargando")[0] !== 'undefined'){
		$("#cargando").css("display", "flex");
	}
	else{
		$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
	}
}
$(document).ready(function(){
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});
    $("body").on("change", "#infoEmpresa", function(e){
        e.preventDefault();
        
        $("#infoNomina").html('<option value=""></option>');
        $("#infoNomina").trigger("change");
        
        $("#centroCosto").html('<option value=""></option>');
        $("#centroCosto").trigger("change");
        
        const idEmpresa = $(this).val();
        if(idEmpresa != ""){
            cargando();
            $.ajax({
                type:'GET',
                url: "/empleado/cargarDatosPorEmpresa/" + idEmpresa,
                success:function(data){
                    $("#cargando").css("display", "none");
                    $("#infoNomina").html(data.opcionesNomina);
                    $("#centroCosto").html(data.opcionesCentroCosto);
                },
                error: function(data){
                    console.log("error");
                    console.log(data);
                }
            });
        }
        
    });
    $("body").on("click", ".recargar", function(){
        cargando();
        $.ajax({
            type:'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#infoNomina").val(),
            success:function(data){
                $("#cargando").css("display", "none");
				$(".resFormBusEmpleado").html(data);
				$('#busquedaEmpleadoModal').modal('show');
            },
            error: function(data){
                console.log("error");
                console.log(data);
            }
        });
    });



    $("body").on("click", "#busquedaEmpleado", function(){
        cargando();
        $.ajax({
            type:'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#infoNomina").val(),
            success:function(data){
                $("#cargando").css("display", "none");
				$(".resFormBusEmpleado").html(data);
				$('#busquedaEmpleadoModal').modal('show');
            },
            error: function(data){
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("submit", "#filtrarEmpleado", function(e){
        e.preventDefault();
        cargando();
        
        var formdata = $('#filtrarEmpleado').serialize();
        $.ajax({
            type: 'GET',
            url: $(this).attr("action"),
            data: formdata,
            success: function(data) {
				$(".resFormBusEmpleado").html(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    
    });
    $("body").on("click", ".resFormBusEmpleado .pagination a", function(e){
        e.preventDefault();
        cargando();
        $.ajax({
            type:'GET',
            url: $(this).attr("href"),
            success:function(data){
                $("#cargando").css("display", "none");
				$(".resFormBusEmpleado").html(data);
            },
            error: function(data){
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("click", ".resFormBusEmpleado a.seleccionarEmpleado", function(e){
        e.preventDefault();
        $("#nombreEmpleado").val($(this).html().trim());
        $("#nombreEmpleado").trigger("change");
        $("#idEmpleado").val($(this).attr("data-id"));

        $('#busquedaEmpleadoModal').modal('hide');

    });

});
</script>
@endsection