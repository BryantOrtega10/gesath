@extends('layouts.admin')
@section('title', 'Subida conceptos fijos')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Subida conceptos fijos</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            Se subireon <b>{{$subidos}}</b> registros
        @if (sizeof($errores) > 0)
            <h2>Errores</h2>
            <ul>
                @foreach ($errores as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        @endif

        </div>


    </div>
</div>

@endsection