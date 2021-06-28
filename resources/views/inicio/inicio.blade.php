<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- CSS -->

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        <link rel = "stylesheet" href = "https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
        <link rel = "stylesheet" href = "https://cdn.jsdelivr.net/npm/fullcalendar@5.5.0/main.css">

        <!-- JAVASCRIPT -->

        <script  src="https://code.jquery.com/jquery-3.5.1.min.js"  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="  crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/688179f4a3.js" crossorigin="anonymous"></script>
        <script src = "https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <script src = "https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src = "https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
        <script src = "https://cdn.jsdelivr.net/npm/fullcalendar@5.5.0/main.min.js"></script>
        <script src = "https://cdn.jsdelivr.net/npm/fullcalendar@5.5.0/locales/es.js"></script>
        <script src='https://cdn.jsdelivr.net/npm/moment@2.27.0/min/moment.min.js'></script>

        <script src="{{ URL::asset('js/general.js') }}"></script>
        <script src="{{ URL::asset('js/controlErroresFormularios.js') }}"></script>
        <script src="{{ URL::asset('js/sweetAlertas.js') }}"></script>
        <script src="{{ URL::asset('js/funcionesGenerales.js') }}"></script>
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
                    <div class="ojo-password" data-para="password"><i class="fas fa-eye"></i></div>
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
