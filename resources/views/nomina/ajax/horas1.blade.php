<div class="subTitulo">
    <h2>Horas Extras - Rango Horas</h2>
    <hr />
</div>
<form action="/nomina/insertarNovedadHoraTipo1" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
    @csrf
    <input type="hidden" name="fkTipoNovedad" value="{{$req->tipo_novedad}}" />
    <input type="hidden" name="fkTipoReporte" value="{{$req->tipo_reporte}}" />
    <input type="hidden" name="fkNomina" value="{{$req->nomina}}" />
    <input type="hidden" name="fechaRegistro" value="{{$req->fecha}}" />

    <div class="row">
        <div class="col-3">
            <div class="form-group busquedaPop" data-res-input="#idEmpleado" data-res-input2="#nombreEmpleado" id="busquedaEmpleado">
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
                <label for="horaInicial" class="control-label">Hora Inicial:</label>
                <input type="datetime-local" class="form-control" id="horaInicial" name="horaInicial" min="{{$req->fechaMinima}}T00:00:00" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="horaFinal" class="control-label">Hora Final:</label>
                <input type="datetime-local" class="form-control" id="horaFinal" name="horaFinal" min="{{$req->fechaMinima}}T00:00:00" />
            </div>
        </div>
    </div>
    <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
        <ul></ul>
      </div>
    <div class="text-center"><input type="submit" value="AGREGAR" class="btnSubmitGen" /></div>
</form>