<div class="novedadAdicional" data-id="{{$idRow}}">
    @if ($idRow != 0)
        <div class="row">
            <div class="offset-10 col-2 text-right">
                <a href="#" class="btn btn-outline-danger quitarNovedadAdicional" data-id="{{$idRow}}">Quitar</a>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-3">
            <div class="form-group busquedaPop busquedaEmpleado" id="busquedaEmpleado{{$idRow}}" data-id="{{$idRow}}">
                <label for="nombreEmpleado{{$idRow}}" class="control-label">Empleado:</label>
                <input type="text" readonly class="form-control nombreEmpleado" id="nombreEmpleado{{$idRow}}" name="nombreEmpleado[]" data-id="{{$idRow}}" />
                <input type="hidden" class="form-control idEmpleado" id="idEmpleado{{$idRow}}" name="idEmpleado[]" data-id="{{$idRow}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="concepto{{$idRow}}" class="control-label">Concepto:</label>
                <select class="form-control concepto" id="concepto{{$idRow}}" name="concepto[]">
                    <option value=""></option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaInicial{{$idRow}}" class="control-label">Fecha Inicial:</label>
                <input type="date" class="form-control fechaInicial" id="fechaInicial{{$idRow}}" data-id="{{$idRow}}" name="fechaInicial[]" min="{{$req->fechaMinima}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="dias{{$idRow}}" class="control-label">Dias:</label>
                <input type="text" class="form-control dias" id="dias{{$idRow}}" name="dias[]" data-id="{{$idRow}}" />
            </div>
        </div>
    </div>
    <div class="row">
        
        <div class="col-3">
            <div class="form-group">
                <label for="fechaFinal{{$idRow}}" class="control-label">Fecha Final:</label>
                <input type="date" class="form-control" id="fechaFinal{{$idRow}}" name="fechaFinal[]" readonly min="{{$req->fechaMinima}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaRealI{{$idRow}}" class="control-label">Fecha Eps Inicio:</label>
                <input type="date" class="form-control" id="fechaRealI{{$idRow}}" name="fechaRealI[]" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaRealF{{$idRow}}" class="control-label">Fecha Eps Fin:</label>
                <input type="date" class="form-control" id="fechaRealF{{$idRow}}" name="fechaRealF[]" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group busquedaPop busquedaCodDiagnostico"  id="busquedaCodDiagnostico{{$idRow}}" data-id="{{$idRow}}">
                <label for="codigoDiagnostico{{$idRow}}" class="control-label">C&oacute;digo de diagnostico:</label>
                <input type="text" readonly class="form-control" id="codigoDiagnostico{{$idRow}}" name="codigoDiagnostico[]" data-id="{{$idRow}}" />
                <input type="hidden" class="form-control" id="idCodigoDiagnostico{{$idRow}}" name="idCodigoDiagnostico[]" data-id="{{$idRow}}" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="numIncapacidad{{$idRow}}" class="control-label">N&uacute;mero incapacidad:</label>
                <input type="text" class="form-control" id="numIncapacidad{{$idRow}}" name="numIncapacidad[]" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="pagoTotal{{$idRow}}" class="control-label">Pago total:</label>
                <select class="form-control" id="pagoTotal{{$idRow}}" name="pagoTotal[]">
                    <option value=""></option>
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
            </div>
        </div>  
        <div class="col-3">
            <div class="form-group">
                <label for="tipoAfiliacion{{$idRow}}" class="control-label">Tipo Entidad:</label>
                <select class="form-control tipoAfiliacion" id="tipoAfiliacion{{$idRow}}" name="tipoAfiliacion[]" data-id="{{$idRow}}">
                    <option value=""></option>
                    <option value="-1">Administradora de Riesgos Profesionales</option>
                    @foreach ($tiposAfiliacion as $tipoAfiliacion)
                        <option value="{{$tipoAfiliacion->idTipoAfiliacion}}">{{$tipoAfiliacion->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group busquedaPop"  id="busquedaEntidad{{$idRow}}" data-id="{{$idRow}}">
                <label for="terceroEntidad{{$idRow}}" class="control-label">Entidad:</label>
                <input type="text" readonly class="form-control terceroEntidad" id="terceroEntidad{{$idRow}}" name="terceroEntidad[]" />
                <input type="hidden" class="form-control idTerceroEntidad" id="idTerceroEntidad{{$idRow}}" name="idTerceroEntidad[]" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="naturaleza{{$idRow}}" class="control-label">Naturaleza:</label>
                <select class="form-control" id="naturaleza{{$idRow}}" name="naturaleza[]">
                    <option value=""></option>
                    <option value="Accidente de trabajo">Accidente de trabajo</option>
                    <option value="Enfermedad General o Maternidad">Enfermedad General o Maternidad</option>
                    <option value="Enfermedad Profesional">Enfermedad Profesional</option>
                </select>
            </div>
        </div>
    
        <div class="col-3">
            <div class="form-group">
                <label for="tipo{{$idRow}}" class="control-label">Tipo:</label>
                <select class="form-control" id="tipo{{$idRow}}" name="tipo[]">
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
</div>
