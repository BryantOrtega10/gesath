<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('layouts.partials.head')
		<link rel="stylesheet" href="{{ URL::asset('css/styleGen.css') }}">
        <title>Recuperar contraseña</title>
    </head>
    <body>        
        <div class="inicioSesion">
            <img src="{{ URL::asset('img/logo.png') }}" />
            <form method="POST" id = "solicitar_rec_pass" action="/enviar_correo_rec_pass">
                @csrf
                <div class="form-group row">
                    <input id="email" type="email" class="form-control form_log" name="email" placeholder = "Correo" required autofocus>
                </div>
                <div class="form-group row mb-0">
                    <div class="col">
                        <input type="submit" value="Recuperar" />
                    </div>
                </div>
            </form>
        </div>
    </body>
    <script src = "{{ URL::asset('js/funcionesGenerales.js') }}"></script>
    <script src = "{{ URL::asset('js/recuperarPass.js') }}"></script>
</html>
