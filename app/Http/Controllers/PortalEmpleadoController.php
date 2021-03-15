<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoloPassRequest;
use App\EmpleadoModel;
use App\Ubicacion;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Dompdf\Dompdf;
use DateTime;

class PortalEmpleadoController extends Controller
{
    public function index() {
        $dataUsu = Auth::user();
        $dataEmple = DB::table('empleado')
        ->join('datospersonales', 'empleado.fkDatosPersonales', 'datospersonales.idDatosPersonales')
        ->select(
            'datospersonales.primerNombre',
            'datospersonales.segundoNombre',
            'datospersonales.primerApellido',
            'datospersonales.segundoApellido',
            'datospersonales.foto',
            'empleado.fkEmpresa'
        )
        ->where('idempleado', $dataUsu->fkEmpleado)->first();
        $fotoEmple = 'http://gesath.web-html.com/storage/imgEmpleados/'.$dataEmple->foto;
        $dataEmpr = DB::table('empresa')->select(
            'empresa.idempresa',
            'empresa.logoEmpresa',
            'empresa.razonSocial',
            'empresa.permisosGenerales',
            'empleado.fkNomina',
            'empleado.idempleado'
        )->join('empleado', 'empresa.idempresa', 'empleado.fkEmpresa')
        ->where('empresa.idempresa', $dataEmple->fkEmpresa)->first();
            
        if (is_null($dataEmple->foto) || $dataEmple->foto === '') {
           
            if (is_null($dataEmpr->logoEmpresa)) {
                $fotoEmple = '/img/noimage.png';
            } else {
                $fotoEmple = '/storage/logosEmpresas/'.$dataEmpr->logoEmpresa;
            }
        }

        return view('/portalEmpleado.inicio', [
            'dataUsu' => $dataUsu,
            'dataEmple' => $dataEmple,
            'dataEmpr' => $dataEmpr,
            'fotoEmple' => $fotoEmple
        ]);
    }

    public function infoLaboralEmpleado($idEmple) {
        $infoEmpleado = DB::table('empleado')
        ->join('empresa', 'empleado.fkEmpresa', 'empresa.idempresa')
        ->join('datospersonales', 'datospersonales.idDatosPersonales', 'empleado.fkDatosPersonales')
        ->join('nomina', 'empleado.fkNomina', 'nomina.idnomina')
        ->join('conceptofijo', 'empleado.idempleado', 'conceptofijo.fkEmpleado')
        ->join('cargo', 'empleado.fkCargo', 'cargo.idCargo')
        ->join('centrocosto', 'empresa.idempresa', 'centrocosto.fkEmpresa')
        ->select(
            'datospersonales.primerNombre',
            'datospersonales.segundoNombre',
            'datospersonales.primerApellido',
            'datospersonales.segundoApellido',
            'conceptofijo.valor',
            'conceptofijo.unidad',
            'cargo.nombreCargo',
            'centrocosto.nombre',
            'empresa.razonSocial',
            'empleado.fechaIngreso',
            'datospersonales.numeroIdentificacion'
        )
        ->where('empleado.idempleado', $idEmple)
        ->where('conceptofijo.fkConcepto', '1')
        ->groupBy('empresa.idempresa')
        ->get();
        
        

        $otrosIngresos = DB::table('conceptofijo')
        ->join('concepto', 'conceptofijo.fkConcepto', 'concepto.idconcepto')
        ->select(
            'conceptofijo.valor',
            'concepto.nombre'
        )
        ->where('conceptofijo.fkEmpleado', $idEmple)
        ->where('conceptofijo.fkConcepto', '<>', 1)
        ->get();

        $afiliaciones = DB::table('afiliacion')
        ->join('tercero', 'afiliacion.fkTercero', 'tercero.idTercero')
        ->join('tipoafilicacion', 'afiliacion.fkTipoAfilicacion', 'tipoafilicacion.idTipoAfiliacion')
        ->join('empleado', 'empleado.idempleado', 'afiliacion.fkEmpleado')
        ->select(
            'tercero.razonSocial',
            'afiliacion.fechaAfiliacion',
            'tipoafilicacion.nombre'
        )
        ->where('empleado.idempleado', $idEmple)
        ->get();
    
        return view('portalEmpleado.infoLaboral', [
            'dataEmple' => $infoEmpleado,
            'otrosIngresos' => $otrosIngresos,
            'afiliaciones' => $afiliaciones
        ]);
    }

