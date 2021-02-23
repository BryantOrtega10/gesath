@extends('layouts.admin')
@section('title', $titulo)
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">{{$titulo}}</h1>
        <p>{{$mensaje}}</p>
    </div>
</div>
@endsection
