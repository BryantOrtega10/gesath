<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ 'uses' => 'InicioController@index', 'as' => '/']);

Route::post('/iniciarSesion', 'InicioController@iniciarSesion');

Route::group([
	'prefix' => 'variables',
	'middleware' => ['auth', 'guest:2,3'],
], function(){
	Route::get('','VariableController@index');
	Route::get('getForm/add', 'VariableController@getFormAdd');
	Route::get('getForm/getTipoCampo/{id}', 'VariableController@getTipoCampo');
	Route::post('crear','VariableController@insert');
	Route::get('getForm/edit/{id}', 'VariableController@getFormEdit');
	Route::post('modificar','VariableController@update');
	Route::get('getFormulaVariable', 'VariableController@getFormulaVariableAdd');
	Route::get('getFormulaVariable/masFormulas/{id}', 'VariableController@getFormulaVariableMas');
	Route::post('getFormulaVariable/llenarVariable', 'VariableController@fillVariable');
});

Route::group([
	'prefix' => 'ubicacion',
	'middleware' => ['auth', 'guest:2,3'],
], function(){
	Route::get('','UbicacionController@index');
	Route::get('getForm/add', 'UbicacionController@getFormAdd');
	Route::post('crear','UbicacionController@insert');
	Route::get('getForm/edit/{id}', 'UbicacionController@getFormEdit');
	Route::post('modificar','UbicacionController@update');
	Route::get('cambioTUbicacion/{id}', 'UbicacionController@cambioTUbicacion');
	Route::get('/obtenerHijos/{id}', 'UbicacionController@obtenerSubUbicaciones');
});

Route::get('/ubicaciones/obtenerHijos/{id}', 'UbicacionController@obtenerSubUbicaciones');


