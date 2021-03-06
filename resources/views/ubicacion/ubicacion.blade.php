@extends('layouts.admin')
@section('title', 'Ubicación')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection


@section('contenido')
<div class="row">
    <div class="col-9">
        <h1 class="granAzul">Ubicaci&oacute;n</h1>
    </div>
    <div class="col-3 text-right">
        <a class="btn btnGeneral btnAzulGen text-center" href="#" id="addVariable">Agregar ubicaci&oacute;n</a>
    </div>
</div>

<div class="cajaGeneral">
    
    
    <div class="table-responsive">
        <table class="table table-hover table-striped" id = "ubicaciones">
            <thead>
                <tr>
                    <th scope="col">C&oacute;digo</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Superior</th>
                    <th scope="col">Nombre</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ubicaciones as $ubicacion)
                <tr>
                    <td>{{ $ubicacion->idubicacion }}</td>
                    <td>{{ $ubicacion->tpu_nombre }}</td>
                    <td>{{ $ubicacion->u2_nombre }}</td>
                    <td>{{ $ubicacion->nombre }}</td>
                    <td><a href="/ubicacion/getForm/edit/{{ $ubicacion->idubicacion }}" class="editar"><i class="fas fa-edit"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- {{-- {{ $ubicaciones->links() }} --}} -->
</div>
<div class="modal fade" id="ubicacionModal" tabindex="-1" role="dialog" aria-labelledby="ubicacionModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/ubicacion.js') }}"></script>
@endsection
