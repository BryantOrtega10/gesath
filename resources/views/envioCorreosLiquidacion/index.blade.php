@extends('layouts.admin')
@section('title', 'Envio de correos')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<h1 class="granAzul">Envio de correos</h1>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formCrear" autocomplete="off" class="formGeneral" action="/nomina/envioCorreos/crearPeticion" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="idLiquidacionNomina" value="{{$idLiquidacionNomina}}" />
                <div class="text-center"><input type="submit" value="Crear nuevo envio" class="btnSubmitGen" /></div>
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">ID Envio</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Estado</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($enviosxLiquidacion as $envioxLiquidacion)
                <tr>
                    <th scope="row">{{ $envioxLiquidacion->idEnvioCorreoLiq }}</th>
                    <td>{{ $envioxLiquidacion->fecha }}</td>
                    <td>{{ $envioxLiquidacion->nombre }}</td>
                    <td>
                        <a href="/nomina/envioCorreos/verEnviarCorreo/{{$envioxLiquidacion->idEnvioCorreoLiq}}">Ver/Enviar correos</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
    $("#formCrear").submit(function(e){
        e.preventDefault();
        cargando();
        var formdata = new FormData(this);
         $.ajax({
            type: 'POST',
            url: $(this).attr("action"),
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {
                    window.open("/nomina/envioCorreos/verEnviarCorreo/"+data.idEnvioCorreoLiq, "_self")
                } else {
                    alert(data.mensaje);
                }
            },
            error: function(data) {
                console.log("error" + data);
            }
        });
    })
});

</script>


@endsection