Route::group([
	'prefix' => 'concepto',
	'middleware' => ['auth', 'guest:2,3'],
], function(){
	Route::get('','ConceptoController@index');
	Route::get('getForm/add', 'ConceptoController@getFormAdd');
	Route::post('crear','ConceptoController@insert');
	Route::get('getFormulaConcepto', 'ConceptoController@getFormulaConceptoAdd');
	Route::get('getFormulaConcepto/masFormulas/{id}', 'ConceptoController@getFormulaConceptoMas');
	Route::post('getFormulaConcepto/llenar', 'ConceptoController@fillFormula');
	Route::get('condiciones/{id}', 'CondicionController@index');
	Route::get('condiciones/getForm/add/{id}', 'CondicionController@getFormAdd');
	Route::get('condiciones/camposOperador/{id}', 'CondicionController@camposOperador');
	Route::get('condiciones/masItems/{id}', 'CondicionController@masItems');
	Route::post('condiciones/agregarCondicion', 'CondicionController@insert');
	Route::get('getForm/edit/{id}', 'ConceptoController@getFormEdit');
	Route::get('getForm/copy/{id}', 'ConceptoController@getFormCopy');

	Route::get('getFormulaConcepto/{id}', 'ConceptoController@getFormulaConceptoMod');
	Route::post('modificar','ConceptoController@update');
	Route::post('copiar','ConceptoController@copy');

	
});
Route::group([
	'prefix' => 'grupoConcepto',
	'middleware' => ['auth', 'guest:2,3'],
], function(){
	Route::get('','GrupoConceptoController@index');
	Route::get('getForm/add', 'GrupoConceptoController@getFormAdd');
	Route::post('crear','GrupoConceptoController@insert');
	Route::get('getForm/masConceptos/{id}', 'GrupoConceptoController@getMasConceptos');
	
	Route::get('/edit/{id}', "GrupoConceptoController@edit");
	Route::get('/detail/{id}', "GrupoConceptoController@detail");
	Route::post('/update/{id}', "GrupoConceptoController@update");
	Route::post('/delete/{id}', "GrupoConceptoController@delete");
	/*
	Route::get('getForm/edit/{id}', 'UbicacionController@getFormEdit');
	Route::post('modificar','UbicacionController@update');
	*/
});
Route::group([
	'prefix' => 'empleado',
	'middleware' => ['auth', 'guest:2,3'],
	'as' => 'empleado'
], function(){
	Route::get('/', [ 'uses' => 'EmpleadoController@index', 'as' => '/']);
	Route::get('formCrear/{id}','EmpleadoController@formCrear');
		
	Route::get('cargarPersonasVive/{id}','EmpleadoController@cargarPersonasVive');
	Route::get('cargarUpcAdicional/{id}','EmpleadoController@cargarUpcAdicional');
	
	Route::get('cargarContactoEmergencia/{id}','EmpleadoController@cargarContactoEmergencia');
	Route::post('ingresarDatosBasicos','EmpleadoController@insert');
	Route::get('formModificar/{id}','EmpleadoController@formModificar');
	Route::post('verificarDocumento','EmpleadoController@verificarDocumento');
	Route::get('cargarCentroCosto/','EmpleadoController@cargarCentroCosto');
	Route::get('cargarBeneficiosTributarios/{id}/{idEmpleado}','EmpleadoController@cargarBeneficiosTributarios');
	Route::get('cargarDatosPorEmpresa/{id}','EmpleadoController@cargarDatosPorEmpresa');
	Route::post('agregarDatosInfoPersonalSinEmpresa','EmpleadoController@addDatosInfoPersonalSinEmpresa');
	Route::get('cargarAfiliaciones/{id}','EmpleadoController@cargarAfiliaciones');
	Route::get('cargarEntidadesAfiliacion/{id}','EmpleadoController@cargarEntidadesAfiliacion');
	Route::post('afiliacionesEmpleado','EmpleadoController@ingresarAfiliacionesEmpleado');
	Route::get('cargarConceptosFijos/{id}','EmpleadoController@cargarConceptosFijos');
	Route::post('conceptosFijos','EmpleadoController@validarConceptosFijos');
	Route::get('validarEstadoEmpleado/{id}','EmpleadoController@validarEstadoEmpleado');
	Route::get('cargarFormEmpleadosxNomina','EmpleadoController@cargarFormEmpleadosxNomina');

	Route::get('cargarEmpleadosMasivaIndex','EmpleadoController@cargarEmpleadosMasivaIndex');
	Route::post('cargaMasivaEmpleados','EmpleadoController@cargaMasivaEmpleados');
	Route::get('cargaEmpleados/{id}', 'EmpleadoController@cargaEmpleados');
	Route::get('subirEmpleadosCsv/{id}', 'EmpleadoController@subirEmpleadosCsv');

	Route::post('modificarDatosBasicos','EmpleadoController@modificarDatosBasicos');
	Route::get('formVer/{id}','EmpleadoController@formVer');	
	Route::get('mostrarPorqueFalla/{id}','EmpleadoController@mostrarPorqueFalla');	

	Route::post('modificarDatosInfoPersonal','EmpleadoController@modificarDatosInfoPersonal');
	
	Route::get('desactivarEmpleado/{id}','EmpleadoController@desactivarEmpleado');
	Route::get('reactivarEmpleado/{id}','EmpleadoController@reactivarEmpleado');
	Route::get('eliminarDefUsuario/{id}','EmpleadoController@eliminarDefUsuario');
	
	Route::get('/CSVEmpleados', 'EmpleadoController@CSVEmpleados');

	Route::get('/dataEmpContrasenia/{id}', 'EmpleadoController@getDataPass');
	
});

