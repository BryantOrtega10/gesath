<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('layouts.partials.head')
		<link rel="stylesheet" href="{{ URL::asset('css/styleGen.css') }}">
        <link rel="icon" type="image/vnd.microsoft.icon" href="{{URL::asset('img/favicon.ico')}}">
        <title>Bienvenido | Proyecto nomina</title>
    </head>
    <body class="blanco">        
        <div class="inicioSesion">
            <img src="{{ URL::asset('img/logo.png') }}" />
            <form method="POST" action="/login" id="iniciarSesion" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label for="email">Usuario</label>
                    <input type="text" class="form-control form_log" id="email" name="email" />
                </div>
                <div class="form-group">
                    <label for="password">Contrase&ntilde;a</label>
                    <input type="password" class="form-control form_log" id="password" name="password" />
                </div>
                
                <input type="submit" value="Ingresar" class="enfasis-background" />
                <div class="contTerminos">
                    <input type="checkbox" value="aceptoTermino" id="aceptoTermino">
                    <label for="aceptoTermino">Acepto la <a href="https://gesath.com/nosotros/politica-de-tratamiento-y-proteccion-de-datos-personales/" target="_blank">pol&iacute;tica de tratamiento y protección de datos personales</a></label>
                </div>
                <div class="olvidePass">
                    Olvidaste tu contrase&ntilde;a? <a href="/recuperar_pass">Recordar contraseña</a>
                </div>

            </form>

        </div>
    </body>
    <script src = "{{ URL::asset('js/login.js') }}"></script>
</html>
