@extends('layouts.partials.portalEmpleado.head', [
    'dataEmple' => $dataEmple,
    'fotoEmple' => $fotoEmple
])
@section('title', 'Home | Portal Empleado')
@section('contenidoPortal')
<h1>Portal empleado</h1>
<!-- Sección de botones portal empleado -->
<input type = "hidden" name = "idUsu" id = "idUsu" value = "{{ $dataUsu->fkEmpleado }}">
{{--<input type = "hidden" name = "idUsu" id = "idUsu" value = "34">--}}
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="card tarjeta_hover puntero alto_tarjeta info_laboral">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Información laboral</h5>
                <p class="card-text mt-auto">Consulte información sobre su actual trabajo.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="card tarjeta_hover puntero alto_tarjeta vacaciones_emple">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Vacaciones</h5>
                <p class="card-text mt-auto">Enterese de sus vacaciones disfrutadas o por disfrutar.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="card tarjeta_hover puntero alto_tarjeta">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Certificado laboral</h5>
                <p class="card-text mt-auto">Genere su certificado laboral seleccionando un periodo</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="card tarjeta_hover puntero alto_tarjeta">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Comprobante de pago</h5>
                <p class="card-text mt-auto">Genere su comprobante de pago</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="card tarjeta_hover puntero alto_tarjeta">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Certificado de ingreso y retenciones</h5>
                <p class="card-text mt-auto">Genere su certificado de ingreso y pensiones</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="card tarjeta_hover puntero alto_tarjeta cambiar_pass">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Cambiar contraseña</h5>
                <p class="card-text mt-auto">Cambie la contraseña para ingresar al portal</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="card tarjeta_hover puntero alto_tarjeta perfil_emple">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Perfil</h5>
                <p class="card-text mt-auto">Actualice los datos del portal de empleado</p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="portalEmpleModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='portalEmple'></div>
            </div>
        </div>
    </div>
</div>
@endsection