Route::group([
	'prefix' => 'nomina',
	'middleware' => ['auth', 'guest:2,3'],
], function(){
	Route::get('cargarFechaxNomina/{id}','NominaController@cargarFechaxNomina');
	Route::get('solicitudLiquidacion','NominaController@solicitudLiquidacion');
	Route::get('agregarSolicitudLiquidacion','NominaController@agregarSolicitudLiquidacion');
	Route::get('cargarFechaPagoxNomina/{id}/{tipoLiq}','NominaController@cargarFechaPagoxNomina');
	Route::get('cargarEmpleadosxNomina/{id}/{tipoNomina}','NominaController@cargarEmpleadosxNomina');
	Route::post('insertarSolicitud','NominaController@insertarSolicitud');
	Route::get('verSolicitudLiquidacion/{id}','NominaController@verSolicitudLiquidacion');
	Route::get('recalcularBoucher/{id}','NominaController@recalcularBoucher');
	Route::get('cargarInfoxBoucher/{id}','NominaController@cargarInfoxBoucher');
	Route::post('aprobarSolicitud','NominaController@aprobarSolicitud');
	Route::post('cancelarSolicitud','NominaController@cancelarSolicitud');
	Route::get('comoCalculo/{id}','NominaController@comoCalculo');
	Route::get('verDetalleRetencion/{id}/{tipo}','NominaController@verDetalleRetencion');
	Route::get('nominasLiquidadas','NominaController@nominasLiquidadas');
	Route::get('documentoRetencion/{id}','NominaController@documentoRetencion');
	Route::get('documentoSS/{id}','NominaController@documentoSS');
	
	
	Route::get('recalcularNomina/{id}','NominaController@recalcularNomina');

	
	Route::get('reversar/{id}','NominaController@reversar');
	Route::get('cierre','NominaController@indexCierre');
	Route::post('generarCierre','NominaController@generarCierre');
	Route::get('verSolicitudLiquidacionSinEdit/{id}','NominaController@verSolicitudLiquidacionSinEdit');	
	Route::get('centroCostoPeriodo','NominaController@centroCostoPeriodo');




	Route::group(['prefix' => 'distri'], function(){
		Route::get('add','NominaController@centroCostoPeriodoFormAdd');
		Route::post('crear','NominaController@insertDistri');
		
		Route::get('modificarDistri/{idDistri}','NominaController@modificarDistriIndex');
		Route::get('editarDistriEm/{idEmp}/{idDistri}','NominaController@editarDistriEm');
		Route::post('modDistriEmp','NominaController@modDistriEmp');
		Route::post('modificarDistribucion','NominaController@modificarDistribucion');

		Route::get('copiarDistri/{idDistri}','NominaController@copiarDistri');
		Route::post('copiar','NominaController@copyDistriBd');
		Route::post('subirPlano','NominaController@subirPlano');
		
	});	
});
Route::group([
	'prefix' => 'reportes',
	'middleware' => ['auth', 'guest:2,3'],
], function(){
	Route::get('documentoNominaHorizontal/{idNomina}','ReportesNominaController@documentoNominaHorizontal');
	Route::get('reporteNominaAcumulado','ReportesNominaController@reporteNominaAcumuladoIndex');
	Route::post('documentoNominaFechas','ReportesNominaController@documentoNominaFechas');
	
	Route::get('reporteNominaHorizontal','ReportesNominaController@reporteNominaHorizontalIndex');
	Route::post('documentoNominaHorizontalFechas','ReportesNominaController@documentoNominaHorizontalFechas');
	Route::get('boucherPdf/{idBoucher}','ReportesNominaController@boucherPagoPdf');	
	Route::post('documentoSSTxt','ReportesNominaController@documentoSSTxt');	
	Route::post('documentoProv','ReportesNominaController@documentoProv');
	Route::get('seleccionarDocumentoSeguridad','ReportesNominaController@seleccionarDocumentoSeguridad');
	Route::get('seleccionarDocumentoProvisiones','ReportesNominaController@seleccionarDocumentoProvisiones');

	Route::get('indexReporteVacaciones','ReportesNominaController@indexReporteVacaciones');
	Route::post('reporteVacaciones','ReportesNominaController@reporteVacaciones');
});



Route::group([
	'prefix' => 'datosPasados',
	'middleware' => ['auth', 'guest:2,3'],
	], function(){
	Route::get('/','DatosPasadosController@index');
	Route::post('/subirArchivo','DatosPasadosController@subirArchivo');

	
	Route::get('/verCarga/{idCarga}','DatosPasadosController@verCarga');
	Route::get('/subir/{idCarga}','DatosPasadosController@subir');

	
	Route::get('/cancelarCarga/{idCarga}','DatosPasadosController@cancelarCarga');
	Route::post('/eliminarRegistros','DatosPasadosController@eliminarRegistros');
	Route::get('/aprobarCarga/{idCarga}','DatosPasadosController@aprobarCarga');
	
	
});

Route::group([
	'prefix' => 'datosPasadosVac',
	'middleware' => ['auth', 'guest:2,3'],
	], function(){
	Route::get('/','DatosPasadosController@indexVac');
	Route::post('/subirArchivo','DatosPasadosController@subirArchivoVac');

	
	Route::get('/verCarga/{idCarga}','DatosPasadosController@verCargaVac');
	Route::get('/subir/{idCarga}','DatosPasadosController@subirVac');

	
	Route::get('/cancelarCarga/{idCarga}','DatosPasadosController@cancelarCargaVac');
	Route::post('/eliminarRegistros','DatosPasadosController@eliminarRegistrosVac');
	Route::get('/aprobarCarga/{idCarga}','DatosPasadosController@aprobarCargaVac');
	
	
});

