@extends('layouts.admin')
@section('title', 'Subida conceptos fijos')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Subida conceptos fijos</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            Se subireon <b>{{$subidos}}</b> registros
        </div>
    </div>
</div>

@endsection