<div class="row" style="margin-bottom: 10px;">
    <div class="col-3">
        <input type="text" class="form-control campoNom" name="campoNom[]" readonly value="{{$itemReporte->nombre}}" />
        <input type="hidden" name="campoId[]" readonly value="{{$itemReporte->IdItemTipoReporte}}" />
    </div>
    <div class="col-2">
        <select class="form-control"name="operador[]" required="">
            @foreach($OperadorComparacion as $opComparacion)
                <option value="{{$opComparacion}}">{{$opComparacion}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-3">
        @if ($idReporteItem == "41")
            <select class="form-control"name="filtro[]" required="">
                @foreach ($estados as $estado)
                    <option value="{{$estado->nombre}}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        @else
            @if ($itemReporte->tipo == "texto")
                <input type="text" class="form-control filtro" name="filtro[]" required />
            @endif
            @if ($itemReporte->tipo == "fecha")
                <input type="date" class="form-control filtro" name="filtro[]" required />
            @endif
            @if ($itemReporte->tipo == "bool")
                <select class="form-control"name="filtro[]" required="">
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
            @endif
        @endif
    </div>
    <div class="col-2">
        <select class="form-control"name="concector[]" required="">
            <option value="AND">AND</option>
            <option value="OR">OR</option>
        </select>
    </div>
    <div class="col-2">
        <a href="#" class="btn btn-outline-danger quitarFiltro">Quitar</a>
    </div>
</div>
