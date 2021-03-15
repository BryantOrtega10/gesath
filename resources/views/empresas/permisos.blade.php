@extends('layouts.admin')
@section('title', 'Permisos empresa')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<h1 class="granAzul">Permisos empresa</h1>
<div class="cajaGeneral text-left">
<div class="container">
    <div class="row">
        <div class="col">            
            <form autocomplete="off" action="/empresa/permisosPortal/modificar" class="formGen" method = "POST">
                @csrf
                <input type="hidden" name="idEmpresa" value = "{{ $idEmpresa }}">
                <div class="form-check">
                    <input value = "1" type="checkbox" class="form-check-input" id="permiso1" name="permiso1"
                    @if(isset($permisos[0]) && $permisos[0]=="1") checked @endif>
                    <label class="form-check-label" for="permiso1">Información laboral</label>
                </div>
                <div class="form-check">
                    <input value = "1" type="checkbox" class="form-check-input" id="permiso2" name="permiso2"
                    @if(isset($permisos[1]) && $permisos[1]=="1") checked @endif>
                    <label class="form-check-label" for="permiso2">Vacaciones</label>
                </div>
                <div class="form-check">
                    <input value = "1" type="checkbox" class="form-check-input" id="permiso3" name="permiso3"
                    @if(isset($permisos[2]) && $permisos[2]=="1") checked @endif>
                    <label class="form-check-label" for="permiso3">Certificado laboral</label>
                </div>
                <div class="form-check">
                    <input value = "1" type="checkbox" class="form-check-input" id="permiso4" name="permiso4"
                    @if(isset($permisos[3]) && $permisos[3]=="1") checked @endif>
                    <label class="form-check-label" for="permiso4">Comprobante de pago</label>
                </div>
                <div class="form-check">
                    <input value = "1" type="checkbox" class="form-check-input" id="permiso5" name="permiso5"
                    @if(isset($permisos[4]) && $permisos[4]=="1") checked @endif>
                    <label class="form-check-label" for="permiso5">Certificado de ingreso y retenciones</label>
                </div>
                <div class="form-check">
                    <input value = "1" type="checkbox" class="form-check-input" id="permiso6" name="permiso6"
                    @if(isset($permisos[5]) && $permisos[5]=="1") checked @endif>
                    <label class="form-check-label" for="permiso6">Cambiar contraseña</label>
                </div>
                <div class="form-check">
                    <input value = "1" type="checkbox" class="form-check-input" id="permiso7" name="permiso7"
                    @if(isset($permisos[6]) && $permisos[6]=="1") checked @endif>
                    <label class="form-check-label" for="permiso7">Perfil</label>
                </div>
                

                <br>

                <button type="submit" id="envio" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(() => {
        $("body").on("submit", ".formGen", function(e){
            e.preventDefault();
            if(typeof $("#cargando")[0] !== 'undefined'){
                $("#cargando").css("display", "flex");
            }
            else{
                $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
            }
            var formdata = new FormData(this);
            $.ajax({
                type:'POST',
                url: $(this).attr("action"),
                cache:false,
                processData: false,
                contentType: false,
                data: formdata,
                success:function(data){
                    $("#cargando").css("display", "none");
                    if(data.success){
                        retornarAlerta(
                            "Permisos actualizados",
                            "Permisos actualizados",
                            "success",
                            'Aceptar'
                        );
                        window.location.reload();
                    }
                    else{
                        $("#infoErrorForm").css("display", "block");
                        $("#infoErrorForm").html(data.mensaje);
                    }
            },
            error: function(data){
                    console.log("error");
                    console.log(data);
                }
            });	
        });	
    });
</script>
@endsection