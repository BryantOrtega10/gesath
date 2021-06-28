<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('layouts.partials.head')
		<link rel="stylesheet" href="{{ URL::asset('css/styleGen.css') }}">
        <title>@yield('title','Bienvenido') | Proyecto nomina</title>
        <link rel="icon" type="image/vnd.microsoft.icon" href="{{URL::asset('img/favicon.ico')}}">
    </head>
    <body>
        
        <section class="contenido">
            <div class="menuLateral">
                @yield('menuLateral')
                <a href="#" target="_blank" class="version">Version: 2.3</a>
            </div>
            <div class="contenidoInterno">
                @yield('contenido')
            </div>
          
        </section>
    </body>
</html>
