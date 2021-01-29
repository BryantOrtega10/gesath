<form method="post" action="/nomina/distri/modDistriEmp" class="formGen">
    <h2>Modificar Distibucion Centro de costo</h2>
    <input type="hidden" name="idDistri" value="{{$idDistri}}" />
    <input type="hidden" name="idEmpleado" value="{{$idEmpleado}}" />
	@csrf
    @foreach ($centrosCostoGen as $centroCostoGen)
        @php
            $porcentaje = 0;
        @endphp
        @foreach ($arrEmpleadoCC as $arrCC)
            @if ($arrCC["centroCosto"] == $centroCostoGen->idcentroCosto)
                @php
                    $porcentaje = $arrCC["porcentaje"]
                @endphp
            @endif
        @endforeach
        <div class="form-group">
            <label for="porcentajeCentro{{$centroCostoGen->idcentroCosto}}" class="control-label">{{$centroCostoGen->nombre}}: </label>
            <input type="text" class="form-control" id="porcentajeCentro{{$centroCostoGen->idcentroCosto}}" name="porcentajeCentro[]" value="{{$porcentaje}}" />
            <input type="hidden" name="idCentroCosto[]" value="{{$centroCostoGen->idcentroCosto}}" />
        </div>
    @endforeach
	<div class="alert alert-danger" role="alert" id="infoErrorForm" style="display: none;"></div>
	<button type="submit" class="btn btn-success">Modificar</button>
</form>