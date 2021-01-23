<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use DateTime;
use DateInterval;

class NovedadesController extends Controller
{
    public function index(){
        $nominas = DB::table("nomina")->get();
        $tipos_novedades = DB::table("tiponovedad")->get();
        $fechaMinima = date('Y-m-01');
        $dataUsu = UsuarioController::dataAdminLogueado();
        return view('/novedades.cargarNovedad',[
            'nominas' => $nominas,
            'tipos_novedades' => $tipos_novedades,
            'fechaMinima' =>  $fechaMinima,
            'dataUsu' => $dataUsu
        ]);
    }
    public function cargarFormxTipoNov($idTipoNovedad){
        switch ($idTipoNovedad) {
            case '1':
                $opciones = '<option value=""></option><option value="1">Rango Horas - Fechas</option><option value="2">Cantidad Dias - Horas</option>';
                return response()->json([
                    "success" => true,
                    "tipo" => 2,
                    "opciones" => $opciones
                ]);
                break;
            case '2':
                return response()->json([
                    "success" => true,
                    "tipo" => 2
                ]);
                break;
            case '3':
                return response()->json([
                    "success" => true,
                    "tipo" => 2
                ]);
                break;
            case '4':
                $opciones = '<option value=""></option><option value="1">Rango Horas</option><option value="2">Total Horas</option>';
                return response()->json([
                    "success" => true,
                    "tipo" => 1,
                    "opciones" => $opciones
                ]);
                break;
            case '5':
                    return response()->json([
                        "success" => true,
                        "tipo" => 2
                    ]);
                    break;    
            case '6':
                $opciones = '<option value=""></option><option value="1">Vacaciones Disfrutadas</option><option value="2">Vacaciones Compensadas</option>';
                return response()->json([
                    "success" => true,
                    "tipo" => 1,
                    "opciones" => $opciones
                ]);
                break;  
            case '7':
                return response()->json([
                    "success" => true,
                    "tipo" => 2
                ]);
                break;    

        }        
    }

    public function cargarFormxTipoReporte(Request $req){
        if(!isset($req->nomina) || !isset($req->fecha) || !isset($req->tipo_novedad)){
            return "";
        }
        if($req->tipo_novedad == "1"){ //&& isset($req->tipo_reporte) && $req->tipo_reporte == "1"){
            $conceptos = DB::table("concepto", "c")
                        ->select(["c.*"])
                        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
                        ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)->get();
            

            return view('/novedades.ajax.ausencia1',[
                'conceptos' => $conceptos,
                'req' => $req
     
            ]);
        }
        /*else if($req->tipo_novedad == "1" && isset($req->tipo_reporte) && $req->tipo_reporte == "2"){
            $conceptos = DB::table("concepto", "c")
                        ->select(["c.*"])
                        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
                        ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)->get();
            

            return view('/novedades.ajax.ausencia2',[
                'conceptos' => $conceptos,
                'req' => $req
     
            ]);
        }*/
        else if($req->tipo_novedad == "2"){
            $conceptos = DB::table("concepto", "c")
                        ->select(["c.*"])
                        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
                        ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)->get();
            $tiposAfiliacion = DB::table("tipoafilicacion")->whereIn("idTipoAfiliacion", [3,4])->get();

