@extends('layouts.admin')
@section('title', 'Configuración SMTP')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection
@section('contenido')
<h1 class="granAzul">Configuración SMTP</h1>
<div class="container">
    <div class="row">
        <div class="col">
            <form autocomplete="off" action="{{$url}}" class = "smtp_configuracion" method = "POST">
                @csrf
                <input type="hidden" name="id_smpt" value = "{{ $smtp->id_smpt }}">
                <div class="form-group">
                    <label for="smtp_host">Host</label>
                    <input autocomplete="false" type="text" class="form-control" name = "smtp_host" id="smtp_host" placeholder="Host SMTP" value = "{{ $smtp->smtp_host }}">
                </div>
                <div class="form-group">
                    <label for="smtp_username">Usuario</label>
                    <input autocomplete="false" type="email" name = "smtp_username" class="form-control" id="smtp_username" placeholder="Usuario SMTP" value = "{{ $smtp->smtp_username }}">
                </div>
                <div class="form-group">
                    <label for="smtp_password">Contraseña</label>
                    <input autocomplete="new-password" type="password" class="form-control" id="smtp_password" name = "smtp_password" placeholder="Contraseña SMTP" value = "{{ $smtp->smtp_password }}">
                </div>
                <div class="form-group">
                    <label for="smtp_encrypt">Encriptación</label>
                    <select name="smtp_encrypt" id="smtp_encrypt" class="form-control">
                        <option value="">-- Seleccione una opción --</option>
                        <option value = "TLS" @if ($smtp->smtp_encrypt == "TLS")
                            selected
                        @endif>TLS</option>
                        <option value = "SSL" @if ($smtp->smtp_encrypt == "SSL")
                            selected
                        @endif>SSL</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="smtp_port">Puerto</label>
                    <input autocomplete="false" type="numbr" class="form-control" id="smtp_port" name = "smtp_port" placeholder="Contraseña SMTP" value = "{{ $smtp->smtp_port}}">                    
                </div>
                <div class="form-group">
                    <label for="smtp_mail_envia">Correo Envía</label>
                    <input autocomplete="false" type="email" class="form-control" name = "smtp_mail_envia" id="smtp_mail_envia" placeholder="Correo envía" value = "{{ $smtp->smtp_mail_envia }}">
                </div>
                <div class="form-group">
                    <label for="smtp_nombre_envia">Nombre Envía</label>
                    <input autocomplete="false" type="text" class="form-control" name = "smtp_nombre_envia" id="smtp_nombre_envia" placeholder="Nombre envía" value = "{{ $smtp->smtp_nombre_envia }}">
                </div>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(() => {
        $("body").on("submit", ".smtp_configuracion", function(e) {
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
                    if (data.success) {
                        retornarAlerta(
                            '¡Hecho!',
                            data.mensaje,
                            'success',
                            'Aceptar'
                        );
                        window.location.reload();
                    } else {
                        $("#infoErrorForm").css("display", "block");
                        $("#infoErrorForm").html(data.mensaje);
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