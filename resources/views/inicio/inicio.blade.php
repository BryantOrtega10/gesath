<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('layouts.partials.head')
		<link rel="stylesheet" href="{{ URL::asset('css/styleGen.css') }}">
        <title>Bienvenido | Proyecto nomina</title>
    </head>
    <body>        
        <div class="inicioSesion">
            <img src="{{ URL::asset('img/logo.png') }}" />
            <form method="POST" action="/login" id="iniciarSesion" autocomplete="off">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control form_log" id="email" name="email" placeholder = "Usuario" />
                </div>
                <div class="form-group">
                    <input type="password" class="form-control form_log" id="password" name="password" placeholder = "Contrase&ntilde;a" />
                </div>
                
                <input type="submit" value="Ingresar" />
                <div class="contTerminos">
                    <input type="checkbox" value="aceptoTermino" id="aceptoTermino">
                    <label for="aceptoTermino">Aceptar T&eacute;rminos y Condiciones</label>
                </div>
                <div class="olvidePass">
                    Olvidaste tu contrase&ntilde;a? <a href="/recuperar_pass">Recordar contraseña</a>
                </div>

            </form>

        </div>
    </body>
    <script src = "{{ URL::asset('js/login.js') }}"></script>
</html>
