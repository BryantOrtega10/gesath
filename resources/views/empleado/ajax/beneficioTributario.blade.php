<div class="beneficioTrib" data-id="{{$num}}">
    <input type="hidden" name="idBeneficioTributario[]" value="-1"/>
    <div class="row">

        <div class="col-12 text-right">
            <a href="#" class="btn btn-outline-danger quitarBeneficio" data-id="{{$num}}">Quitar</a><br><br>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="infoTipoBeneficio{{$num}}" class="control-label">Tipo Beneficio *</label>
                <select class="form-control infoTipoBeneficio" id="infoTipoBeneficio{{$num}}" name="infoTipoBeneficio[]" data-id="{{$num}}">
                    <option value=""></option>
                    @foreach ($tipobeneficio as $tipobene)
                        <option value="{{$tipobene->idTipoBeneficio}}">{{$tipobene->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="infoFechaVigencia{{$num}}" class="control-label">Fecha Vigencia *</label>
                <input type="date" class="form-control" id="infoFechaVigencia{{$num}}" name="infoFechaVigencia[]" value=""/>                    
            </div>
        </div>
        <div class="col-2">
            <div class="infoBeneficioSinPersona activo" data-id="{{$num}}">
                <div class="form-group">
                    <label for="infoValorTotal{{$num}}" class="control-label">Valor total *</label>
                    <input type="text" class="form-control separadorMiles valorTotalBeneficio"  data-id="{{$num}}" id="infoValorTotal{{$num}}" name="infoValorTotal[]" value=""/>                    
                </div>
            </div>
            <div class="infoPersonaBeneficio1" data-id="{{$num}}">
                <div class="form-group">
                    <label for="infoPersonaVive{{$num}}" class="control-label">Persona *</label>
                    <select class="form-control" id="infoPersonaVive{{$num}}" name="infoPersonaVive[]">
                        <option value=""></option>
                        @foreach ($nucleofamiliar as $nucleofam)
                            <option value="{{$nucleofam->idNucleoFamiliar}}">{{$nucleofam->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-2">
            <div class="infoBeneficioSinPersona activo" data-id="{{$num}}">
                <div class="form-group">
                    <label for="infoNumMeses{{$num}}" class="control-label">Num Meses *</label>
                    <input type="text" class="form-control infoNumMesesBeneficio"  data-id="{{$num}}" id="infoNumMeses{{$num}}" name="infoNumMeses[]" value=""/>                    
                </div>
            </div>
            <div class="infoPersonaBeneficio1" data-id="{{$num}}">
                <div class="form-group">
                    <label for="infoTIdentificacion{{$num}}" class="control-label">Tipo Identificación *</label>
                    <select class="form-control" id="infoTIdentificacion{{$num}}" name="infoTIdentificacion[]">
                        <option value=""></option>
                        @foreach ($tipoidentificacion as $tipoidentificacio)
                            <option value="{{$tipoidentificacio->idtipoIdentificacion}}">{{$tipoidentificacio->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>        
        <div class="col-2">
            <div class="infoBeneficioSinPersona activo" data-id="{{$num}}">
                <div class="form-group">
                    <label for="infoValorMensual{{$num}}" class="control-label">Valor mensual *</label>
                    <input type="text" class="form-control separadorMiles infoValorMensual" data-id="{{$num}}" readonly id="infoValorMensual{{$num}}" name="infoValorMensual[]" value=""/>                    
                </div>
            </div>
            <div class="infoPersonaBeneficio1" data-id="{{$num}}">
                <div class="form-group">
                    <label for="infoNumIdentificacion{{$num}}" class="control-label">Número Identificación *</label>
                    <input type="text" class="form-control" id="infoNumIdentificacion{{$num}}" name="infoNumIdentificacion[]" />
                </div>
            </div>
        </div>
    </div>
    <div class="infoPersonaBeneficio2" data-id="{{$num}}">
        <div class="row">
            <div class="col-3">
                <div class="form-group">
                    <label for="info2PersonaVive{{$num}}" class="control-label">Persona *</label>
                    <select class="form-control" id="info2PersonaVive{{$num}}" name="info2PersonaVive[]">
                        <option value=""></option>
                        @foreach ($nucleofamiliar as $nucleofam)
                            <option value="{{$nucleofam->idNucleoFamiliar}}">{{$nucleofam->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="info2TIdentificacion{{$num}}" class="control-label">Tipo Identificación *</label>
                    <select class="form-control" id="info2TIdentificacion{{$num}}" name="info2TIdentificacion[]">
                        <option value=""></option>
                        @foreach ($tipoidentificacion as $tipoidentificacio)
                            <option value="{{$tipoidentificacio->idtipoIdentificacion}}">{{$tipoidentificacio->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="info2NumIdentificacion{{$num}}" class="control-label">Número Identificación *</label>
                    <input type="text" class="form-control" id="info2NumIdentificacion{{$num}}" name="info2NumIdentificacion[]" />
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="info2Genero{{$num}}" class="control-label">Genero *</label>
                    <select class="form-control" id="info2Genero{{$num}}" name="info2Genero[]">
                        <option value=""></option>
                        @foreach ($generos as $genero)
                            <option value="{{$genero->idGenero}}">{{$genero->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <div class="form-group">
                    <label for="info2DireccionPersona{{$num}}" class="control-label">Direccion *</label>
                    <input type="text" class="form-control" id="info2DireccionPersona{{$num}}" data-id="{{$num}}" name="info2DireccionPersona[]" />                    
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="info2PaisPersona{{$num}}" class="control-label">País *</label>
                    <select class="form-control infoPaisPersona" id="info2PaisPersona{{$num}}" data-id="{{$num}}" name="info2PaisPersona[]">
                        <option value=""></option>
                        @foreach ($paises as $pais)
                            <option value="{{$pais->idubicacion}}">{{$pais->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="info2DeptoPersona{{$num}}" class="control-label">Departamento *</label>
                    <select class="form-control infoDeptoPersona" id="info2DeptoPersona{{$num}}" data-id="{{$num}}" name="info2DeptoPersona[]">
                        <option value=""></option>
                        
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="info2LugarPersona{{$num}}" class="control-label">Lugar *</label>
                    <select class="form-control infoLugarPersona" id="info2LugarPersona{{$num}}"  data-id="{{$num}}" name="info2LugarPersona[]">
                        <option value=""></option>                            
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>