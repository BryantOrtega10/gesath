@extends('layouts.admin')
@section('title', 'Reporte nomina horizontal')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Reporte nomina horizontal</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formCierre" autocomplete="off" class="formGeneral" action="/reportes/documentoNominaHorizontalFechas">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="empresa" class="control-label">Empresa</label>
                            <select class="form-control" id="empresa" name="empresa">
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
                            <select class="form-control" id="nomina" name="nomina">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="fechaInicio" class="control-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" />
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="fechaFin" class="control-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin" name="fechaFin" />
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="text-center"><input type="submit" value="Generar reporte" class="btnSubmitGen" /></div>
                    </div>
                </div>                
            </form>
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
    $(document).ready(function(e){
        $("body").on("change", "#empresa", function(e) {
            e.preventDefault();
            const idEmpresa = $(this).val();
            $("#nomina").html('<option value=""></option>');
            $("#nomina").trigger("change");
            if (idEmpresa != "") {
                cargando();
                $.ajax({
                    type: 'GET',
                    url: "/empleado/cargarDatosPorEmpresa/" + idEmpresa,
                    success: function(data) {
                        $("#cargando").css("display", "none");
                        $("#nomina").html(data.opcionesNomina);
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
            }
        });
    });
</script>
@endsection