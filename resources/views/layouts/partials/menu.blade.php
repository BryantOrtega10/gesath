<div class="interiorMenu">
    <div class="headMenu">
        <div class="openMenu"></div>
        @if (isset($dataUsu))
            <div class="perfilPersona">
                <span class="nombreUsuario">
                    Hola {{ $dataUsu->primerNombre }} {{ $dataUsu->primerApellido }}
                </span>
                <img src="/storage/imgEmpleados/{{ $dataUsu->foto }}" />
            </div>
        @else
            <div class="perfilPersona">
                <span class="nombreUsuario">
                    Hola Andres12
                </span>
                <img src="{{ URL::asset('img/menu/personaDefecto.png') }}" />
            </div>
        @endif
    </div>
    <ul class="itemsMenu">
        <li class="{{ Request::is('empleado') ? 'active' : '' }}">
            <a href="/empleado" >
                <img src="{{ URL::asset('img/menu/hojaVida.png') }}" />
                <span class="textoMenu">Hoja de vida</span>
            </a>
            <ul class="subMenu">
                <li class="{{ Request::is('empleado') ? 'active' : '' }}">
                    <a href="/empleado" >
                        <span class="textoMenu">Empleados</span>
                    </a>
                </li>
                <li>
                    <a href="/empleado/cargarEmpleadosMasivaIndex" >
                        <span class="textoMenu">Carga Masiva de Empleados</span>
                    </a>
                </li>
                <li>
                    <a href="/empleado/reintegro" >
                        <span class="textoMenu">Reintegro Empleados</span>
                    </a>
                </li>
                <li>
                    <a href="/empleado/" >
                        <span class="textoMenu">Carga Masiva de Reintegro Empleados</span>
                    </a>
                </li>
                <li>
                    <a href="/empleado/subirFotos/" >
                        <span class="textoMenu">Cargar Fotos Masivo</span>
                    </a>
                </li>
                <li>
                    <a href="/nomina/cambiarConceptosFijos/" >
                        <span class="textoMenu">Incrementos de Salario y Conceptos Fijos</span>
                    </a>
                </li>                
            </ul>
        </li>
        <li class="{{ Request::is('nomina') ? 'active' : '' }} {{ Request::is('novedades') ? 'active' : '' }}">
            <a href="/novedades/cargarNovedades/" >
                <img src="{{ URL::asset('img/menu/nomina.png') }}" />
                <span class="textoMenu">Nómina</span>
            </a>
            <ul class="subMenu">
                <li>
                    <a href="/novedades/cargarNovedades/" >
                        <span class="textoMenu">Cargar Novedades</span>
                    </a>
                </li>
                <li>
                    <a href="/novedades/listaNovedades/" >
                        <span class="textoMenu">Lista de Novedades</span>
                    </a>
                </li>
                <li>
                    <a href="/nomina/solicitudLiquidacion/" >
                        <span class="textoMenu">Solicitudes Liquidaci&oacute;n</span>
                    </a>
                </li>
                <li>
                    <a href="/nomina/nominasLiquidadas/" >
                        <span class="textoMenu">Liquidaciones terminadas</span>
                    </a>
                </li>
                <li>
                    <a href="/novedades/seleccionarArchivoMasivoNovedades/" >
                        <span class="textoMenu">Carga Masiva Novedades</span>
                    </a>
                </li>
                <li>
                    <a href="/nomina/cierre/" >
                        <span class="textoMenu">Generar cierre</span>
                    </a>
                </li>
                <li>
                    <a href="/catalogo-contable/" >
                        <span class="textoMenu">Catalogo contable</span>
                    </a>
                </li>
                <li>
                    <a href="/nomina/centroCostoPeriodo/" >
                        <span class="textoMenu">Centros de costo por periodo</span>
                    </a>
                </li>
                
                <li>
                    <a href="/datosPasados/" >
                        <span class="textoMenu">Cargar Datos pasados</span>
                    </a>
                </li>
                <li>
                    <a href="/datosPasadosVac/" >
                        <span class="textoMenu">Cargar VAC/LRN pasadas</span>
                    </a>
                </li>
                
                <li>
                    <a href="/datosPasadosSal/" >
                        <span class="textoMenu">Cargar saldos</span>
                    </a>
                </li>
                <li>
                    <a href="/prestamos/" >
                        <span class="textoMenu">Prestamos y Embargos</span>
                    </a>
                </li>
                
            </ul>
        </li>
        <li class="{{ Request::is('reportes') ? 'active' : '' }}">
            <a href="/reportes/reporteNominaHorizontal/" >
                <img src="{{ URL::asset('img/menu/reportes.png') }}" />
                <span class="textoMenu">Reportes</span>
            </a>
            <ul class="subMenu">
                <li>
                    <a href="/catalogo-contable/reporteNomina/" >
                        <span class="textoMenu">Reporte contable de nomina</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/reporteNominaHorizontal/" >
                        <span class="textoMenu">Reporte nomina horizontal</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/reporteNominaAcumulado/" >
                        <span class="textoMenu">Reporte concepto acumulado</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/reportePorEmpleado/" >
                        <span class="textoMenu">Reporte por empleado</span>
                    </a>
                </li>                
                <li>
                    <a href="/reportes/seleccionarDocumentoSeguridad/" >
                        <span class="textoMenu">Documento Seguridad Social</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/seleccionarDocumentoProvisiones/" >
                        <span class="textoMenu">Reporte de provisiones</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/indexReporteVacaciones/" >
                        <span class="textoMenu">Reporte de vacaciones</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/formulario220/" >
                        <span class="textoMenu">Formulario 220</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/novedades/" >
                        <span class="textoMenu">Reporte de novedades</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/prestamos/" >
                        <span class="textoMenu">Reporte de prestamos</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/envioCorreosReporte/" >
                        <span class="textoMenu">Enviar Correos</span>
                    </a>
                </li>
                <li>
                    <a href="/reportes/reporteador/" >
                        <span class="textoMenu">Reporteador</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="{{ Request::is('ubicacion') ? 'active' : '' }} {{ Request::is('variables') ? 'active' : '' }} {{ Request::is('concepto') ? 'active' : '' }} {{ Request::is('grupoConcepto') ? 'active' : '' }}">
            <a href="/ubicacion/" >
                <img src="{{ URL::asset('img/menu/parametrizacion.png') }}" />
                <span class="textoMenu">Parametrizaci&oacute;n</span>
            </a>
            <ul class="subMenu">
                <li  class="{{ Request::is('ubicacion') ? 'active' : '' }}">
                    <a href="/ubicacion/">
                        <span class="textoMenu">Ubicaci&oacute;n</span>
                    </a>
                </li>
                <li  class="{{ Request::is('terceros') ? 'active' : '' }}">
                    <a href="/terceros/">
                        <span class="textoMenu">Terceros</span>
                    </a>
                </li>
                <li  class="{{ Request::is('empresa') ? 'active' : '' }}">
                    <a href="/empresa/">
                        <span class="textoMenu">Empresa</span>
                    </a>
                </li>
                <li  class="{{ Request::is('variables') ? 'active' : '' }}">
                    <a href="/variables/">
                        <span class="textoMenu">Variables</span>
                    </a>
                </li>
                <li  class="{{ Request::is('concepto') ? 'active' : '' }}">
                    <a href="/concepto/" >
                        <span class="textoMenu">Conceptos</span>
                    </a>
                </li>
                <li  class="{{ Request::is('grupoConcepto') ? 'active' : '' }}">
                    <a href="/grupoConcepto/">
                        <span class="textoMenu">Grupo Concepto</span>
                    </a>
                </li>
                <li  class="{{ Request::is('cargos') ? 'active' : '' }}">
                    <a href="/cargos/">
                        <span class="textoMenu">Cargos</span>
                    </a>
                </li>
                <li>
                    <a href="/formulario220/" >
                        <span class="textoMenu">Admin Formulario 220</span>
                    </a>
                </li>
                <li>
                    <a href="/calendario/" >
                        <span class="textoMenu">Calendario</span>
                    </a>
                </li>
                <li>
                    <a href="/mensajes/" >
                        <span class="textoMenu">Admin Mensajes General</span>
                    </a>
                </li>
                <li>
                    <a href="/smtpGeneral/" >
                        <span class="textoMenu">SMTP General</span>
                    </a>
                </li>
                <li  class="{{ Request::is('codigos') ? 'active' : '' }}">
                    <a href="/codigos/">
                        <span class="textoMenu">Códigos diagnóstico</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="{{ Request::is('usuarios') ? 'active' : '' }}">
            <a href="/usuarios" >
                <img src="{{ URL::asset('img/menu/usuarios.png') }}" />
                <span class="textoMenu">Usuarios</span>
            </a>
            <ul class="subMenu">
                <li>
                    <a href="/usuarios" >
                        <span class="textoMenu">Lista usuarios</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="cerrar_sesion">
            <a href="/logout" >
                <i class="fas fa-sign-out-alt"></i>
                <span class="textoMenu">Cerrar sesión</span>
            </a>
        </li>

    </ul>

</div>
