@extends('layouts.admin')
@section('title', 'Variables')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-9">
        <h1 class="granAzul">Variables</h1>
    </div>
    <div class="col-3 text-right">
        <a class="btn btnAzulGen btnGeneral text-center"href="#" id="addVariable">Agregar variable</a>
    </div>
</div>
<div class="cajaGeneral">
    <div class="table-responsive">
        <table class="table table-hover table-striped" id = "variables">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Descripci&oacute;n</th>
                    <th scope="col">Valor</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($variables as $variable)
                <tr>
                    <th scope="row">{{ $variable->idVariable }}</th>
                    <td>{{ $variable->nombre }}</td>
                    <td>{{ $variable->descripcion }}</td>
                    <td>{{ $variable->valor }}</td>
                    <td><a href="/variables/getForm/edit/{{ $variable->idVariable }}" class="editar"><i class="fas fa-edit"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- {{ $variables->appends($arrConsulta)->links() }} --}}
</div>


<div class="modal fade" id="variableModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='variable'></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="formulaVariableModal" tabindex="-1" role="dialog" aria-labelledby="variableModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='formulaVariable'></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/variables.js') }}"></script>
@endsection
