@extends('layouts.admin')
@section('title', 'Notificaciones')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-9">
        <h1 class="granAzul">Notificaciones</h1>
    </div>
    <div class="col-3 text-right">
        <a class="btn btnAzulGen btnGeneral text-center" href="/notificaciones/modificarVisto">Marcar todas como vistas</a>
    </div>
</div>
<div class="cajaGeneral">
<form autocomplete="off" action="{{ Request::url() }}" method="GET" id="filtrarEmpleado" class="formGeneral">
    <div class="row">
        <div class="col-2">
            <div class="form-group @isset($req->nombre) hasText @endisset">
                <label for="nombre" class="control-label">Nombre:</label>
                <input type="text" class="form-control" name="nombre" id="nombre" @isset($req->nombre) value="{{$req->nombre}}" @endisset/>
            </div>               
        </div>
        <div class="col-2">
            <div class="form-group @isset($req->numDoc) hasText @endisset">
                <label for="numDoc" class="control-label">Número Identificación:</label>
                <input type="text" class="form-control" id="numDoc" name="numDoc" @isset($req->numDoc) value="{{$req->numDoc}}" @endisset/>
            </div>               
        </div>
        <div class="col-2">
            <div class="form-group @isset($req->fechaInicio) hasText @endisset">
                <label for="fechaInicio" class="control-label">Fecha inicio:</label>
                <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" @isset($req->fechaInicio) value="{{$req->fechaInicio}}" @endisset/>
            </div>               
        </div>
        <div class="col-2">
            <div class="form-group @isset($req->fechaFin) hasText @endisset">
                <label for="fechaFin" class="control-label">Fecha fin:</label>
                <input type="date" class="form-control" id="fechaFin" name="fechaFin" @isset($req->fechaFin) value="{{$req->fechaFin}}" @endisset/>
            </div>               
        </div>
        <div class="col-3">
            <input type="submit" value="Consultar"/><input type="reset" class="recargar" style="margin-left: 5px;" data-url="{{Request::url()}}" value="" /> 
        </div>
    </div>   
</form>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <table class="table table-hover table-striped">
                <tr>
                    <th>Fecha</th>
                    <th>Mensaje</th>
                    <th></th>
                </tr>
                @foreach ($notificaciones as $notificacion)
                    <tr>
                        <td>{{substr($notificacion->fecha,0,10)}}</td>
                        <td>{{$notificacion->mensaje}}</td>
                        <td><a href="/empleado/formModificar/{{$notificacion->fkEmpleado}}">Ir a modificar empleado</a></td>
                    </tr>
                @endforeach
            </table>
            {{ $notificaciones->links() }}
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(e){
    $(".recargar").click(function(){
        window.open("/notificaciones/","_self");
    });
});
</script>

@endsection