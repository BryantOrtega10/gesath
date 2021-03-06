@extends('layouts.admin')
@section('title', 'Mensajes')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Mensajes</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
           <table class="table table-hover table-striped">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th></th>
                </tr>
       
                    @foreach ($mensajes as $mensaje)
                        <tr>
                            <td>{{$mensaje->idMensaje}}</td>
                            <td>{{$mensaje->nombre}}</td>
                            <td><a class="editar" href="/mensajes/getForm/editxEmpresa/{{$mensaje->idMensaje}}/{{$fkEmpresa}}"><i class="fas fa-edit"></i></a></td>
                        </tr>
                    @endforeach
       
                    @foreach ($mensajesDefault as $mensaje)
                        <tr>
                            <td>{{$mensaje->idMensaje}}</td>
                            <td>{{$mensaje->nombre}}</td>
                            <td>
                                <div class="dropdown">
                                    <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                                    <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item editar" href="/mensajes/getForm/edit/{{$mensaje->idMensaje}}"><i class="fas fa-edit"></i> Editar General</a>
                                        <a class="dropdown-item editar" href="/mensajes/getForm/editxEmpresa/{{$mensaje->idMensaje}}/{{$fkEmpresa}}"><i class="fas fa-file"></i> Crear Conf</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                
                
                
           </table>
        </div>
    </div>
</div>

@endsection