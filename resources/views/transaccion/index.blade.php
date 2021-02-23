@extends('layouts.admin')
@section('title', 'Transacciones')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="cajaGeneral">
    <h1 class="granAzul">Transacciones</h1>
    <a class="btn btn-primary" href="#" id="addTransaccion">Agregar transaccion</a>
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <tr>
                <th scope="col">Grupo de concepto</th>
                <th scope="col">Cuenta credito</th>
                <th scope="col">Cuento debito</th>
                <th scope="col">Centro costo</th>
                <th scope="col"></th>
            </tr>
            @foreach ($transacciones as $transaccion)
            <tr>
                <th scope="row">{{ $transaccion->grupoConcepto_nm }}</th>
                <td>{{ $transaccion->cuenta_credito }}</td>
                <td>{{ $transaccion->cuenta_debito }}</td>
                <td>{{$transaccion->razonSocial}} - {{ $transaccion->centroCosto_nm }}</td>
                <td>
                    <a href="/transacciones/getForm/edit/{{ $transaccion->idTransaccion }}" class="editar"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    {{ $transacciones->links() }}
</div>
<div class="modal fade" id="transaccionModal" tabindex="-1" role="dialog" aria-labelledby="transaccionModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/transaccion.js') }}"></script>
@endsection