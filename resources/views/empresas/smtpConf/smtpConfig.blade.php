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
            <div class="form-check">
                <input value = "1" type="checkbox" class="form-check-input" id="check_smtp"
                @if(!empty($smtp->fkSmtpConf)) checked @endif>
                <label class="form-check-label" for="check_smtp">¿Tiene configuración SMTP propia?</label>
            </div>
            <form autocomplete="off" class = "smtp_configuracion" method = "POST">
                @csrf
                <input type="hidden" name="idEmpresa" value = "{{ $idEmpresa }}">
                <div class="form-group">
                    <label for="smtp_host">Host</label>
                    <input autocomplete="false" type="text" class="form-control" name = "smtp_host" id="smtp_host" placeholder="Host SMTP" value = "{{ $smtp->smtp_host ?? $smtpDefault['host'] }}"
                    @if(empty($smtp->fkSmtpConf))
                        readonly
                    @endif
                    >
                </div>
                <div class="form-group">
                    <label for="smtp_username">Usuario</label>
                    <input autocomplete="false" type="email" name = "smtp_username" class="form-control" id="smtp_username" placeholder="Usuario SMTP" value = "{{ $smtp->smtp_username ?? $smtpDefault['user'] }}"
                    @if(empty($smtp->fkSmtpConf))                       
                        readonly                        
                    @endif
                    >
                </div>
                <div class="form-group">
                    <label for="smtp_password">Contraseña</label>
                    <input autocomplete="new-password" type="password" class="form-control" id="smtp_password" name = "smtp_password" placeholder="Contraseña SMTP" value = "{{ $smtp->smtp_password ?? $smtpDefault['pass'] }}"
                    @if(empty($smtp->fkSmtpConf))                        
                        readonly                       
                    @endif
                    >
                </div>
                <div class="form-group">
                    <label for="smtp_encrypt">Encriptación</label>
                    <select name="smtp_encrypt" id="smtp_encrypt" class="form-control"
                    @if(empty($smtp->fkSmtpConf))
                        readonly
                    @endif
                    >
                        <option value="">-- Seleccione una opción --</option>
                        <option value = "tls"
                            @if ($smtp->smtp_encrypt ?? $smtpDefault['encrypt'] == 'TLS')
                                selected="selected"
                            @endif
                        >               
                            TLS
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="smtp_port">Puerto</label>
                    <input autocomplete="false" type="numbr" class="form-control" id="smtp_port" name = "smtp_port" placeholder="Puerto SMTP" value = "{{ $smtp->smtp_port ?? $smtpDefault['port']}}"
                    @if(empty($smtp->fkSmtpConf))
                        readonly
                    @endif
                    >
                </div>
                <div class="form-group">
                    <label for="smtp_mail_envia">Correo Envía</label>
                    <input autocomplete="false" type="email" class="form-control" name = "smtp_mail_envia" id="smtp_mail_envia" placeholder="Correo envía" value = "{{ $smtp->smtp_mail_envia ?? $smtpDefault['sender_mail'] }}"
                    @if(empty($smtp->fkSmtpConf))
                        readonly
                    @endif
                    >
                </div>
                <div class="form-group">
                    <label for="smtp_nombre_envia">Nombre Envía</label>
                    <input autocomplete="false" type="text" class="form-control" name = "smtp_nombre_envia" id="smtp_nombre_envia" placeholder="Nombre envía" value = "{{ $smtp->smtp_nombre_envia ?? $smtpDefault['sender_name'] }}"
                    @if(empty($smtp->fkSmtpConf))
                        readonly
                    @endif
                    >
                </div>
                <button type="submit" id="envio" class="btn btn-primary @if(empty($smtp->fkSmtpConf))
                    d-none
                @endif">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(() => {
        let errorTit = '¡Error!';
        let ic = 'error';
        const arrDefaultSmtp = [
            '{{$smtpDefault['host']}}',
            '{{$smtpDefault['user']}}',
            '{{$smtpDefault['pass']}}',
            '{{$smtpDefault['encrypt']}}',
            '{{$smtpDefault['port']}}',
            '{{$smtpDefault['sender_mail']}}',
            '{{$smtpDefault['sender_name']}}'
        ];
        const arrAntSmtp = '{{ $smtp }}';
        
        var objSmtpActual = {};
        if(arrAntSmtp!=""){
            var objSmtpActual = JSON.parse(arrAntSmtp.replace(/&quot;/g,'"'));
        }


        const arrSmtpActual = [];
        arrSmtpActual.push(objSmtpActual.smtp_host);
        arrSmtpActual.push(objSmtpActual.smtp_username);
        arrSmtpActual.push(objSmtpActual.smtp_password);
        arrSmtpActual.push(objSmtpActual.smtp_encrypt);
        arrSmtpActual.push(objSmtpActual.smtp_port);
        arrSmtpActual.push(objSmtpActual.smtp_mail_envia);
        arrSmtpActual.push(objSmtpActual.smtp_nombre_envia);
        $("body").on("click", "#check_smtp", () => {
            let i_rec = 0;
            const valCheck = $('#check_smtp').is(':checked');
            const inputsForm = $('.smtp_configuracion input, .smtp_configuracion select');
            Object.keys(inputsForm).forEach((i, el) => {
                const idInput = inputsForm[i].id;
                if (idInput !== '' && idInput !== undefined) {
                    if(valCheck){
                        $("#envio").removeClass("d-none");
                        $("#envio").addClass("d-inline-block");
                       
                    }
                    else{
                        $("#envio").addClass("d-none");
                        $("#envio").removeClass("d-inline-block");
                    }
                    
                    $('#' + idInput).attr('readonly', !valCheck);
                    if (valCheck == true) {
                        if (arrSmtpActual !== undefined) {
                            $('#' + idInput).val(arrSmtpActual[i_rec]);
                            if (i_rec == 4) {
                                $("#smtp_encrypt option[value='" + arrSmtpActual[i_rec] +"']").prop('selected', true);
                            }
                        } else {
                            $('#' + idInput).val('');
                        }
                        i_rec++;
                    } else {
                        if (objSmtpActual.fkSmtpConf == 11 && arrSmtpActual !== undefined) {
                            $('#' + idInput).val(arrSmtpActual[i_rec]);
                        } else {
                            $('#' + idInput).val(arrDefaultSmtp[i_rec]);
                            if (i_rec == 3) {
                                $("#smtp_encrypt option[value='" + arrSmtpActual[i_rec] +"']").prop('selected', true);
                            }
                        }
                        i_rec++;
                    }
                }
            });
        });
        $("body").on("submit", ".smtp_configuracion", (e) => {
            e.preventDefault();
            const formData = new FormData($('.smtp_configuracion')[0]);
            formData.append('confPropia', $('#check_smtp').is(':checked'));
            solicitudAjax(`/empresa/smtp/actSMTPConfig`, 'POST', formData,
                (data) => {
                    if (data.success) {
                        errorTit = '¡Hecho!';
                        ic = 'success';
                    }
                    retornarAlerta(
                        errorTit,
                        data.mensaje,
                        ic,
                        'Aceptar'
                    );
                    window.location.reload();
                }, (err) => {
                    const error = err.responseJSON;
                    if (error.error_code === 'VALIDATION_ERROR') {
                        mostrarErrores(error.errors);
                    } else {
                        $("#cargando").css("display", "none");
                        retornarAlerta(
                            err.responseJSON.exception,
                            err.responseJSON.message + ", en la linea: " + err.responseJSON.line,
                            'error',
                            'Aceptar'
                        );
                        console.log("error");
                        console.log(err);
                    }
                }
            );
        });
    });
</script>
@endsection