Route::group([
	'prefix' => 'datosPasadosSal',
	'middleware' => ['auth', 'guest:2,3'],
	], function(){
	Route::get('/','DatosPasadosController@indexSal');
	Route::post('/subirArchivo','DatosPasadosController@subirArchivoSal');

	
	Route::get('/verCarga/{idCarga}','DatosPasadosController@verCargaSal');
	Route::get('/subir/{idCarga}','DatosPasadosController@subirSal');

	
	Route::get('/cancelarCarga/{idCarga}','DatosPasadosController@cancelarCargaSal');
	Route::post('/eliminarRegistros','DatosPasadosController@eliminarRegistrosSal');
	Route::get('/aprobarCarga/{idCarga}','DatosPasadosController@aprobarCargaSal');
	
	
});
Route::group([
	'prefix' => 'novedades',
	'middleware' => ['auth', 'guest:2,3'],
], function(){
	Route::get('cargarNovedades','NovedadesController@index');
	Route::get('cargarFormxTipoNov/{id}','NovedadesController@cargarFormxTipoNov');
	Route::post('cargarFormNovedadesxTipo','NovedadesController@cargarFormxTipoReporte');	
	Route::get('tipoAfiliacionxConcepto/{tipoNovedad}/{concepto}','NovedadesController@tipoAfiliacionxConcepto');
	Route::get('entidadxTipoAfiliacion/{tipoAfiliacion}/{idEmpleado}','NovedadesController@entidadxTipoAfiliacion');	
	
	Route::get('fechaConCalendario','NovedadesController@fechaConCalendario');	

	Route::post('insertarNovedadHoraTipo1','NovedadesController@insertarNovedadHoraTipo1');
	Route::post('insertarNovedadHoraTipo2','NovedadesController@insertarNovedadHoraTipo2');
	Route::post('insertarNovedadIncapacidad','NovedadesController@insertarNovedadIncapacidad');
	Route::post('insertarNovedadLicencia','NovedadesController@insertarNovedadLicencia');
	Route::post('insertarNovedadAusencia1','NovedadesController@insertarNovedadAusencia1');
	Route::post('insertarNovedadAusencia2','NovedadesController@insertarNovedadAusencia2');
	Route::post('insertarNovedadRetiro','NovedadesController@insertarNovedadRetiro');
	Route::post('insertarNovedadOtros','NovedadesController@insertarNovedadOtros');
	Route::post('insertarNovedadVacaciones','NovedadesController@insertarNovedadVacaciones');
	Route::post('insertarNovedadVacaciones2','NovedadesController@insertarNovedadVacaciones2');
	
	Route::get('listaNovedades','NovedadesController@lista');
	Route::get('modificarNovedad/{id}', 'NovedadesController@modificarNovedad');
	Route::get('eliminarNovedad/{id}', 'NovedadesController@eliminarNovedad');

	Route::get('eliminarNovedadDef/{id}', 'NovedadesController@eliminarNovedadDef');
	
	Route::post('modificarNovedadAusencia1','NovedadesController@modificarNovedadAusencia1');
	Route::post('modificarNovedadLicencia','NovedadesController@modificarNovedadLicencia');
	Route::post('modificarNovedadIncapacidad','NovedadesController@modificarNovedadIncapacidad');
	Route::post('modificarNovedadHoraExtra1','NovedadesController@modificarNovedadHoraExtra1');
	Route::post('modificarNovedadHoraExtra2','NovedadesController@modificarNovedadHoraExtra2');
	Route::post('modificarNovedadRetiro','NovedadesController@modificarNovedadRetiro');
	Route::post('modificarNovedadVacaciones','NovedadesController@modificarNovedadVacaciones');
	Route::post('modificarNovedadVacaciones2','NovedadesController@modificarNovedadVacaciones2');
	Route::post('modificarNovedadOtros','NovedadesController@modificarNovedadOtros');
	
	Route::post('eliminarSeleccionados','NovedadesController@eliminarSeleccionados');
	Route::post('eliminarSeleccionadosDef','NovedadesController@eliminarSeleccionadosDef');

	Route::post('cargaMasivaNovedades','NovedadesController@cargaMasivaNovedades');
	Route::get('seleccionarArchivoMasivoNovedades','NovedadesController@seleccionarArchivoMasivoNovedades');
	Route::get('verCarga/{id}','NovedadesController@verCarga');

	Route::get('cancelarSubida/{id}','NovedadesController@cancelarSubida');
	Route::get('aprobarSubida/{id}','NovedadesController@aprobarSubida');
});