    public function diasVacacionesDisponibles($idEmpleado){        
        $empleado = DB::table("empleado","e")->where("e.idempleado","=",$idEmpleado)->first();
        $fechaFin = date("Y-m-d");

        $novedadesRetiro = DB::table("novedad","n")
        ->select("r.fecha")
        ->join("retiro AS r", "r.idRetiro","=","n.fkRetiro")
        ->where("n.fkEmpleado", "=", $empleado->idempleado)
        ->whereIn("n.fkEstado",["7","8"])
        ->whereNotNull("n.fkRetiro")
        ->first();

        $fechaFin = date("Y-m-d");

        $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$fechaFin) + 1 ;

        if(isset($novedadesRetiro)){
            if(strtotime($fechaFin) > strtotime($novedadesRetiro->fecha)){
                $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
            }
        }
        $diasVac = $periodoPagoVac * 15 / 360;
        $novedadesVacacion = DB::table("novedad","n")
        ->join("vacaciones as v","v.idVacaciones","=","n.fkVacaciones")
        ->where("n.fkEmpleado","=",$empleado->idempleado)
        ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
        ->whereNotNull("n.fkVacaciones")
        ->get();
        //$diasVac = $totalPeriodoPagoAnioActual * 15 / 360;
        foreach($novedadesVacacion as $novedadVacacion){
            $diasVac = $diasVac - $novedadVacacion->diasCompensar;
        }
        if(isset($diasVac) && $diasVac < 0){
            $diasVac = 0;
        }
        $diasVac = intval($diasVac*100);
        $diasVac = $diasVac/100;

        $fechaInicioVacas = new DateTime($fechaFin);
        $stringResta = (string) 'P'.round($diasVac).'D';
        $fechaInicioVacas->sub(new \DateInterval($stringResta));

        /* return response()->json([
            "diasVac" => number_format($diasVac, 2),
            "fechaIngreso" => $empleado->fechaIngreso,
            "fechaInicioVacas" => date("Y-m-d", $fechaInicioVacas),
            "fechaCorteCalculo" => $fechaFin
        ]); */

