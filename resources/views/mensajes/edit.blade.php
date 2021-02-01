@extends('layouts.admin')
@section('title', 'Modificar Mensaje')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1>Modificar Mensaje</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            @if (isset($modificacion))
                <div class="p-3 mb-2 bg-success text-white desaparece">Modificación exitosa</div>
            @endif
            <form method="POST" action="/mensajes/modificar">
                @csrf
                <input type="hidden" name="idMensaje" value="{{$mensaje->idMensaje}}">
                <textarea name="html" id="tinyMce">{{$mensaje->html}}</textarea><br>
                <button type="submit" class="btnSubmitGen">Modificar Mensaje</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.tiny.cloud/1/yp871v34ln01zvn1b7dr1flllyzc3pge887bzbkeuzgd58f1/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script type="text/javascript">
    tinymce.init({
        height: 500,
        selector: '#tinyMce',
        language: 'es',
        plugins: 'a11ychecker advcode casechange formatpainter linkchecker autolink lists checklist media mediaembed pageembed permanentpen powerpaste table advtable',
        toolbar: 'a11ycheck addcomment showcomments casechange checklist code formatpainter pageembed permanentpen table',
    });
    </script>
@endsection