            return view('/novedades.ajax.incapacidad',[
                'conceptos' => $conceptos,
                'tiposAfiliacion' => $tiposAfiliacion,
                'req' => $req
     
            ]);
        }
        else if($req->tipo_novedad == "3"){
            $conceptos = DB::table("concepto", "c")
                        ->select(["c.*"])
                        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
                        ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)->get();
            

            return view('/novedades.ajax.licencia',[
                'conceptos' => $conceptos,
                'req' => $req
     
            ]);
        }
        else if($req->tipo_novedad == "4" && isset($req->tipo_reporte) && $req->tipo_reporte == "1"){
            $conceptos = DB::table("concepto", "c")
                        ->select(["c.*"])
                        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
                        ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)->get();
            
            return view('/novedades.ajax.horas1',[
                'conceptos' => $conceptos,
                'req' => $req
     
            ]);
        }
        else if($req->tipo_novedad == "4" && isset($req->tipo_reporte) && $req->tipo_reporte == "2"){
            $conceptos = DB::table("concepto", "c")
                        ->select(["c.*"])
                        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
                        ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)->get();
            
            return view('/novedades.ajax.horas2',[
                'conceptos' => $conceptos,
                'req' => $req
     
            ]);
        }
        else if($req->tipo_novedad == "5"){
            $motivosRetiro = DB::table("motivo_retiro", "m")->orderBy("nombre")->get();
            
            return view('/novedades.ajax.retiro',[
                'motivosRetiro' => $motivosRetiro,
                'req' => $req
     
            ]);
        }
        else if($req->tipo_novedad == "6" && isset($req->tipo_reporte) && $req->tipo_reporte == "1"){
            $conceptos = DB::table("concepto", "c")
            ->select(["c.*"])
            ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
            ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)
            ->where("tnc.tipoReporte", "=", $req->tipo_reporte)
            ->get();

            return view('/novedades.ajax.vacaciones',[
                'conceptos' => $conceptos,
                'req' => $req
            ]);
        }
        else if($req->tipo_novedad == "6" && isset($req->tipo_reporte) && $req->tipo_reporte == "2"){
            $conceptos = DB::table("concepto", "c")
                        ->select(["c.*"])
                        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
                        ->where("tnc.fkTipoNovedad", "=", $req->tipo_novedad)
                        ->where("tnc.tipoReporte", "=", $req->tipo_reporte)
                        ->get();
            
            return view('/novedades.ajax.vacaciones2',[
                'conceptos' => $conceptos,
                'req' => $req
     
            ]);
        }
        else if($req->tipo_novedad == "7"){
            $conceptos = DB::table("concepto", "c")->orderBy("nombre")->get();

            return view('/novedades.ajax.otros',[
                'conceptos' => $conceptos,
                'req' => $req
            ]);
        }
        
    }
    public function tipoAfiliacionxConcepto($tipoNovedad, $concepto){
        $tipoAfiliacion = DB::table("tiponovconceptotipoent")->where("fkTipoNovedad","=", $tipoNovedad)
            ->where("fkConcepto", "=", $concepto)->first();
        
        return response()->json(['success'=>true, 'actividad' => $tipoAfiliacion->fkTipoAfilicacion]);
    }
    public function entidadxTipoAfiliacion($tipoAfiliacion, $idEmpleado){
        if($tipoAfiliacion == -1){
            $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.idTercero"])
            ->join("empresa AS em","em.fkTercero_ARL","=","t.idTercero")
            ->join("empleado AS e","e.fkEmpresa","=","em.idempresa")
            ->where("e.idempleado","=",$idEmpleado)->first();
            return response()->json(['success'=>true, 'nombreTercero' => $tercero->razonSocial, 'idTercero' => $tercero->idTercero]);
        }
        else{
            $tercero = DB::table("afiliacion", "a")->select(["t.razonSocial", "t.idTercero"])
            ->join("tercero AS t","t.idTercero","=","a.fkTercero")
            ->where("a.fkTipoAfilicacion","=", $tipoAfiliacion)
            ->where("a.fkEmpleado","=",$idEmpleado)->first();
            return response()->json(['success'=>true, 'nombreTercero' => $tercero->razonSocial, 'idTercero' => $tercero->idTercero]);
        }
        
    }
    


    public function insertarNovedadHoraTipo1(Request $req){
      
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'horaInicial' => 'required|date',
            'horaFinal' => 'required|date|after_or_equal:horaInicial'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $horaI = strtotime( $req->horaInicial );
        $horaF = strtotime( $req->horaFinal );
        $diff = $horaF - $horaI;
        $horas = $diff / ( 60 * 60 );
        $horas = round($horas, 2);

        $idHoraExtra = DB::table('horas_extra')->insertGetId([
            "cantidadHoras" => $horas, 
            "fechaHoraInicial" => date("Y-m-d H:i:s", $horaI),
            "fechaHoraFinal" => date("Y-m-d H:i:s", $horaF)
        ], "idHoraExtra");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkTipoReporte" => $req->fkTipoReporte,
            "fkHorasExtra" => $idHoraExtra,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        
        

        DB::table('novedad')->insert($arrInsertNovedad);
        
        return response()->json(['success'=>true]);


    }
    public function insertarNovedadHoraTipo2(Request $req){
      
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'numeric' => 'La :attribute debe ser numerica.'
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'cantidadHoras' => 'required|numeric'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        

        $idHoraExtra = DB::table('horas_extra')->insertGetId([
            "cantidadHoras" => $req->cantidadHoras, 
        ], "idHoraExtra");

        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkTipoReporte" => $req->fkTipoReporte,
            "fkHorasExtra" => $idHoraExtra,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }
    public function insertarNovedadIncapacidad(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'numeric' => 'La :attribute debe ser numerica.'
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaInicial' => 'required|date',
            'dias' => 'required|numeric',
            'fechaFinal' => 'required|date',
            'fechaRealI' => 'required|date',
            'fechaRealF' => 'required|date',
            'codigoDiagnostico' => 'required',
            'pagoTotal' => 'required',
            'tipoAfiliacion' => 'required',
            'terceroEntidad' => 'required',
            'naturaleza' => 'required',
            'tipo' => 'required'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $tipoAfiliacion = null;
        if($req->tipoAfiliacion!="-1"){
            $tipoAfiliacion = ($req->tipoAfiliacion);
        }
        $idIncapacidad = DB::table('incapacidad')->insertGetId([
            "numDias" => $req->dias, 
            "fechaInicial" => $req->fechaInicial, 
            "fechaFinal" => $req->fechaFinal, 
            "fechaRealI" => $req->fechaRealI, 
            "fechaRealF" => $req->fechaRealF, 
            "pagoTotal" => $req->pagoTotal,
            "fkCodDiagnostico" => $req->idCodigoDiagnostico,
            "numIncapacidad" => $req->numIncapacidad,
            "fkTipoAfilicacion" => $tipoAfiliacion,
            "fkTercero" => $req->idTerceroEntidad,
            "naturaleza" => $req->naturaleza,
            "tipoIncapacidad" => $req->tipo,
        ], "idIncapacidad");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkIncapacidad" => $idIncapacidad,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }
    public function insertarNovedadLicencia(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'numeric' => 'La :attribute debe ser numerica.'
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaInicial' => 'required|date',
            'dias' => 'required|numeric',
            'fechaFinal' => 'required|date',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $idLicencia = DB::table('licencia')->insertGetId([
            "numDias" => $req->dias, 
            "fechaInicial" => $req->fechaInicial, 
            "fechaFinal" => $req->fechaFinal, 
        ], "idLicencia");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkLicencia" => $idLicencia,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }
    public function insertarNovedadAusencia1(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaAusenciaInicial' => 'required|date',
            'fechaAusenciaFinal' => 'required|date|after_or_equal:fechaAusenciaInicial'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $fechaAusInicial = date("Y-m-d", strtotime($req->fechaAusenciaInicial));
        $fechaAusFinal = date("Y-m-d", strtotime($req->fechaAusenciaFinal));

        $fechaAusenciaInicial = strtotime( $req->fechaAusenciaInicial );
        $fechaAusenciaFinal = strtotime( $req->fechaAusenciaFinal );
        $diff = $fechaAusenciaFinal - $fechaAusenciaInicial;

        $dias = $diff / ( 60 * 60 * 24);
        $dias = floor($dias);

        $restoDias = $diff % ( 60 * 60 * 24);
        

        $horas = $restoDias / ( 60 * 60 );
        $horas = round($horas, 2);

        
        $empleado = DB::table("empleado")->where("idempleado","=", $req->idEmpleado)->first();

        $fecha = new DateTime($fechaAusInicial);
        $arrDiasAdicionales=array();
        
        
        do{

            $domingoSemana = date("Y-m-d", strtotime('next sunday '.$fecha->format('Y-m-d')));
            $sabadoSemana = date("Y-m-d", strtotime('next saturday '.$fecha->format('Y-m-d')));

            if($empleado->sabadoLaborable == "0"){//No trabaja el sabado
                if(!in_array($domingoSemana, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $domingoSemana);
                }
                if(!in_array($sabadoSemana, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $sabadoSemana);
                }
            }
            else{
                if(!in_array($domingoSemana, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $domingoSemana);
                }
            }

            $sql = "'".$fecha->format('Y-m-d')."' BETWEEN fechaInicioSemana AND fechaFinSemana";
            $calendarios = DB::table("calendario")
            ->whereRaw($sql)->get();
            foreach($calendarios as $calendario){
                if(!in_array($calendario->fecha, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $calendario->fecha);
                }
            }


            if($fechaAusFinal != $fecha->format('Y-m-d')){
                $fecha->add(new DateInterval('P1D'));
            }
            
        }
        while($fechaAusFinal != $fecha->format('Y-m-d'));

        $arrDiasAdicionalesFinal = array();

        foreach($arrDiasAdicionales as $arrDiasAdicional){
            $cuentaDiaAdd = DB::table("ausencia","a")->join("novedad AS n", "n.fkAusencia", "=", "a.idAusencia")
            ->where("n.fkEmpleado","=",$req->idEmpleado)
            ->where("a.fechasAdicionales","LIKE", "%".$arrDiasAdicional."%")
            ->get();

            if(sizeof($cuentaDiaAdd)==0){
                array_push($arrDiasAdicionalesFinal, $arrDiasAdicional);
                $dias++;
            }
        }


        

        

        $textoDias = implode(",",$arrDiasAdicionalesFinal);

        $arrAusenciaIns = array(
            "fechaInicio" => $req->fechaAusenciaInicial, 
            "fechaFin" => $req->fechaAusenciaFinal, 
            "cantidadDias" => $dias, 
            "cantidadHoras" => $horas,
            "fechasAdicionales" => $textoDias
        );
        
        

        $idAusencia = DB::table('ausencia')->insertGetId($arrAusenciaIns, "idAusencia");
        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkAusencia" => $idAusencia,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }

    public function insertarNovedadAusencia2(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'dias' => 'required_without:horas',
            'horas' => 'required_without:dias'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      


        $idAusencia = DB::table('ausencia')->insertGetId([
            "cantidadDias" => $req->dias, 
            "cantidadHoras" => $req->horas
        ], "idAusencia");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkAusencia" => $idAusencia,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }
    public function insertarNovedadRetiro(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'motivoRetiro' => 'required',
            'idEmpleado' => 'required',
            'fechaRetiro' => 'required',
            'fechaRetiroReal' => 'required',
            'indemnizacion' => 'required'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      


        $idRetiro = DB::table('retiro')->insertGetId([
            "fecha" => $req->fechaRetiro, 
            "fechaReal" => $req->fechaRetiroReal,
            "fkMotivoRetiro" => $req->motivoRetiro,
            "indemnizacion" => $req->indemnizacion
        ], "idRetiro");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkRetiro" => $idRetiro,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }
    
    public function fechaConCalendario(Request $req){

        if(isset($req->fecha) && isset($req->dias) && isset($req->idEmpleado)){

            $empleado = DB::table("empleado")->where("idempleado","=", $req->idEmpleado)->first();
            $fecha = new DateTime($req->fecha);
            $i=0;
            if($empleado->sabadoLaborable == "1"){
                if(date('N',strtotime($fecha->format('Y-m-d')))<=6){
                    $i++;
                }
            }
            else{
                if(date('N',strtotime($fecha->format('Y-m-d')))<=5){
                    $i++;
                }
            }
            
            
            while(intval($req->dias) > $i){
                $fecha->add(new DateInterval('P1D'));
                if($empleado->sabadoLaborable == "1"){
                    if(date('N',strtotime($fecha->format('Y-m-d')))<=6){
                        
                        $calendarios = DB::table("calendario")->selectRaw("count(*) as cuenta")
                        ->where("fecha", "=", $fecha->format('Y-m-d'))->first();
                        if($calendarios->cuenta == 0){
                            $i++;
                        }
                    }
                }
                else{

                    if(date('N',strtotime($fecha->format('Y-m-d')))<=5){
                        $calendarios = DB::table("calendario")->selectRaw("count(*) as cuenta")
                        ->where("fecha", "=", $fecha->format('Y-m-d'))->first();
                        if($calendarios->cuenta == 0){
                            $i++;
                        }
                    }
                }





                
            }
            

            /*$calendarios = DB::table("calendario")->selectRaw("count(*) as cuenta")
            ->whereBetween("fecha",[$req->fecha,  $fecha->format('Y-m-d')])->first();
            if($calendarios->cuenta > 0){

                $fecha->add(new DateInterval('P'.$calendarios->cuenta.'D'));
            }*/



            $datetime1 = new DateTime($req->fecha);
            $datetime2 = new DateTime($fecha->format('Y-m-d'));

            $interval = $datetime1->diff($datetime2);

            return response()->json([
                'success'=>true,
                'fecha'=>$fecha->format('Y-m-d'),
                "diasCalendario" => ($interval->format('%a') + 1)
            ]);
            
        }

       
    }

    public function insertarNovedadVacaciones2(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'dias' => 'required',
            'pagoAnticipado' => 'required'            
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      
   
        $idVacaciones = DB::table('vacaciones')->insertGetId([
            "diasCompensar" => $req->dias,
            "pagoAnticipado" => $req->pagoAnticipado
        ], "idVacaciones");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkVacaciones" => $idVacaciones,
            "fkEmpleado" => $req->idEmpleado,
            "fkConcepto" => $req->concepto,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }

    public function insertarNovedadVacaciones(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaInicial' => 'required',
            'dias' => 'required',
            'fechaFinal' => 'required',
            'pagoAnticipado' => 'required'            
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      
   
        $idVacaciones = DB::table('vacaciones')->insertGetId([
            "fechaInicio" => $req->fechaInicial, 
            "fechaFin" => $req->fechaFinal,
            "diasCompensar" => $req->dias,
            "diasCompletos" => $req->diasCompletos,
            "pagoAnticipado" => $req->pagoAnticipado
        ], "idVacaciones");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkVacaciones" => $idVacaciones,
            "fkEmpleado" => $req->idEmpleado,
            "fkConcepto" => $req->concepto,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }
    
    public function insertarNovedadOtros(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'valor' => 'required',
            'sumaResta' => 'required',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      
   
        $idOtraNovedad = DB::table('otra_novedad')->insertGetId([
            "valor" => $req->valor,
            "sumaResta" => $req->sumaResta
        ], "idOtraNovedad");


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();

        $arrInsertNovedad = array(
            "fkTipoNovedad" => $req->fkTipoNovedad, 
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fkNomina" => $req->fkNomina,
            "fechaRegistro" => $req->fechaRegistro,
            "fkOtros" => $idOtraNovedad,
            "fkEmpleado" => $req->idEmpleado,
            "fkConcepto" => $req->concepto,
        );
        DB::table('novedad')->insert($arrInsertNovedad);
        return response()->json(['success'=>true]);
    }
    public function lista(Request $req){
        $novedades = DB::table("novedad","n")
        ->select([
            "n.*",
            "c.nombre as nombreConcepto",
            "nom.nombre as nombreNomina",
            "em.razonSocial as nombreEmpresa",
            "est.nombre as nombreEstado",
            "ti.nombre as tipoDocumento",
            "dp.numeroIdentificacion",
            "dp.primerNombre",
            "dp.segundoNombre",
            "dp.primerApellido",
            "dp.segundoApellido"
        ])
        ->join("concepto as c","c.idconcepto", "=", "n.fkConcepto", "left")
        ->join("nomina as nom","nom.idNomina", "=", "n.fkNomina")
        ->join("empresa as em","em.idempresa", "=", "nom.fkEmpresa")
        ->join("estado as est","est.idestado", "=", "n.fkEstado")
        ->join("empleado as e","e.idempleado", "=", "n.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion");

        if(isset($req->fechaInicio)){
            $novedades = $novedades->where("n.fechaRegistro",">=",$req->fechaInicio);
        }
        
        if(isset($req->fechaFin)){
            $novedades = $novedades->where("n.fechaRegistro","<=",$req->fechaFin);
        }

        if(isset($req->nomina)){
            $novedades = $novedades->where("n.fkNomina","=",$req->nomina);
        }
        if(isset($req->tipoNovedad)){
            $novedades = $novedades->where("n.fkTipoNovedad","=",$req->tipoNovedad);
        }

        $novedades = $novedades->where("n.fkEstado","=","7")
        ->whereRaw("n.fkPeriodoActivo in(
            SELECT p.idPeriodo from periodo as p where p.fkEstado = '1'
        )")
        ->get();
        $nominas = DB::table("nomina")->orderBy("nombre")->get();
        $tiposnovedades = DB::table("tiponovedad")->orderBy("nombre")->get();

        return view('/novedades.listaNovedades',[
            'novedades' => $novedades,
            "tiposnovedades" => $tiposnovedades,
            "nominas" => $nominas,
            "req" => $req
        ]);        

    }

    public function eliminarNovedad($idNovedad){
        DB::table('novedad')->where("idNovedad","=",$idNovedad)->update(["fkEstado" => "9"]);
        
        return response()->json([
			"success" => true
        ]);
    }
    public function eliminarNovedadDef($idNovedad){
        DB::table('novedad')->where("idNovedad","=",$idNovedad)->delete();
        
        return response()->json([
			"success" => true
        ]);
    }

    public function modificarNovedad($idNovedad){
        $novedad = DB::table('novedad',"n")->select([
            "n.*", 
            "dp.primerNombre",
            "dp.primerNombre",
            "dp.segundoNombre",
            "dp.primerApellido", 
            "dp.segundoApellido",
            "nom.nombre as nombreNomina",
            "nom.periodo as periodoNomina",
            "nom.tipoPeriodo as tipoPeriodoNomina",
            "tn.nombre as tipoNovedadNombre"
            ]
        )
        ->join("nomina as nom", "nom.idNomina", "=", "n.fkNomina")
        ->join("tiponovedad as tn", "tn.idtipoNovedad", "=", "n.fkTipoNovedad")
        ->join("empleado as e", "e.idempleado", "=", "n.fkEmpleado")
        ->join("datospersonales as dp", "dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->where("idNovedad","=", $idNovedad)->first();
        $conceptos = DB::table("concepto", "c")
        ->select(["c.*"])
        ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
        ->where("tnc.fkTipoNovedad", "=", $novedad->fkTipoNovedad)->get();

        if(isset($novedad->fkAusencia)){
            $ausencia = DB::table('ausencia')->where("idAusencia","=", $novedad->fkAusencia)->first();
            return view('/novedades.modificar.ausencia',[
                'novedad' => $novedad,
                'ausencia' => $ausencia,
                'conceptos' => $conceptos
            ]);      
        }
        else if(isset($novedad->fkIncapacidad)){
            $incapacidad = DB::table('incapacidad',"i")->select(["i.*","cd.nombre as nmCodDiagnostico","ter.razonSocial as nmTercero","ta.nombre as nmTipoAfilicacion"])
            ->join("cod_diagnostico as cd", "cd.idCodDiagnostico","=","i.fkCodDiagnostico")
            ->join("tipoafilicacion as ta", "ta.idTipoAfiliacion","=","i.fkTipoAfilicacion", "left")
            ->join("tercero as ter", "ter.idTercero","=","i.fkTercero")
            ->where("idIncapacidad","=", $novedad->fkIncapacidad)->first();
            $tiposAfiliacion = DB::table("tipoafilicacion")->whereIn("idTipoAfiliacion", [3,4])->get();

            return view('/novedades.modificar.incapacidad',[
                'novedad' => $novedad,
                'incapacidad' => $incapacidad,
                'conceptos' => $conceptos,
                'tiposAfiliacion' => $tiposAfiliacion

            ]);      
        }
        else if(isset($novedad->fkLicencia)){
            $licencia = DB::table('licencia')->where("idLicencia","=", $novedad->fkLicencia)->first();
            
            return view('/novedades.modificar.licencia',[
                'novedad' => $novedad,
                'licencia' => $licencia,
                'conceptos' => $conceptos
            ]);      
        }
        else if(isset($novedad->fkHorasExtra)){
            $horas_extra = DB::table('horas_extra')->where("idHoraExtra","=", $novedad->fkHorasExtra)->first();

            if(isset($horas_extra->fechaHoraInicial)){
                return view('/novedades.modificar.horas_extra1',[
                    'novedad' => $novedad,
                    'horas_extra' => $horas_extra,
                    'conceptos' => $conceptos
                ]);  
            }
            else{
                return view('/novedades.modificar.horas_extra2',[
                    'novedad' => $novedad,
                    'horas_extra' => $horas_extra,
                    'conceptos' => $conceptos
                ]);  
            }
                
        }
        else if(isset($novedad->fkRetiro)){
            $retiro = DB::table('retiro')->where("idRetiro","=", $novedad->fkRetiro)->first();
            $motivosRetiro = DB::table("motivo_retiro", "m")->orderBy("nombre")->get();
            return view('/novedades.modificar.retiro',[
                'novedad' => $novedad,
                'retiro' => $retiro,
                'motivosRetiro' => $motivosRetiro
            ]);      
        }
        else if(isset($novedad->fkVacaciones)){
            $vacaciones = DB::table('vacaciones')->where("idVacaciones","=", $novedad->fkVacaciones)->first();
            $conceptos = DB::table("concepto", "c")
            ->select(["c.*"])
            ->join("tiponovconceptotipoent AS tnc", "tnc.fkConcepto", "=", "c.idconcepto")
            ->where("tnc.fkTipoNovedad", "=", $novedad->fkTipoNovedad)
            ->where("tnc.fkConcepto", "=", $novedad->fkConcepto)
            ->get();
            
            if( $novedad->fkConcepto == "29"){
                return view('/novedades.modificar.vacaciones',[
                    'novedad' => $novedad,
                    'vacaciones' => $vacaciones,
                    'conceptos' => $conceptos
                ]);
            }
            else{
                return view('/novedades.modificar.vacaciones2',[
                    'novedad' => $novedad,
                    'vacaciones' => $vacaciones,
                    'conceptos' => $conceptos
                ]);
            }
        }
        else if(isset($novedad->fkOtros)){
            
            $otra_novedad = DB::table('otra_novedad')->where("idOtraNovedad","=", $novedad->fkOtros)->first();
            $conceptos = DB::table("concepto", "c")->orderBy("nombre")->get();

            return view('/novedades.modificar.otra_novedad',[
                'novedad' => $novedad,
                'otra_novedad' => $otra_novedad,
                'conceptos' => $conceptos
            ]);
        }
        
    }
    public function modificarNovedadAusencia1(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaAusenciaInicial' => 'required|date',
            'fechaAusenciaFinal' => 'required|date|after_or_equal:fechaAusenciaInicial'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $fechaAusInicial = date("Y-m-d", strtotime($req->fechaAusenciaInicial));
        $fechaAusFinal = date("Y-m-d", strtotime($req->fechaAusenciaFinal));

        $fechaAusenciaInicial = strtotime( $req->fechaAusenciaInicial );
        $fechaAusenciaFinal = strtotime( $req->fechaAusenciaFinal );
        $diff = $fechaAusenciaFinal - $fechaAusenciaInicial;

        $dias = $diff / ( 60 * 60 * 24);
        $dias = floor($dias);

        $restoDias = $diff % ( 60 * 60 * 24);
        

        $horas = $restoDias / ( 60 * 60 );
        $horas = round($horas, 2);

        
        $empleado = DB::table("empleado")->where("idempleado","=", $req->idEmpleado)->first();

        $fecha = new DateTime($fechaAusInicial);
        $arrDiasAdicionales=array();
        
        
        do{

            $domingoSemana = date("Y-m-d", strtotime('next sunday '.$fecha->format('Y-m-d')));
            $sabadoSemana = date("Y-m-d", strtotime('next saturday '.$fecha->format('Y-m-d')));

            if($empleado->sabadoLaborable == "0"){//No trabaja el sabado
                if(!in_array($domingoSemana, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $domingoSemana);
                }
                if(!in_array($sabadoSemana, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $sabadoSemana);
                }
            }
            else{
                if(!in_array($domingoSemana, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $domingoSemana);
                }
            }

            $sql = "'".$fecha->format('Y-m-d')."' BETWEEN fechaInicioSemana AND fechaFinSemana";
            $calendarios = DB::table("calendario")
            ->whereRaw($sql)->get();
            foreach($calendarios as $calendario){
                if(!in_array($calendario->fecha, $arrDiasAdicionales)){
                    array_push($arrDiasAdicionales, $calendario->fecha);
                }
            }


            if($fechaAusFinal != $fecha->format('Y-m-d')){
                $fecha->add(new DateInterval('P1D'));
            }
            
        }
        while($fechaAusFinal != $fecha->format('Y-m-d'));

        $arrDiasAdicionalesFinal = array();

        foreach($arrDiasAdicionales as $arrDiasAdicional){
            $cuentaDiaAdd = DB::table("ausencia","a")->join("novedad AS n", "n.fkAusencia", "=", "a.idAusencia")
            ->where("n.fkEmpleado","=",$req->idEmpleado)
            ->where("a.fechasAdicionales","LIKE", "%".$arrDiasAdicional."%")
            ->get();

            if(sizeof($cuentaDiaAdd)==0){
                array_push($arrDiasAdicionalesFinal, $arrDiasAdicional);
                $dias++;
            }
        }


        

        

        $textoDias = implode(",",$arrDiasAdicionalesFinal);

        $arrAusenciaIns = array(
            "fechaInicio" => $req->fechaAusenciaInicial, 
            "fechaFin" => $req->fechaAusenciaFinal, 
            "cantidadDias" => $dias, 
            "cantidadHoras" => $horas,
            "fechasAdicionales" => $textoDias
        );
        
        

        $cantidad = DB::table('ausencia')->where("idAusencia", "=", $req->idAusencia)->update($arrAusenciaIns);

        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,            
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado            
        );
        DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->update($arrNovedad);

        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }
    public function modificarNovedadLicencia(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'numeric' => 'La :attribute debe ser numerica.'
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaInicial' => 'required|date',
            'dias' => 'required|numeric',
            'fechaFinal' => 'required|date',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $arrLicencia = [
            "numDias" => $req->dias, 
            "fechaInicial" => $req->fechaInicial, 
            "fechaFinal" => $req->fechaFinal, 
        ];
        DB::table('licencia')->where("idLicencia","=",$req->idLicencia)->update($arrLicencia);


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);

        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }
    
    public function modificarNovedadIncapacidad(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'numeric' => 'La :attribute debe ser numerica.'
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaInicial' => 'required|date',
            'dias' => 'required|numeric',
            'fechaFinal' => 'required|date',
            'fechaRealI' => 'required|date',
            'fechaRealF' => 'required|date',
            'codigoDiagnostico' => 'required',
            'pagoTotal' => 'required',
            'tipoAfiliacion' => 'required',
            'terceroEntidad' => 'required',
            'naturaleza' => 'required',
            'tipo' => 'required'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $tipoAfiliacion = null;
        if($req->tipoAfiliacion != "-1"){
            $tipoAfiliacion = $req->tipoAfiliacion;
        }

        $arrIncapacidad=[
            "numDias" => $req->dias, 
            "fechaInicial" => $req->fechaInicial, 
            "fechaFinal" => $req->fechaFinal, 
            "fechaRealI" => $req->fechaRealI, 
            "fechaRealF" => $req->fechaRealF, 
            "pagoTotal" => $req->pagoTotal,
            "fkCodDiagnostico" => $req->idCodigoDiagnostico,
            "numIncapacidad" => $req->numIncapacidad,
            "fkTipoAfilicacion" => $tipoAfiliacion,
            "fkTercero" => $req->idTerceroEntidad,
            "naturaleza" => $req->naturaleza,
            "tipoIncapacidad" => $req->tipo,
        ];
        DB::table('incapacidad')
        ->where("idIncapacidad", "=",$req->idIncapacidad)
        ->update($arrIncapacidad);

        
        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);
        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }

    public function modificarNovedadHoraExtra1(Request $req){
      
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'horaInicial' => 'required|date',
            'horaFinal' => 'required|date|after_or_equal:horaInicial'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $horaI = strtotime( $req->horaInicial );
        $horaF = strtotime( $req->horaFinal );
        $diff = $horaF - $horaI;
        $horas = $diff / ( 60 * 60 );
        $horas = round($horas, 2);

        $arrHora = [
            "cantidadHoras" => $horas, 
            "fechaHoraInicial" => date("Y-m-d H:i:s", $horaI),
            "fechaHoraFinal" => date("Y-m-d H:i:s", $horaF)
        ];

        DB::table('horas_extra')->where("idHoraExtra","=",$req->idHorasExtra)->update($arrHora);

        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);
        
        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);


    }
    public function modificarNovedadHoraExtra2(Request $req){
      
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'numeric' => 'La :attribute debe ser numerica.'
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'cantidadHoras' => 'required|numeric'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        
        $arrHora = [
            "cantidadHoras" => $req->cantidadHoras
        ];

        DB::table('horas_extra')
        ->where("idHoraExtra","=",$req->idHorasExtra)
        ->update($arrHora);
 
        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);

        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }
    public function modificarNovedadRetiro(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'motivoRetiro' => 'required',
            'idEmpleado' => 'required',
            'fechaRetiro' => 'required',
            'fechaRetiroReal' => 'required',
            'indemnizacion' => 'required'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      

        $arrRetiro = [
            "fecha" => $req->fechaRetiro, 
            "fechaReal" => $req->fechaRetiroReal,
            "fkMotivoRetiro" => $req->motivoRetiro,
            "indemnizacion" => $req->indemnizacion
        ];

        DB::table('retiro')->where("idRetiro","=",$req->idRetiro)->update($arrRetiro);

        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);
        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }
    public function modificarNovedadVacaciones(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'fechaInicial' => 'required',
            'dias' => 'required',
            'fechaFinal' => 'required',
            'pagoAnticipado' => 'required'            
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      
        $arrVacaciones = [
            "fechaInicio" => $req->fechaInicial, 
            "fechaFin" => $req->fechaFinal,
            "diasCompensar" => $req->dias,
            "diasCompletos" => $req->diasCompletos,
            "pagoAnticipado" => $req->pagoAnticipado
        ];

        DB::table('vacaciones')->where("idVacaciones","=",$req->idVacaciones)->update($arrVacaciones);


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);
        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }
    public function modificarNovedadVacaciones2(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'dias' => 'required',
            'pagoAnticipado' => 'required'            
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
      
        $arrVacaciones = [
            "diasCompensar" => $req->dias,
            "pagoAnticipado" => $req->pagoAnticipado
        ];

        DB::table('vacaciones')->where("idVacaciones","=",$req->idVacaciones)->update($arrVacaciones);


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);
        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }
    public function modificarNovedadOtros(Request $req){

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'concepto' => 'required',
            'idEmpleado' => 'required',
            'valor' => 'required',
            'sumaResta' => 'required',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $arrOtros = [
            "valor" => $req->valor,
            "sumaResta" => $req->sumaResta
        ];
   
        DB::table('otra_novedad')->where("idOtraNovedad","=",$req->idOtraNovedad)->update($arrOtros);


        $periodoActivoReintegro = DB::table("periodo")
        ->where("fkEstado","=","1")
        ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
        $arrNovedad = array(
            "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
            "fechaRegistro" => $req->fecha,
            "fkConcepto" => $req->concepto,
            "fkEmpleado" => $req->idEmpleado,
        );
        DB::table('novedad')->where("idNovedad","=",$req->idNovedad)->update($arrNovedad);
        $novedad =  DB::table('novedad')->where("idNovedad", "=", $req->idNovedad)->first();
        $ruta ="/novedades/listaNovedades/";
        if($novedad->fkEstado == "3"){
            $ruta ="/novedades/verCarga/".$novedad->fkCargaNovedad;
        }

        return response()->json(['success'=>true, "ruta" => $ruta]);
    }
    public function eliminarSeleccionados(Request $req){

        DB::table('novedad')->whereIn("idNovedad",$req->idNovedad)->update(["fkEstado" => "9"]);
        
        return response()->json([
			"success" => true
        ]);
    }

    public function eliminarSeleccionadosDef(Request $req){




        DB::table('novedad')->whereIn("idNovedad",$req->idNovedad)->delete();
        
        
        return response()->json([
			"success" => true
        ]);
    }
    public function seleccionarArchivoMasivoNovedades(){
        $nominas = DB::table("nomina")->orderBy("nombre")->get();
        $cargas = DB::table("carganovedad")->where("fkEstado", "=","3")->get();

        return view('/novedades.subirNovedades',[
            'nominas' => $nominas,
            'cargas' => $cargas
        ]);
    }
    public function cargaMasivaNovedades(Request $req){
        //3 en estadoEnCreacion
        $csvRuta = "";
        
        if(!isset($req->fkNomina)){
            echo "<script>alert('Selecciona una nomina');
            window.history.back();</script>";
            exit;

        }

        if ($req->hasFile('archivoCSV')) {
            $file = $req->file('archivoCSV');
            $reader = Reader::createFromFileObject($file->openFile());
            $reader->setDelimiter(';');
            
             
            $idCargaNovedad = DB::table('carganovedad')->insertGetId([
                "fkEstado" => "3"
            ], "idCargaNovedad");

            // Create a customer from each row in the CSV file
            foreach ($reader as $index => $row) {



                foreach($row as $key =>$valor){
                    if($valor==""){
                        $row[$key]=null;
                    }
                    else{
                        $row[$key] = utf8_encode($row[$key]);
                        if(strpos($row[$key], "/")){
                        
                            $dt = DateTime::createFromFormat("d/m/Y", $row[$key]);
                            if($dt===false){
                                $dt = DateTime::createFromFormat("d/m/Y H:i", $row[$key]);
                                if($dt !== false){
                                    $ts = $dt->getTimestamp();
                                    $row[$key] = date("Y-m-d H:i:s", $ts);
                                }
                                
                            }
                            else{
                                $ts = $dt->getTimestamp();
                                $row[$key] = date("Y-m-d", $ts);
                            }
                                
                           
                           
                            
                            
                        }
                    }
                }



                $req->fkTipoNovedad = $row[0];
                $req->fkTipoReporte = $row[1];
                $req->fechaRegistro = $row[2];
                $req->concepto = $row[3];
                $empleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=","e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion", "=",$row[5])
                ->get();
                $req->idEmpleado = null;
                if(sizeof($empleado)>0){
                    $req->idEmpleado = $empleado[0]->idempleado;
                }
                else{
                    continue;
                }
                
                


                if($row[0]=="1"){
                    
                    $req->fechaAusenciaInicial = $row[6];
                    $req->fechaAusenciaFinal = $row[7];
                    
                    $fechaAusInicial = date("Y-m-d", strtotime($req->fechaAusenciaInicial));
                    $fechaAusFinal = date("Y-m-d", strtotime($req->fechaAusenciaFinal));
            
                    $fechaAusenciaInicial = strtotime( $req->fechaAusenciaInicial );
                    $fechaAusenciaFinal = strtotime( $req->fechaAusenciaFinal );
                    $diff = $fechaAusenciaFinal - $fechaAusenciaInicial;
            
                    $dias = $diff / ( 60 * 60 * 24);
                    $dias = floor($dias);
            
                    $restoDias = $diff % ( 60 * 60 * 24);
                    
            
                    $horas = $restoDias / ( 60 * 60 );
                    $horas = round($horas, 2);
            
                    
                    $empleado = DB::table("empleado")->where("idempleado","=", $req->idEmpleado)->first();
            
                    $fecha = new DateTime($fechaAusInicial);
                    $arrDiasAdicionales=array();
                    
                    
                    do{
            
                        $domingoSemana = date("Y-m-d", strtotime('next sunday '.$fecha->format('Y-m-d')));
                        $sabadoSemana = date("Y-m-d", strtotime('next saturday '.$fecha->format('Y-m-d')));
            
                        if($empleado->sabadoLaborable == "0"){//No trabaja el sabado
                            if(!in_array($domingoSemana, $arrDiasAdicionales)){
                                array_push($arrDiasAdicionales, $domingoSemana);
                            }
                            if(!in_array($sabadoSemana, $arrDiasAdicionales)){
                                array_push($arrDiasAdicionales, $sabadoSemana);
                            }
                        }
                        else{
                            if(!in_array($domingoSemana, $arrDiasAdicionales)){
                                array_push($arrDiasAdicionales, $domingoSemana);
                            }
                        }
            
                        $sql = "'".$fecha->format('Y-m-d')."' BETWEEN fechaInicioSemana AND fechaFinSemana";
                        $calendarios = DB::table("calendario")
                        ->whereRaw($sql)->get();
                        foreach($calendarios as $calendario){
                            if(!in_array($calendario->fecha, $arrDiasAdicionales)){
                                array_push($arrDiasAdicionales, $calendario->fecha);
                            }
                        }
            
            
                        if($fechaAusFinal != $fecha->format('Y-m-d')){
                            $fecha->add(new DateInterval('P1D'));
                        }
                        
                    }
                    while($fechaAusFinal != $fecha->format('Y-m-d'));
            
                    $arrDiasAdicionalesFinal = array();
            
                    foreach($arrDiasAdicionales as $arrDiasAdicional){
                        $cuentaDiaAdd = DB::table("ausencia","a")->join("novedad AS n", "n.fkAusencia", "=", "a.idAusencia")
                        ->where("n.fkEmpleado","=",$req->idEmpleado)
                        ->where("a.fechasAdicionales","LIKE", "%".$arrDiasAdicional."%")
                        ->get();
            
                        if(sizeof($cuentaDiaAdd)==0){
                            array_push($arrDiasAdicionalesFinal, $arrDiasAdicional);
                            $dias++;
                        }
                    }
            
            
                    
            
                    
            
                    $textoDias = implode(",",$arrDiasAdicionalesFinal);
            
                    $arrAusenciaIns = array(
                        "fechaInicio" => $req->fechaAusenciaInicial, 
                        "fechaFin" => $req->fechaAusenciaFinal, 
                        "cantidadDias" => $dias, 
                        "cantidadHoras" => $horas,
                        "fechasAdicionales" => $textoDias
                    );
                    
                    
            
                    $idAusencia = DB::table('ausencia')->insertGetId($arrAusenciaIns, "idAusencia");

                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkAusencia" => $idAusencia,
                        "fkConcepto" => $req->concepto,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    DB::table('novedad')->insert($arrInsertNovedad);

                }
                else if($row[0]=="2"){

                    $req->dias = $row[8];
                    $req->fechaFinal = $row[10];
                    if(!empty($row[9]) && !empty($row[10])){
                        $datetime1 = new DateTime($row[9]);
                        $datetime2 = new DateTime($row[10]);

                        $interval = $datetime1->diff($datetime2);
                        $req->dias = ($interval->format('%a') + 1);
                    }
                    else if(empty($row[10])){
                        $datetime1 = new DateTime($row[9]);
                        
                        $datetime1->add(new DateInterval('P'.($req->dias - 1).'D'));
                        $req->fechaFinal = $datetime1->format("Y-m-d");
                    }


                    
                    $req->fechaInicial = $row[9];
                    
                    $req->fechaRealI = $row[11];
                    $req->fechaRealF = $row[12];
                    $req->pagoTotal = $row[13];
                    $req->idCodigoDiagnostico = $row[14];
                    


                    $req->numIncapacidad = $row[15];
                    $req->tipoAfiliacion = $row[16];

                    if($req->tipoAfiliacion == "-1"){
                        $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.idTercero"])
                        ->join("empresa AS em","em.fkTercero_ARL","=","t.idTercero")
                        ->join("empleado AS e","e.fkEmpresa","=","em.idempresa")
                        ->where("e.idempleado","=",$req->idEmpleado)->first();

                        $req->idTerceroEntidad = $tercero->idTercero;
                    }
                    else{
                        $tercero = DB::table("afiliacion", "a")->select(["t.razonSocial", "t.idTercero"])
                        ->join("tercero AS t","t.idTercero","=","a.fkTercero")
                        ->where("a.fkTipoAfilicacion","=", $req->tipoAfiliacion)
                        ->where("a.fkEmpleado","=",$req->idEmpleado)->first();
                        $req->idTerceroEntidad = $tercero->idTercero;
                    }
                   
                    $req->naturaleza = $row[17];
                    
                    
                    if($req->naturaleza == "1"){
                        $req->naturaleza = "Accidente de trabajo";
                    }
                    else if($req->naturaleza == "2"){
                        $req->naturaleza = "Enfermedad General o Maternidad";
                    }
                    else if($req->naturaleza == "3"){
                        $req->naturaleza = "Enfermedad Profesional";
                    }


                    $req->tipo = $row[18];
                    if($req->tipo == "1"){
                        $req->tipo = "Ambulatoria";
                    }
                    else if($req->tipo == "2"){
                        $req->tipo = "Hospitalaria";
                    }
                    else if($req->tipo == "3"){
                        $req->tipo = "Maternidad";
                    }
                    else if($req->tipo == "4"){
                        $req->tipo = "Paternidad";
                    }
                    else if($req->tipo == "4"){
                        $req->tipo = "Prorroga";
                    }


                    $tipoAfiliacion = null;
                    if($req->tipoAfiliacion!="-1"){
                        $tipoAfiliacion = ($req->tipoAfiliacion);
                    }

                    $codigoDiagnostico = DB::table("cod_diagnostico")->where("idCodDiagnostico","=", $req->idCodigoDiagnostico)->first();
                    if(!isset($codigoDiagnostico)){
                        dump("idCodDiagnostico invalido");
                        dd($req->idCodigoDiagnostico);
                    }

                    $idIncapacidad = DB::table('incapacidad')->insertGetId([
                        "numDias" => $req->dias, 
                        "fechaInicial" => $req->fechaInicial, 
                        "fechaFinal" => $req->fechaFinal, 
                        "fechaRealI" => $req->fechaRealI, 
                        "fechaRealF" => $req->fechaRealF, 
                        "pagoTotal" => $req->pagoTotal,
                        "fkCodDiagnostico" => $req->idCodigoDiagnostico,
                        "numIncapacidad" => $req->numIncapacidad,
                        "fkTipoAfilicacion" => $tipoAfiliacion,
                        "fkTercero" => $req->idTerceroEntidad,
                        "naturaleza" => $req->naturaleza,
                        "tipoIncapacidad" => $req->tipo,
                    ], "idIncapacidad");

                    
                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkIncapacidad" => $idIncapacidad,
                        "fkConcepto" => $req->concepto,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    DB::table('novedad')->insert($arrInsertNovedad);
                }
                else if($row[0]=="3"){

                    
                    $req->dias = $row[19];
                    $req->fechaInicial = $row[20];
                    $req->fechaFinal = $row[21];


                    $idLicencia = DB::table('licencia')->insertGetId([
                        "numDias" => $req->dias, 
                        "fechaInicial" => $req->fechaInicial, 
                        "fechaFinal" => $req->fechaFinal, 
                    ], "idLicencia");
            
            
                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkLicencia" => $idLicencia,
                        "fkConcepto" => $req->concepto,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    DB::table('novedad')->insert($arrInsertNovedad);
                }
                else if($row[0]=="4" && $row[1]=="1"){

                    $req->horaInicial = $row[22];
                    $req->horaFinal = $row[23];

                    $horaI = strtotime( $req->horaInicial );
                    $horaF = strtotime( $req->horaFinal );



                    $diff = $horaF - $horaI;
                    $horas = $diff / ( 60 * 60 );
                    $horas = round($horas, 2);
            
                    $idHoraExtra = DB::table('horas_extra')->insertGetId([
                        "cantidadHoras" => $horas, 
                        "fechaHoraInicial" => date("Y-m-d H:i:s", $horaI),
                        "fechaHoraFinal" => date("Y-m-d H:i:s", $horaF)
                    ], "idHoraExtra");
            
                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkTipoReporte" => $req->fkTipoReporte,
                        "fkHorasExtra" => $idHoraExtra,
                        "fkConcepto" => $req->concepto,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    
                    
            
                    DB::table('novedad')->insert($arrInsertNovedad);
                }
                else if($row[0]=="4" && $row[1]=="2"){

                    if(strpos($row[24],",")){
                        $row[24] = str_replace(",",".",$row[24]);
                    }
                    $req->cantidadHoras = $row[24];



                    $idHoraExtra = DB::table('horas_extra')->insertGetId([
                        "cantidadHoras" => $req->cantidadHoras, 
                    ], "idHoraExtra");
            
                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkTipoReporte" => $req->fkTipoReporte,
                        "fkHorasExtra" => $idHoraExtra,
                        "fkConcepto" => $req->concepto,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    DB::table('novedad')->insert($arrInsertNovedad);
                }
                else if($row[0]=="5"){

                    
                    $req->fechaRetiro = $row[25];
                    $req->fechaRetiroReal = $row[26];
                    $req->motivoRetiro = $row[27];
                    $req->indemnizacion = $row[28];
                    

                    $idRetiro = DB::table('retiro')->insertGetId([
                        "fecha" => $req->fechaRetiro, 
                        "fechaReal" => $req->fechaRetiroReal,
                        "fkMotivoRetiro" => $req->motivoRetiro,
                        "indemnizacion" => $req->indemnizacion
                    ], "idRetiro");
            
            
                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkRetiro" => $idRetiro,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    DB::table('novedad')->insert($arrInsertNovedad);
                }
                else if($row[0]=="6"){
                    $req->fechaInicial = $row[29];
                    $req->fechaFinal = $row[30];
                    $req->dias = $row[31];
                    $req->pagoAnticipado = $row[32];

                    $idVacaciones = DB::table('vacaciones')->insertGetId([
                        "fechaInicio" => $req->fechaInicial, 
                        "fechaFin" => $req->fechaFinal,
                        "diasCompensar" => $req->dias,
                        "pagoAnticipado" => $req->pagoAnticipado
                    ], "idVacaciones");
            
            
                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkVacaciones" => $idVacaciones,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkConcepto" => $req->concepto,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    DB::table('novedad')->insert($arrInsertNovedad);
                }
                else if($row[0]=="7"){

                    if(strpos($row[33],".")){
                        $row[33] = str_replace(".","",$row[33]);
                    }
                    

                    if(strpos($row[33],",")){
                        $row[33] = str_replace(",",".",$row[33]);
                    }
                    
                    $req->valor = $row[33];
                    $req->sumaResta = $row[34];


                    $idOtraNovedad = DB::table('otra_novedad')->insertGetId([
                        "valor" => $req->valor,
                        "sumaResta" => $req->sumaResta
                    ], "idOtraNovedad");
            
            
                    $periodoActivoReintegro = DB::table("periodo")
                    ->where("fkEstado","=","1")
                    ->where("fkEmpleado", "=", $req->idEmpleado)->first();
        
                    $arrInsertNovedad = array(
                        "fkPeriodoActivo" => $periodoActivoReintegro->idPeriodo,
                        "fkTipoNovedad" => $req->fkTipoNovedad, 
                        "fkNomina" => $req->fkNomina,
                        "fechaRegistro" => $req->fechaRegistro,
                        "fkOtros" => $idOtraNovedad,
                        "fkEmpleado" => $req->idEmpleado,
                        "fkConcepto" => $req->concepto,
                        "fkEstado" => "3",
                        "fkCargaNovedad" => $idCargaNovedad
                    );
                    DB::table('novedad')->insert($arrInsertNovedad);
                }
                

                
            }
            
            return redirect('novedades/verCarga/'.$idCargaNovedad);
        }
    }

    public function verCarga($idCarga){
        $novedades = DB::table("novedad","n")
        ->select([
            "n.*",
            "c.nombre as nombreConcepto",
            "nom.nombre as nombreNomina",
            "em.razonSocial as nombreEmpresa",
            "est.nombre as nombreEstado",
            "ti.nombre as tipoDocumento",
            "dp.numeroIdentificacion",
            "dp.primerNombre",
            "dp.segundoNombre",
            "dp.primerApellido",
            "dp.segundoApellido"
        ])
        ->join("concepto as c","c.idconcepto", "=", "n.fkConcepto", "left")
        ->join("nomina as nom","nom.idNomina", "=", "n.fkNomina")
        ->join("empresa as em","em.idempresa", "=", "nom.fkEmpresa")
        ->join("estado as est","est.idestado", "=", "n.fkEstado")
        ->join("empleado as e","e.idempleado", "=", "n.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
        ->where("n.fkCargaNovedad","=",$idCarga)
        ->get();
        
        return view('/novedades.listaNovedadesCarga',[
            'novedades' => $novedades,
            'idCarga' => $idCarga
        ]);        
    }
    public function cancelarSubida($idCarga){
        DB::table('carganovedad')
        ->where("idCargaNovedad", "=",$idCarga)->delete();
        return redirect('novedades/seleccionarArchivoMasivoNovedades/');
    }
    public function aprobarSubida($idCarga){
        DB::table('carganovedad')
        ->where("idCargaNovedad", "=",$idCarga)->update(["fkEstado" => "5"]);

        DB::table('novedad')->where("fkCargaNovedad","=",$idCarga)->update(["fkEstado" => "7"]);

        return redirect('novedades/listaNovedades/');
    }
    

}
