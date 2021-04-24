<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('layouts.partials.head')
		<link rel="stylesheet" href="{{ URL::asset('css/styleGen.css') }}">
        <title>@yield('title','Bienvenido') | Proyecto nomina</title>
    </head>
    <body>
        
        <section class="contenido">
            <div class="menuLateral">
                @yield('menuLateral')
                <a href="/versiones/Notas version 2.1.pdf" class="version">Version: 2.1</a>
            </div>
            <div class="contenidoInterno">
                @yield('contenido')
            </div>
          
        </section>
    </body>
</html>
