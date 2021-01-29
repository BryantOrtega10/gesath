<div class="subTitulo">
    <h2>Incapacidad</h2>
    <hr />
</div>
<form action="/novedades/insertarNovedadIncapacidad" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
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
                <label for="fechaInicial" class="control-label">Fecha Inicial:</label>
                <input type="date" class="form-control" id="fechaInicial" name="fechaInicial" min="{{$req->fechaMinima}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="dias" class="control-label">Dias:</label>
                <input type="text" class="form-control" id="dias" name="dias" />
            </div>
        </div>
    </div>
    <div class="row">
        
        <div class="col-3">
            <div class="form-group">
                <label for="fechaFinal" class="control-label">Fecha Final:</label>
                <input type="date" class="form-control" id="fechaFinal" name="fechaFinal" readonly min="{{$req->fechaMinima}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaRealI" class="control-label">Fecha Eps Inicio:</label>
                <input type="date" class="form-control" id="fechaRealI" name="fechaRealI" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaRealF" class="control-label">Fecha Eps Fin:</label>
                <input type="date" class="form-control" id="fechaRealF" name="fechaRealF" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group busquedaPop"  id="busquedaCodDiagnostico">
                <label for="codigoDiagnostico" class="control-label">C&oacute;digo de diagnostico:</label>
                <input type="text" readonly class="form-control" id="codigoDiagnostico" name="codigoDiagnostico" />
                <input type="hidden" class="form-control" id="idCodigoDiagnostico" name="idCodigoDiagnostico" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="numIncapacidad" class="control-label">N&uacute;mero incapacidad:</label>
                <input type="text" class="form-control" id="numIncapacidad" name="numIncapacidad" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="pagoTotal" class="control-label">Pago total:</label>
                <select class="form-control" id="pagoTotal" name="pagoTotal">
                    <option value=""></option>
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
            </div>
        </div>  
        <div class="col-3">
            <div class="form-group">
                <label for="tipoAfiliacion" class="control-label">Tipo Entidad:</label>
                <select class="form-control" id="tipoAfiliacion" name="tipoAfiliacion">
                    <option value=""></option>
                    <option value="-1">Administradora de Riesgos Profesionales</option>
                    @foreach ($tiposAfiliacion as $tipoAfiliacion)
                        <option value="{{$tipoAfiliacion->idTipoAfiliacion}}">{{$tipoAfiliacion->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group busquedaPop"  id="busquedaEntidad">
                <label for="terceroEntidad" class="control-label">Entidad:</label>
                <input type="text" readonly class="form-control" id="terceroEntidad" name="terceroEntidad" />
                <input type="hidden" class="form-control" id="idTerceroEntidad" name="idTerceroEntidad" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="naturaleza" class="control-label">Naturaleza:</label>
                <select class="form-control" id="naturaleza" name="naturaleza">
                    <option value=""></option>
                    <option value="Accidente de trabajo">Accidente de trabajo</option>
                    <option value="Enfermedad General o Maternidad">Enfermedad General o Maternidad</option>
                    <option value="Enfermedad Profesional">Enfermedad Profesional</option>
                </select>
            </div>
        </div>
    
        <div class="col-3">
            <div class="form-group">
                <label for="tipo" class="control-label">Tipo:</label>
                <select class="form-control" id="tipo" name="tipo">
                    <option value=""></option>
                    <option value="Ambulatoria">Ambulatoria</option>
                    <option value="Hospitalaria">Hospitalaria</option>
                    <option value="Maternidad">Maternidad</option>
                    <option value="Paternidad">Paternidad</option>
                    <option value="Prorroga">Prorroga</option>
                </select>
            </div>
        </div>
        
    </div>
    <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
        <ul></ul>
    </div>
    <div class="text-center"><input type="submit" value="AGREGAR" class="btnSubmitGen" /></div>
</form>