<div class="upcAdicionalV" data-id="{{$idRow}}">
    <div class="row">
        <div class="col-10">
            UPC ADICIONAL 
        </div>
        <div class="col-2 text-right">
            <a href="#" class="btn btn-outline-danger quitarUpcAdicional" data-id="{{$idRow}}">Quitar</a>
        </div>
    </div> 
    <input type="hidden" name="idUpcAdicional[]"  value="-1" />
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="primerApellidoUpc{{$idRow}}" class="control-label">Primer Apellido:</label>
                <input type="text" class="form-control" id="primerApellidoUpc{{$idRow}}" data-id="{{$idRow}}" name="primerApellidoUpc[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group @isset($upcAdic->segundoApellido) hasText  @endisset">
                <label for="segundoApellidoUpc{{$idRow}}" class="control-label">Segundo Apellido:</label>
                <input type="text" class="form-control" id="segundoApellidoUpc{{$idRow}}" data-id="{{$idRow}}" name="segundoApellidoUpc[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group @isset($upcAdic->primerNombre) hasText  @endisset">
                <label for="primerNombreUpc{{$idRow}}" class="control-label">Primer Nombre:</label>
                <input type="text" class="form-control" id="primerNombreUpc{{$idRow}}" data-id="{{$idRow}}" name="primerNombreUpc[]"/>                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group @isset($upcAdic->segundoNombre) hasText  @endisset">
                <label for="segundoNombreUpc{{$idRow}}" class="control-label">Segundo Nombre:</label>
                <input type="text" class="form-control" id="segundoNombreUpc{{$idRow}}" data-id="{{$idRow}}" name="segundoNombreUpc[]" />                    
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group @isset($upcAdic->fkTipoIdentificacion) hasText @endisset">
                <label for="tIdentificacionUpc{{$idRow}}" class="control-label">Tipo Identificación</label>
                <select class="form-control" id="tIdentificacionUpc{{$idRow}}" name="tIdentificacionUpc[]">
                    <option value=""></option>
                    @foreach ($tipoidentificacion as $tipoidentificacio)
                        <option value="{{$tipoidentificacio->idtipoIdentificacion}}">{{$tipoidentificacio->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group @isset($upcAdic->numIdentificacion) hasText @endisset">
                <label for="numIdentificacionUpc{{$idRow}}" class="control-label">Número Identificación</label>
                <input type="text" class="form-control" id="numIdentificacionUpc{{$idRow}}" data-id="{{$idRow}}" name="numIdentificacionUpc[]"/>                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group @isset($upcAdic->fechaNacimiento) hasText @endisset">
                <label for="fechaNacimientoUpc{{$idRow}}" class="control-label">Fecha nacimiento</label>
                <input type="date" class="form-control" id="fechaNacimientoUpc{{$idRow}}" data-id="{{$idRow}}" name="fechaNacimientoUpc[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="generoUpc{{$idRow}}" class="control-label">Genero</label>
                <select class="form-control" id="generoUpc{{$idRow}}" name="generoUpc[]">
                    <option value=""></option>
                    @foreach ($generosBen as $genero)
                        <option value="{{$genero->idGenero}}">{{$genero->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="paisUpc{{$idRow}}" class="control-label">País</label>
                <select class="form-control paisUpc" id="paisUpc{{$idRow}}" data-id="{{$idRow}}" name="paisUpc[]" >
                    <option value=""></option>
                    @foreach ($paises as $pais)
                        <option value="{{$pais->idubicacion}}">{{$pais->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="deptoUpc{{$idRow}}" class="control-label">Departamento</label>
                <select class="form-control deptoUpc" id="deptoUpc{{$idRow}}" data-id="{{$idRow}}" name="deptoUpc[]">
                    <option value=""></option>
                   
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="lugarUpc{{$idRow}}" class="control-label">Lugar</label>
                <select class="form-control lugarUpc" id="lugarUpc{{$idRow}}"  data-id="{{$idRow}}" name="lugarUpc[]">
                    <option value=""></option>     
                                      
                </select>
            </div>
        </div>
        @if ($periodo=="15")
            <div class="col-3">
                <div class="form-group hasText">
                    <label for="periocidad{{$idRow}}" class="control-label">Periocidad</label>
                    <select class="form-control periocidad" required id="periocidad{{$idRow}}"  data-id="{{$idRow}}" name="periocidad[]">
                        @foreach ($periocidad as $perio)
                            <option value="{{$perio->per_id}}">{{$perio->per_upc}}</option>
                        @endforeach                                      
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" name="periocidad[]" value="1" />
        @endif
        
    </div>



</div>