Route::group(['prefix' => 'catalogo-contable', 'middleware' => ['auth', 'guest:2,3']], function() {
	Route::get('/', 'CatalogoContableController@index');
	Route::get("/getForm/add", 'CatalogoContableController@getFormAdd');
	Route::get("/getForm/edit/{id}", 'CatalogoContableController@getFormEdit');
	Route::post("/crear", 'CatalogoContableController@crear');
	Route::post("/modificar", 'CatalogoContableController@modificar');
	
	Route::get('/reporteNomina', 'CatalogoContableController@reporteNominaIndex');
	Route::post('/generarReporteNomina', 'CatalogoContableController@generarReporteNomina');
	Route::get('/getCentrosCosto/{idEmpresa}', 'CatalogoContableController@getCentrosCosto');
	Route::get('/getGrupos/{num}', 'CatalogoContableController@getGrupos');

	Route::get('/subirPlano', 'CatalogoContableController@indexPlano');
	Route::post('/subirArchivoPlano', 'CatalogoContableController@subirArchivoPlano');
	Route::get('/verCarga/{id}', 'CatalogoContableController@verCarga');
	Route::get('/subirDatosCuenta/{id}', 'CatalogoContableController@subirDatosCuenta');
	Route::get('/cancelarCarga/{id}', 'CatalogoContableController@cancelarCarga');
	Route::get('/aprobarCarga/{id}', 'CatalogoContableController@aprobarCarga');
	Route::post('/eliminarRegistros', 'CatalogoContableController@eliminarRegistros');
	
	
});


Route::group([
	'prefix' => 'varios',
	'middleware' => ['auth', 'guest:2,3'],
], function(){	
	Route::get('codigosDiagnostico','VariosController@codigosDiagnostico');	
});

Route::group([
	'prefix' => 'terceros',
	'middleware' => ['auth', 'guest:2,3'],
], function() {
	Route::get('/', 'TercerosController@index');
	Route::get('/getForm/add', 'TercerosController@getFormAdd');
	Route::post('/agregarTercero', 'TercerosController@create');
	Route::get('/datosTerceroXId/{id}', 'TercerosController@edit');
	Route::get('/detalleTercero/{id}', 'TercerosController@detail');
	Route::post('/editarTercero/{id}', 'TercerosController@update');
	Route::post('/eliminarTercero/{id}', 'TercerosController@delete');
	Route::get('/ubiTercero/newUbi/{idDom}', 'TercerosController@ubiTerceroDOM');
	Route::get('/ubiTercero/editUbi', 'TercerosController@selectUbicacionesTerceros');
	Route::get('/ubiTercero/detUbi', 'TercerosController@selectUbicacionesTerceros');
});

Route::group([
	'prefix' => 'empresa',
	'middleware' => ['auth', 'guest:2,3'],
], function() {
	Route::get('/', 'EmpresaController@index');
	Route::get('/getForm/add', 'EmpresaController@getFormAdd');
	Route::post('/agregarEmpresa', 'EmpresaController@create');
	Route::get('/datosEmpresaXId/{id}', 'EmpresaController@edit');
	Route::get('/detalleEmpresa/{id}', 'EmpresaController@detail');
	Route::post('/editarEmpresa/{id}', 'EmpresaController@update');
	Route::post('/eliminarEmpresa/{id}', 'EmpresaController@delete');
	
	Route::group(['prefix' => 'centroCosto'], function(){
		Route::get('/{idEmpresa}', "CentroCostoEmpresaController@index");
		Route::get('/formAdd/{idEmpresa}', "CentroCostoEmpresaController@getFormAdd");
		Route::post('/create', "CentroCostoEmpresaController@create");
		Route::get('/edit/{idEmpresa}', "CentroCostoEmpresaController@edit");
		Route::get('/detail/{idEmpresa}', "CentroCostoEmpresaController@detail");
		Route::post('/update/{idEmpresa}', "CentroCostoEmpresaController@update");
		Route::post('/delete/{idEmpresa}', "CentroCostoEmpresaController@delete");
	});

	Route::group(['prefix' => 'nomina'], function(){
		Route::get('/{idNomina}', 'NominaEmpresaController@index');
		Route::get('/formAdd/{idNomina}', 'NominaEmpresaController@getFormAdd');
		Route::post('/create', 'NominaEmpresaController@create');
		Route::get('/edit/{idNomina}', 'NominaEmpresaController@edit');
		Route::get('/detail/{idNomina}', 'NominaEmpresaController@detail');
		Route::post('/update/{idNomina}', 'NominaEmpresaController@update');
		Route::post('/delete/{idNomina}', 'NominaEmpresaController@delete');
	});
});