        return view ('portalEmpleado.vacacionesEmple', [
            "diasVac" => floatval(round(number_format($diasVac), 2)),
            "fechaIngreso" => $empleado->fechaIngreso,
            "fechaInicioVacas" => $fechaInicioVacas->format("Y-m-d"),
            "fechaCorteCalculo" => $fechaFin
        ]);
    }

    public function datosEmpleadoPerfil($idEmpleado) {
        $datosEmple = DB::table('datospersonales')
        ->select(
            'datospersonales.correo',
            'datospersonales.correo2',
            'datospersonales.telefonoFijo',
            'datospersonales.celular',
            'datospersonales.direccion',
            'datospersonales.barrio',
            'datospersonales.fkUbicacionResidencia as ubi',
            DB::raw('(select fkUbicacion from ubicacion where idubicacion = ubi) as ubi_dos'),
            DB::raw('(select fkUbicacion from ubicacion where idubicacion = ubi_dos) as ubi_tres')
        )
        ->join('empleado', 'datospersonales.idDatosPersonales', 'empleado.fkDatosPersonales')
        ->where('empleado.idempleado', $idEmpleado)
        ->first();

        $paises = Ubicacion::where('fkTipoUbicacion', '=', 1)->get();
        $deptos = Ubicacion::where('fkTipoUbicacion', '=', 2)->get();
        $ciudades = Ubicacion::where('fkTipoUbicacion', '=', 3)->get();

        $ubicaciones = Ubicacion::all();

        return view("portalEmpleado.datosEmple", [
            "idEmpleado" => $idEmpleado,
            "datosEmple" => $datosEmple,
            'ubicaciones' => $ubicaciones,
            'paises' => $paises,
            'deptos' => $deptos,
            'ciudades' => $ciudades
        ]);
    }

    public function editarDataEmple(Request $request, $idEmpleado) {
        $emple = DB::table('empleado')->select(
            'fkDatosPersonales'
        )->where('idempleado', $idEmpleado)->first();
        if ($emple) {
            $actDatosEmple = DB::table('datospersonales')
            ->where('idDatosPersonales', $emple->fkDatosPersonales)
            ->update([
                'correo' => $request->correo,
                'correo2' => $request->correo2,
                'telefonoFijo' => $request->telefonoFijo,
                'celular' => $request->celular,
                'direccion' => $request->direccion,
                'barrio' => $request->barrio,
                'fkUbicacionResidencia' => $request->fkUbicacion
            ]);
            if ($actDatosEmple) {
                return response()->json(['success' => true, 'mensaje' => 'Datos actualizados exitosamente']);
            } else {
                return response()->json(['success' => false, 'mensaje' => 'Error al actualizar datos de empleado']);
            }
        } else {
            return response()->json(['success' => false, 'mensaje' => 'Error, empleado con este ID no existe']);
        }
    }

    public function getVistaBoucherPago($id) {
        return view('/portalEmpleado.comprobantesPago', [
            'idEmple' => $id
        ]);
    }

    public function getBouchersPagoEmpleado($idEmple) {
        $bouchersPago = DB::table('liquidacionnomina')
            ->join('boucherpago', 'boucherpago.fkLiquidacion', 'liquidacionnomina.idLiquidacionNomina')
            ->select([
                'boucherpago.idBoucherPago',
                'liquidacionnomina.fechaInicio',
                'liquidacionnomina.fechaFin',
            ])
            ->where('boucherpago.fkEmpleado', '=', $idEmple)
            ->get();
        return $bouchersPago;
    }

    public function buscarBoucherPorFecha(Request $request, $idEmple) {
        $bouchersPago = DB::table('liquidacionnomina')
            ->join('boucherpago', 'boucherpago.fkLiquidacion', 'liquidacionnomina.idLiquidacionNomina')
            ->select([
                'boucherpago.idBoucherPago',
                'liquidacionnomina.fechaInicio',
                'liquidacionnomina.fechaFin',
            ])
            ->where('boucherpago.fkEmpleado', '=', $idEmple)
            ->whereBetween('liquidacionnomina.fechaInicio', [$request->fechaInicio, $request->fechaFin])
            ->get();
        return $bouchersPago;
    }

    public function generarCertificadoLaboral($idEmple) {
        setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
        $fechaCarta = ucwords(iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B de %Y", strtotime(date('Y-m-d')))));

        $dompdf = new Dompdf();
        $datosEmpleado = DB::table('empleado')
        ->join('datospersonales', 'datospersonales.idDatosPersonales', 'empleado.fkDatosPersonales')
        ->join('empresa', 'empleado.fkEmpresa', 'empresa.idempresa')
        ->select(
            'datospersonales.primerNombre',
            'datospersonales.segundoNombre',
            'datospersonales.primerApellido',
            'datospersonales.segundoApellido',
            'datospersonales.numeroIdentificacion',
            'empresa.razonSocial'
        )
        ->where('empleado.idempleado', $idEmple)
        ->first();

        $empresasEmpleado = DB::table('empleado')
        ->join('empresa', 'empleado.fkEmpresa', 'empresa.idempresa')
        ->join('datospersonales', 'datospersonales.idDatosPersonales', 'empleado.fkDatosPersonales')
        ->join('nomina', 'empleado.fkNomina', 'nomina.idnomina')
        ->join('conceptofijo', 'empleado.idempleado', 'conceptofijo.fkEmpleado')
        ->join('cargo', 'empleado.fkCargo', 'cargo.idCargo')
        ->join('centrocosto', 'empresa.idempresa', 'centrocosto.fkEmpresa')
        ->join('periodo', 'periodo.fkEmpleado', 'empleado.idempleado')
        ->select(
            'conceptofijo.valor as sueldoConceptoFijo',
            'conceptofijo.unidad',
            'cargo.nombreCargo',
            'centrocosto.nombre',
            'empresa.razonSocial',
            'periodo.fechaInicio',
            'periodo.fechaFin',
            'periodo.salario as sueldoPeriodo',
            'periodo.fkEstado'
        )
        ->where('empleado.idempleado', $idEmple)
        ->whereIn('conceptofijo.fkConcepto', ['1', '2', '53', '54'])
        ->groupBy('empresa.idempresa')
        ->orderBy('periodo.fechaInicio', 'DESC')
        ->get();

        $posibleReintegro = DB::table('periodo')
        ->join('estado', 'periodo.fkEstado', 'estado.idestado')
        ->join('empleado', 'periodo.fkEmpleado', 'empleado.idempleado')
        ->join('nomina', 'periodo.fkNomina', 'nomina.idNomina')
        ->join('empresa', 'empleado.fkEmpresa', 'empresa.idempresa')
        ->join('datospersonales', 'datospersonales.idDatosPersonales', 'empleado.fkDatosPersonales')
        ->join('conceptofijo', 'empleado.idempleado', 'conceptofijo.fkEmpleado')
        ->join('cargo', 'empleado.fkCargo', 'cargo.idCargo')
        ->join('centrocosto', 'empresa.idempresa', 'centrocosto.fkEmpresa')
        ->select(
            'conceptofijo.valor as sueldoConceptoFijo',
            'conceptofijo.unidad',
            'cargo.nombreCargo',
            'centrocosto.nombre',
            'empresa.razonSocial',
            'periodo.fechaInicio',
            'periodo.fechaFin',
            'periodo.salario as sueldoPeriodo',
            'periodo.fkEstado',
            'estado.nombre'
        )
        ->where('empleado.idempleado', $idEmple)
        ->where('periodo.fkEstado', ['1', '2', '53', '54'])
        ->where('conceptofijo.fkConcepto', '1')
        ->groupBy('empresa.idempresa')
        ->get();

        $trabajos = null;

        if ($posibleReintegro) {
            $empresasEmple = $empresasEmpleado->merge($posibleReintegro);
            $trabajos = $empresasEmple->all();
        } else {
            $trabajos = $empresasEmpleado->all();
            $trabajos = collect($trabajos)->sortByDesc('fechaInicio')->reverse()->toArray();
        }        

        /* return response()->json([
            'dataEmpleado' => $datosEmpleado,
            'empresasEmpleado' => $trabajos,
            'fechaCarta' => $fechaCarta
        ]); */

        /* return view('/pdfs.certificadoLaboral', [
            'dataEmpleado' => $datosEmpleado,
            'empresasEmpleado' => $trabajos,
            'fechaCarta' => $fechaCarta
        ]); */
        
        $vista = view('/pdfs.certificadoLaboral', [
            'dataEmpleado' => $datosEmpleado,
            'empresasEmpleado' => $trabajos,
            'fechaCarta' => $fechaCarta
        ]);

        $dompdf->loadHtml($vista ,'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        $dompdf->get_canvas()->get_cpdf()->setEncryption($datosEmpleado->numeroIdentificacion, $datosEmpleado->numeroIdentificacion);
        $dompdf->stream("Certificación Laboral", array('compress' => 1, 'Attachment' => 1));
    }


    public function vistaActPass($id) {
        try {
            $usuario = DB::table('users')->where('fkEmpleado', $id)->first();            
            return view('/usuarios/cambiarPass', [
                'usuario' => $usuario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }

    public function actPass(Request $request, $id) {
        try {
            $usuario = User::findOrFail($id);
            $usuario->password = $request->password;
            $save = $usuario->save();
            if ($save) {
                $success = true;
                $mensaje = "Contraseña modificada correctamente";
            } else {
                $success = false;
                $mensaje = "Error al modificar contraseña";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }

    public function traerFormularios220() {
        $formularios = DB::table('formulario220')->get();
        return view('portalEmpleado.selectFormulario2020', [
            'formularios' => $formularios
        ]);
    }

    public function days_360($fecha1,$fecha2,$europeo=true) {
        if( $fecha1 > $fecha2 ) {
        $temf = $fecha1;
        $fecha1 = $fecha2;
        $fecha2 = $temf;
        }
    
        list($yy1, $mm1, $dd1) = explode('-', $fecha1);
        list($yy2, $mm2, $dd2) = explode('-', $fecha2);
    
        if( $dd1==31) { $dd1 = 30; }
    
        if(!$europeo) {
        if( ($dd1==30) and ($dd2==31) ) {
            $dd2=30;
        } else {
            if( $dd2==31 ) {
            $dd2=30;
            }
        }
        }
    
        if( ($dd1<1) or ($dd2<1) or ($dd1>30) or ($dd2>31) or
            ($mm1<1) or ($mm2<1) or ($mm1>12) or ($mm2>12) or
            ($yy1>$yy2) ) {
        return(-1);
        }
        if( ($yy1==$yy2) and ($mm1>$mm2) ) { return(-1); }
        if( ($yy1==$yy2) and ($mm1==$mm2) and ($dd1>$dd2) ) { return(-1); }
    
        //Calc
        $yy = $yy2-$yy1;
        $mm = $mm2-$mm1;
        $dd = $dd2-$dd1;
    
        return( ($yy*360)+($mm*30)+$dd );
    }
    
    public function actOnlyPass(SoloPassRequest $request, $id) {
        try {
            $usuario = User::findOrFail($id);
            $usuario->password = $request->password;
            $save = $usuario->save();
            if ($save) {
                $success = true;
                $mensaje = "Contraseña modificada correctamente";
            } else {
                $success = false;
                $mensaje = "Error al modificar contraseña";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }
}