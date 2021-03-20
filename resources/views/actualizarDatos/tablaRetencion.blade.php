@extends('layouts.admin')
@section('title', 'Valor Upc Adicional')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-10">
        <h1 class="granAzul">Valor Upc Adicional</h1>
    </div>
    <div class="col-2">
        <a href="#" class="btnGeneral btnAzulGen addRegistro">Agregar fila</a>
    </div>
</div>
<div class="cajaGeneral">
    <div class="row">
        <table class="table table-hover table-striped">
            <tr>
                <th>Minimo</th>
                <th>Maximo</th>
                <th>Adicion</th>
                <th>Porcentaje</th>
                <th></th>
            </tr>
            @foreach ($retencion as $rete)
                <tr>
                    <td>{{$rete->minimo}}</td>
                    <td>{{$rete->maximo}}</td>
                    <td>{{$rete->adicion}}</td>
                    <td>{{($rete->porcentaje * 100)}}%</td>
                    <td>
                        <div class="dropdown">
                            <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                            <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                                <a data-id ="{{ $rete->idTablaRetencion }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar</a>
                                <a  data-id ="{{ $rete->idTablaRetencion }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar</a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
<div class="modal fade" id="reteModal" tabindex="-1" role="dialog" aria-labelledby="reteModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='rete'></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.numeric.extensions.js') }}"></script>
<script>
    function cargando() {
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
    }
    $(document).ready((e) => {
       
        $(".editar").click(function(e) {
            e.preventDefault();
            cargando();
            var dataId = $(this).attr("data-id");
            $.ajax({
                type: 'GET',
                url: "/ActualizarDatos/valoresRetencion/edit/"+dataId,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".respForm[data-para='rete']").html(data);
                    $('#reteModal').modal('show');
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        });

        $(".eliminar").click(function(e) {
            e.preventDefault();
            if(confirm("En verdad desea eliminar ese registro?")){
                var dataId = $(this).attr("data-id");
                cargando();
                $.ajax({
                    type: 'GET',
                    url: "/ActualizarDatos/valoresRetencion/delete/"+dataId,
                    success: function(data) {
                        $("#cargando").css("display", "none");
                        if (data.success) {
                            retornarAlerta(
                                '¡Hecho!',
                                data.mensaje,
                                'success',
                                'Aceptar'
                            );
                            window.location.reload();
                        } else {
                            retornarAlerta(
                                '¡Error!',
                                data.mensaje,
                                'error',
                                'Aceptar'
                            );
                        }
                    },
                    error: function(data) {
                        console.log("error");
                        console.log(data);
                    }
                });
            }
            
        });
        
        $(".addRegistro").click(function(e) {
            e.preventDefault();
            cargando();
            $.ajax({
                type: 'GET',
                url: "/ActualizarDatos/valoresRetencion/add",
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".respForm[data-para='rete']").html(data);
                    $('#reteModal').modal('show');
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        });
        

        $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
        $("body").on("submit", ".formGen", function(e) {
            e.preventDefault();
            var formdata = new FormData(this);
            $.ajax({
                type: 'POST',
                url: $(this).attr("action"),
                cache: false,
                processData: false,
                contentType: false,
                data: formdata,
                success: function(data) {
                    $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
                    if (data.success) {
                        retornarAlerta(
                            '¡Hecho!',
                            data.mensaje,
                            'success',
                            'Aceptar'
                        );
                        window.location.reload();
                    } else {
                        retornarAlerta(
                            '¡Error!',
                            data.mensaje,
                            'error',
                            'Aceptar'
                        );
                    }
                },
                error: function(data) {
                    const error = data.responseJSON;
                    if (error.error_code === 'VALIDATION_ERROR') {
                        mostrarErrores(error.errors);
                    } else {
                        console.log("error");
                    }
                }
            });
        });
    });
</script>
@endsection