Route::group([
	'prefix' => 'cargos',
	'middleware' => ['auth', 'guest:2,3'],
], function() {
	Route::get('/', 'CargosController@index');
	Route::get('/getFormAdd', 'CargosController@getFormAdd');
	Route::post('/agregarCargo', 'CargosController@create');
	Route::get('/datosCargoXId/{id}', 'CargosController@edit');
	Route::get('/detalleCargo/{id}', 'CargosController@detail');
	Route::post('/editarCargo/{id}', 'CargosController@update');
	Route::post('/eliminarCargo/{id}', 'CargosController@delete');
	Route::get('/subirPlano', 'CargosController@subirPlanoIndex');
	Route::post('/subirArchivo', 'CargosController@subirArchivo');
	
});


Route::group([
	'prefix' => 'usuarios',
	'middleware' => ['auth', 'guest:2,3'],
], function() {
	Route::get('/', 'UsuarioController@index');
	Route::get('/getFormAdd', 'UsuarioController@getFormAdd');
	Route::post('/agregarUsuario', 'UsuarioController@create');
	Route::get('/datosUsuarioXId/{id}', 'UsuarioController@edit');
	Route::get('/detalleUsuario/{id}', 'UsuarioController@detail');
	Route::post('/editarUsuario/{id}', 'UsuarioController@update');
	Route::post('/eliminarUsuario/{id}', 'UsuarioController@delete');
	Route::post('/habDesHabUsu/{id}/{estado}', 'UsuarioController@hab_deshab_usu');
	Route::get('/getVistaPass/{id}', 'UsuarioController@vistaActPass');
	Route::post('/cambiarContrasenia/{id}', 'UsuarioController@actPass');
});

Route::get('/recuperar_pass', 'InicioController@vistaRecuperarMail');
Route::get('/vista_rec_pass/{token}', 'InicioController@vistaActPass');
Route::post('/enviar_correo_rec_pass', 'InicioController@validarUsuario');
Route::post('/act_pass', 'InicioController@resetPassword');

 
Route::get("storage-link", function(){
    File::link(
        storage_path('app/public'), public_path('storage')
    );	
});

// Rutas inicio de sesión

Route::post('/login', 'InicioController@login');
Route::get('/logout', 'InicioController@logout');

Route::get('/no_permitido', [ 'uses' => 'InicioController@noPermitido', 'as' => 'no_permitido'])->middleware('auth');

/** RUTAS PARA PORTAL DE EMPLEADOS */

Route::group([
	'prefix' => 'portal',
	'middleware' => ['auth', 'guest:1'],
	'as' => 'portal'
], function() {
	Route::get('/', [ 'uses' => 'PortalEmpleadoController@index', 'as' => '/']);
	Route::get('/infoLaboral/{idEmpleado}', 'PortalEmpleadoController@infoLaboralEmpleado');
	Route::get('/diasVacacionesDisponibles/{idEmpleado}','PortalEmpleadoController@diasVacacionesDisponibles');
	Route::get('/datosEmple/{idEmpleado}', 'PortalEmpleadoController@datosEmpleadoPerfil');
	Route::post('/ediDatosEmple/{idEmpleado}', 'PortalEmpleadoController@editarDataEmple');
	Route::get('/getVistaPass/{id}', 'PortalEmpleadoController@vistaActPass');
	Route::post('/cambiarContrasenia/{id}', 'PortalEmpleadoController@actPass');
});

// Ruta para eliminar cache de la aplicacion

// Eliminar cache completo de aplicación
Route::get('/config_cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h3>Cache de aplicación eliminado</h3>';
});

// Eliminar cache de rutas
Route::get('/route_clear', function() {
    $exitCode = Artisan::call('route:clear');
    return '<h3>Cache de rutas eliminado</h3>';
});

// Eliminar cache de vistas
Route::get('/view_clear', function() {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

// Generar nuevo key de aplicación
Route::get('/key_generate', function() {
    $exitCode = Artisan::call('key:generate');
    return '<h1>Key generated</h1>';
});