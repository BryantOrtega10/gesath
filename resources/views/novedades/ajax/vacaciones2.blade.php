<div class="subTitulo">
    <h2>Vacaciones</h2>
    <hr />
</div>
<form action="/novedades/insertarNovedadVacaciones2" method="POST" class="formGeneral formVacaciones" id="formDatosNovedad" autocomplete="off">
    @csrf
    <input type="hidden" name="fkTipoNovedad" value="{{$req->tipo_novedad}}" />
    <input type="hidden" name="fkNomina" value="{{$req->nomina}}" />
    <input type="hidden" name="fechaRegistro" value="{{$req->fecha}}" />
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
                <label for="dias" class="control-label">Dias a compensar:</label>
                <input type="text" class="form-control" id="diasVacac" name="dias" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="pagoAnticipado" class="control-label">Pago Anticipado:</label>
                <select class="form-control" id="pagoAnticipado" name="pagoAnticipado">
                    <option value=""></option>
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
            </div>
        </div>  
    </div>
    <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
        <ul></ul>
    </div>
    <div class="text-center"><input type="submit" value="AGREGAR" class="btnSubmitGen" /></div>
</form>