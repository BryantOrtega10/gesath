<div class="novedadAdicional" data-id="{{$idRow}}">
    <div>
        <div class="offset-10 col-2 text-right">
            <a href="#" class="btn btn-outline-danger quitarNovedad" data-id="{{$idRow}}">Quitar</a>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group busquedaPop" id="busquedaEmpleado">
                <label for="nombreEmpleado" class="control-label">Empleado:</label>
                <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado" />
                <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="concepto" class="control-label">Concepto:</label>
                <select class="form-control" id="concepto" name="concepto">
                    <option value=""></option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="numDiasAus" class="control-label">Cantidad de dias:</label>
                <input type="text" id="numDiasAus" name="dias" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="numHorasAus" class="control-label">Cantidad de horas:</label>
                <input type="text" id="numHorasAus" name="horas" />
            </div>
        </div>
    </div>
</div>