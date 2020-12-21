@extends('layouts.admin')
@section('title', 'Crear empleado')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<h1 class="ordenSuperior">Crear empleado</h1>
<nav>
    <div class="navGeneral nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-datosP-tab" data-toggle="tab" href="#nav-datosP" role="tab" aria-controls="nav-datosP" aria-selected="true">Datos Personales</a>
        <a class="nav-item nav-link disabled" id="nav-infoLab-tab" data-toggle="tab" href="#nav-infoLab" role="tab" aria-controls="nav-infoLab" aria-selected="false">Información Laboral</a>
        <a class="nav-item nav-link disabled" id="nav-retencionF-tab" data-toggle="tab" href="#" role="tab" aria-controls="nav-retencionF" aria-selected="false">Afiliaciones</a>
        <a class="nav-item nav-link disabled" data-toggle="tab" href="#" role="tab" aria-controls="nav-ds" aria-selected="false">Conceptos Fijos</a>
    </div>
</nav>
<div class="tab-content" id="nav-tabContent">
    <div class="tabGeneral tab-pane fade show active" id="nav-datosP" role="tabpanel" aria-labelledby="nav-datosP-tab">
        <form method="POST" id="formAgregarEmpleado" autocomplete="off" class="formGeneral" action="/empleado/ingresarDatosBasicos" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-3">
                    
                    <div class="form-group hasText">
                        <label for="tEmpleado" class="control-label">Tipo de empleado</label>
                        <select class="form-control" id="tEmpleado" name="tEmpleado">
                            <option value=""></option>
                            <option value="empleado" @if($tipoEmpleado=="empleado") selected @endif>Empleado</option>
                            <option value="contratista" @if($tipoEmpleado=="contratista") selected @endif>Contratista</option>
                            <option value="aspirante" @if($tipoEmpleado=="aspirante") selected @endif>Aspirante</option>
                        </select>
                    </div>
                </div>
                <div class="col-9 text-right">
                    <div class="contFoto">					
                        <img src="{{ URL::asset('img/foto.png') }}" class="" id="foto" />
                        <input type="file" accept="image/*" id="inputFoto" name="foto">					
                    </div>
                </div>
            </div>
            <section>
                <div class="subTitulo">
                    <h2>Datos personales</h2>
                    <hr />
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="pNombre" class="control-label">Primer Nombre</label>
                            <input type="text" class="form-control" id="pNombre" name="pNombre" required/>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="sNombre" class="control-label">Segundo Nombre</label>
                            <input type="text" class="form-control" id="sNombre" name="sNombre" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="pApellido" class="control-label">Primer Apellido</label>
                            <input type="text" class="form-control" id="pApellido" name="pApellido" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="sApellido" class="control-label">Segundo Apellido</label>
                            <input type="text" class="form-control" id="sApellido" name="sApellido" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="tIdentificacion" class="control-label">Tipo Identificación</label>
                            <select class="form-control" id="tIdentificacion" name="tIdentificacion">
                                <option value=""></option>
                                @foreach ($tipoidentificacion as $tipoidentificacio)
                                    <option value="{{$tipoidentificacio->idtipoIdentificacion}}">{{$tipoidentificacio->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" id="tIdentificacionAnt" value="" name="tIdentificacionAnt" /> 
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="numIdentificacion" class="control-label">Número Identificación</label>
                            <input type="text" class="form-control" id="numIdentificacion" name="numIdentificacion" />
                        </div>
                        <input type="hidden" id="numIdentificacionAnt" value="" name="numIdentificacionAnt" /> 
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="paisExpedicion" class="control-label">País Expedición</label>
                            <select class="form-control" id="paisExpedicion" name="paisExpedicion">
                                <option value=""></option>
                                @foreach ($paises as $pais)
                                    <option value="{{$pais->idubicacion}}">{{$pais->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="deptoExpedicion" class="control-label">Departamento Expedición</label>
                            <select class="form-control" id="deptoExpedicion" name="deptoExpedicion">
                                <option value=""></option>                            
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="lugarExpedicion" class="control-label">Lugar Expedición</label>
                            <select class="form-control" id="lugarExpedicion" name="lugarExpedicion">
                                <option value=""></option>                            
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaExpedicion" class="control-label">Fecha Expedición</label>
                            <input type="date" class="form-control" id="fechaExpedicion" name="fechaExpedicion" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="genero" class="control-label">Genero</label>
                            <select class="form-control" id="genero" name="genero">
                                <option value=""></option>
                                @foreach ($generos as $genero)
                                    <option value="{{$genero->idGenero}}">{{$genero->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="estadoCivil" class="control-label">Estado Civil</label>
                            <select class="form-control" id="estadoCivil" name="estadoCivil">
                                <option value=""></option>
                                @foreach ($estadosCivil as $estadoCivil)
                                    <option value="{{$estadoCivil->idEstadoCivil}}">{{$estadoCivil->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="libretaMilitar" class="control-label">Libreta Militar</label>
                            <input type="text" class="form-control" id="libretaMilitar" name="libretaMilitar" />
                        </div>
                    </div>
            
                    <div class="col-3">
                        <div class="form-group">
                            <label for="distritoMilitar" class="control-label">Distrito Militar - Clase</label>
                            <input type="text" class="form-control" id="distritoMilitar" name="distritoMilitar" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="nivelEstudio" class="control-label">Nivel estudios</label>
                            <select class="form-control" id="nivelEstudio" name="nivelEstudio">
                                <option value=""></option>
                                @foreach ($nivelesEstudios as $nivelEstudio)
                                    <option value="{{$nivelEstudio->idNivelEstudio}}">{{$nivelEstudio->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group hasText">
                            <label for="etnia" class="control-label">Etnia</label>
                            <select class="form-control" id="etnia" name="etnia">
                                @foreach ($etnias as $etnia)
                                    <option value="{{$etnia->idEtnia}}">{{$etnia->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </section>
            <section>
                <div class="subTitulo">
                    <h2>Fecha y Lugar Nacimiento</h2>
                    <hr />
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaNacimiento" class="control-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="paisNacimiento" class="control-label">País Nacimiento</label>
                            <select class="form-control" id="paisNacimiento" name="paisNacimiento">
                                <option value=""></option>
                                @foreach ($paises as $pais)
                                    <option value="{{$pais->idubicacion}}">{{$pais->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="deptoNacimiento" class="control-label">Departamento Nacimiento</label>
                            <select class="form-control" id="deptoNacimiento" name="deptoNacimiento">
                                <option value=""></option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="lugarNacimiento" class="control-label">Lugar Nacimiento</label>
                            <select class="form-control" id="lugarNacimiento" name="lugarNacimiento">
                                <option value=""></option>
                                
                            </select>
                        </div>
                    </div>
                </div>
            </section> 
            <section>
                <div class="subTitulo">
                    <h2>Informaci&oacute;n Residencia</h2>
                    <hr />
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="direccion" class="control-label">Direccion</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="paisResidencia" class="control-label">País</label>
                            <select class="form-control" id="paisResidencia" name="paisResidencia">
                                <option value=""></option>
                                @foreach ($paises as $pais)
                                    <option value="{{$pais->idubicacion}}">{{$pais->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="deptoResidencia" class="control-label">Departamento</label>
                            <select class="form-control" id="deptoResidencia" name="deptoResidencia">
                                <option value=""></option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="lugarResidencia" class="control-label">Lugar</label>
                            <select class="form-control" id="lugarResidencia" name="lugarResidencia">
                                <option value=""></option>                            
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="barrio" class="control-label">Barrio</label>
                            <input type="text" class="form-control" id="barrio" name="barrio" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="estrato" class="control-label">Estrato</label>
                            <input type="text" class="form-control" id="estrato" name="estrato" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="tipoVivienda" class="control-label">Tipo vivienda</label>
                            <select class="form-control" id="tipoVivienda" name="tipoVivienda">
                                <option value=""></option>        
                                @foreach ($tipo_vivienda as $tipo_viv)
                                    <option value="{{$tipo_viv->idTipoVivienda}}">{{$tipo_viv->nombre}}</option>
                                @endforeach                    
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="telFijo" class="control-label">Telefono fijo</label>
                            <input type="text" class="form-control" id="telFijo" name="telFijo" />                    
                        </div>
                    </div>
                </div>
                <div class="row">                
                    <div class="col-3">
                        <div class="form-group">
                            <label for="celular" class="control-label">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular" />                    
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="correo1" class="control-label">Correo principal</label>
                            <input type="email" class="form-control" id="correo1" name="correo1" />                    
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="correo2" class="control-label">Correo secundario</label>
                            <input type="email" class="form-control" id="correo2" name="correo2" />                    
                        </div>
                    </div>


                </div>
                


            </section>
            <section>
                <div class="subTitulo">
                    <h2>Informaci&oacute;n salud ocupacional</h2>
                    <hr />
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="grupoSanguineo" class="control-label">Grupo Sanguineo</label>
                            <select class="form-control" id="grupoSanguineo" name="grupoSanguineo">
                                <option value=""></option>        
                                @foreach ($grupoSanguineo as $grupoSang)
                                    <option value="{{$grupoSang->idGrupoSanguineo}}">{{$grupoSang->nombre}}</option>
                                @endforeach                    
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="rh" class="control-label">RH</label>
                            <select class="form-control" id="rh" name="rh">
                                <option value=""></option>        
                                @foreach ($rhs as $rh)
                                    <option value="{{$rh->idRh}}">{{$rh->nombre}}</option>
                                @endforeach                    
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="tallaCamisa" class="control-label">Talla Camisa</label>
                            <input type="text" class="form-control" id="tallaCamisa" name="tallaCamisa" />                    
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="tallaPantalon" class="control-label">Talla Pantalon</label>
                            <input type="text" class="form-control" id="tallaPantalon" name="tallaPantalon" />                    
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="tallaZapatos" class="control-label">Talla Zapatos</label>
                            <input type="text" class="form-control" id="tallaZapatos" name="tallaZapatos" />                    
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="otros" class="control-label">Otros, Cual?</label>
                            <input type="text" class="form-control" id="otros" name="otros" />                    
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="tallaOtros" class="control-label">Talla Otros</label>
                            <input type="text" class="form-control" id="tallaOtros" name="tallaOtros" />                    
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Contacto(s) de emergencia</h4>
                        <div class="contMasMenos">
                            <div class="mas masContactoEmer" data-num="1" data-para="emergencia">+</div>
                        </div>
                    </div>
                </div>
                <div class="emergenciaCont">
                    <div class="emergencia" data-id="1">
                        <input type="hidden" name="idContactoEmergencia[]" value="-1" />
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="nombreEmergencia1" class="control-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombreEmergencia1" data-id="1" name="nombreEmergencia[]" />                    
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="telefonoEmergencia1" class="control-label">Telefono</label>
                                    <input type="text" class="form-control" id="telefonoEmergencia1" data-id="1" name="telefonoEmergencia[]" />                    
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="direccionEmergencia1" class="control-label">Direccion</label>
                                    <input type="text" class="form-control" id="direccionEmergencia1" data-id="1" name="direccionEmergencia[]" />                    
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="paisEmergencia1" class="control-label">País</label>
                                    <select class="form-control paisEmergencia" id="paisEmergencia1" data-id="1" name="paisEmergencia[]">
                                        <option value=""></option>
                                        @foreach ($paises as $pais)
                                            <option value="{{$pais->idubicacion}}">{{$pais->nombre}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="deptoEmergencia1" class="control-label">Departamento</label>
                                    <select class="form-control deptoEmergencia" id="deptoEmergencia1" data-id="1" name="deptoEmergencia[]">
                                        <option value=""></option>
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="lugarEmergencia1" class="control-label">Lugar</label>
                                    <select class="form-control lugarEmergencia" id="lugarEmergencia1"  data-id="1" name="lugarEmergencia[]">
                                        <option value=""></option>                            
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Personas con quien vive</h4>
                        <div class="contMasMenos">
                            <div class="mas masPersonasVive" data-num="0" data-para="personaVive">+</div>
                        </div>
                    </div>
                </div>
                <div class="personaViveCont"></div>
            </section>
            <div class="alert alert-danger" role="alert" id="infoErrorForm" style="display: none;"></div>
            <div class="text-center"><input type="button" value="SIGUIENTE" class="btnSubmitGen" /></div>
        </form>

    </div>
    <div class="tab-pane fade" id="nav-infoLab" role="tabpanel" aria-labelledby="nav-infoLab-tab">

    </div>
    <div class="tab-pane fade" id="nav-retencionF" role="tabpanel" aria-labelledby="nav-retencionF-tab">

    </div>
</div>
<div class="modal fade" id="camposVaciosModal" tabindex="-1" role="dialog" aria-labelledby="camposVaciosModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="camposVaciosText">
                    <h4>Aun cuenta con campos vacios desea continuar?</h4>
                    <div class="text-center">
                        <a href="#" data-accion="" data-form="" id="btnContinuarCamposVacios" class="btn btn-secondary">Continuar</a>
                        <a data-dismiss="modal" class="btn btn-primary" href="#">Volver</a>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mensajeEmpleadoModal" tabindex="-1" role="dialog" aria-labelledby="mensajeEmpleadoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div id="respMensaje"></div>                
                <div class="text-center">
                    <a href="#" class="btn btn-primary" id="aceptarMensajeEmpleado">Continuar</a>
                    <a data-dismiss="modal" class="btn btn-secondary" href="#">Volver</a>
                </div>                    
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="errorEmpleadoModal" tabindex="-1" role="dialog" aria-labelledby="errorEmpleadoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div id="respError"></div>                
                <div class="text-center">
                    <a data-dismiss="modal" class="btn btn-secondary" href="#">Aceptar</a>
                </div>                    
                
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('js/empleado/empleado.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/empleado/datosEmpleado.js') }}"></script>
@endsection
