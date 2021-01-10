<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use SplTempFileObject;
use DateTime;
use DateInterval;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;

class NominaController extends Controller
{
    

    public function solicitudLiquidacion(Request $req){

        $liquidaciones = DB::table("liquidacionnomina", "ln")
        ->select(["ln.idLiquidacionNomina", "ln.fechaLiquida", "e.razonSocial", "tl.nombre as tipoLiquidacion", "est.nombre as estado","n.nombre as nomNomina"])
        ->join("nomina AS n","ln.fkNomina", "=", "n.idnomina")
        ->join("empresa AS e","n.fkEmpresa","=", "e.idempresa")
        ->join("tipoliquidacion AS tl","ln.fkTipoLiquidacion","=", "tl.idTipoLiquidacion")        
        ->join("estado AS est","ln.fkEstado","=", "est.idestado")
        ->where("ln.fkEstado", "<>", "5")
        ->where("ln.fkEstado", "<>", "8");
        if(isset($req->fechaInicio)){
            $liquidaciones = $liquidaciones->where("ln.fechaLiquida",">=",$req->fechaInicio);
        }
        
        if(isset($req->fechaFin)){
            $liquidaciones = $liquidaciones->where("ln.fechaLiquida","<=",$req->fechaFin);
        }

        if(isset($req->nomina)){
            $liquidaciones = $liquidaciones->where("ln.fkNomina","=",$req->nomina);
        }
        if(isset($req->tipoLiquidacion)){
            $liquidaciones = $liquidaciones->where("ln.fkTipoLiquidacion","=",$req->tipoLiquidacion);
        }

        $liquidaciones = $liquidaciones->get();
        $nominas = DB::table("nomina")->orderBy("nombre")->get();
        $tipoLiquidaciones = DB::table("tipoliquidacion")->orderBy("nombre")->get();

        return view('/nomina.solicitudes.listaSolicitudes',[
            'liquidaciones' => $liquidaciones,
            "nominas" => $nominas,
            "tipoLiquidaciones" => $tipoLiquidaciones,
            "req" => $req

        ]);
    }
    
    public function centroCostoPeriodo(){
        $distri_centro_costo = DB::table("distri_centro_costo", "d")
        ->join("nomina as n", "n.idNomina", "=","d.fkNomina")
        ->where("fkEstado", "=","1")
        ->paginate(15);
        
        return view('/nomina.distri.distribucionCentroCosto',[
            'distris_centro_costo' => $distri_centro_costo,
        ]);
    }
    public function centroCostoPeriodoFormAdd(){
        $nominas = DB::table("nomina")->orderBy("nombre")->get();
        return view('/nomina.distri.addDistri',[
            'nominas' => $nominas
        ]);
    }
    public function insertDistri(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'fkNomina' => 'required',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $arrDistri = [
            "fkNomina" => $req->fkNomina,
            "fechaInicio" => $req->fechaInicio,
            "fechaFin" => $req->fechaFin,
        ];
        $idDistri = DB::table("distri_centro_costo")->insertGetId($arrDistri,  "id_distri_centro_costo");

        return response()->json([
            "success" => true,
            "idDistri" => $idDistri
        ]);
    }
    
    public function modificarDistriIndex($idDistri){
        
        $distri = DB::table("distri_centro_costo", "d")
        ->join("nomina as n", "n.idNomina", "=","d.fkNomina")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->first();
        $empleados = DB::table("empleado", "e")
        ->select("e.idempleado", "dp.primerNombre", "dp.segundoNombre", "dp.primerApellido", "dp.segundoApellido", "dp.numeroIdentificacion", "ti.nombre as tipoDocumento")
        ->join("distri_centro_costo as d", "d.fkNomina", "=","e.fkNomina")
        ->join("datospersonales as dp", "dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti", "ti.idtipoIdentificacion", "=","dp.fkTipoIdentificacion")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->orderByRaw("dp.primerApellido, dp.segundoApellido, dp.primerNombre, dp.segundoNombre")
        ->get();

        $centrosCostoGen = DB::table("centrocosto","cc")
        ->select("cc.*")
        ->join("nomina as n", "n.fkEmpresa", "=","cc.fkEmpresa")
        ->join("distri_centro_costo as d", "d.fkNomina", "=","n.idNomina")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->orderBy("cc.id_uni_centro")
        ->get();

        $arrEmpleadoCC = array();
        foreach($empleados as $empleado){

            $centrosCostoEmpleado = DB::table("distri_centro_costo_centrocosto", "ddc")
            ->where("ddc.fkEmpleado","=",$empleado->idempleado)
            ->where("ddc.fkDistribucion","=",$idDistri)
            ->get();
            $arrCentrosCosto = array();

            if(sizeof($centrosCostoEmpleado) > 0){
                foreach($centrosCostoEmpleado as $centroCostoEmpleado ){
                    array_push($arrCentrosCosto, [
                        "centroCosto" => $centroCostoEmpleado->fkCentroCosto,
                        "porcentaje" => $centroCostoEmpleado->porcentaje
                    ]);
                }
            }
            else{
                $centrosCosto = DB::table("empleado_centrocosto", "ecc")
                ->where("ecc.fkEmpleado", "=",$empleado->idempleado)
                ->get();
                foreach($centrosCosto as $centroCosto){
                    array_push($arrCentrosCosto, [
                        "centroCosto" => $centroCosto->fkCentroCosto,
                        "porcentaje" => $centroCosto->porcentajeTiempoTrabajado
                    ]);
                }
            }

            $arrEmpleadoCC[$empleado->idempleado] = $arrCentrosCosto;
        }


        return view('/nomina.distri.modDistri',[
            'distri' => $distri,
            'arrEmpleadoCC' => $arrEmpleadoCC,
            'centrosCostoGen' => $centrosCostoGen,
            "empleados" => $empleados            
        ]);
    }
    public function editarDistriEm($idEmpleado, $idDistri){
        $centrosCostoGen = DB::table("centrocosto","cc")
        ->select("cc.*")
        ->join("nomina as n", "n.fkEmpresa", "=","cc.fkEmpresa")
        ->join("distri_centro_costo as d", "d.fkNomina", "=","n.idNomina")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->orderBy("cc.id_uni_centro")
        ->get();

        $centrosCostoEmpleado = DB::table("distri_centro_costo_centrocosto", "ddc")
        ->where("ddc.fkEmpleado","=",$idEmpleado)
        ->where("ddc.fkDistribucion","=",$idDistri)
        ->get();
        $arrCentrosCosto = array();

        if(sizeof($centrosCostoEmpleado) > 0){
            foreach($centrosCostoEmpleado as $centroCostoEmpleado ){
                array_push($arrCentrosCosto, [
                    "centroCosto" => $centroCostoEmpleado->fkCentroCosto,
                    "porcentaje" => $centroCostoEmpleado->porcentaje
                ]);
            }
        }
        else{
            $centrosCosto = DB::table("empleado_centrocosto", "ecc")
            ->where("ecc.fkEmpleado", "=",$idEmpleado)
            ->get();
            foreach($centrosCosto as $centroCosto){
                array_push($arrCentrosCosto, [
                    "centroCosto" => $centroCosto->fkCentroCosto,
                    "porcentaje" => $centroCosto->porcentajeTiempoTrabajado
                ]);
            }
        }

        $arrEmpleadoCC = $arrCentrosCosto;
        
        
        return view('/nomina.distri.editarDistriEm',[
            'centrosCostoGen' => $centrosCostoGen,   
            'arrEmpleadoCC' => $arrEmpleadoCC,
            "idDistri" => $idDistri,
            "idEmpleado" => $idEmpleado
        ]);
    }
    
    public function modDistriEmp(Request $req){
        $porcentajeTotal = 0;
        foreach($req->idCentroCosto as $row => $idCentroCosto){
            $porcentajeTotal = $porcentajeTotal + $req->porcentajeCentro[$row];
        }
        if($porcentajeTotal != 100){
            return response()->json([
                "success" => false, 
                "mensaje" => "La suma de porcentajes no es un 100%"
            ]);
        }
        foreach($req->idCentroCosto as $row => $idCentroCosto){
            $dcCentroCosto = DB::table("distri_centro_costo_centrocosto", "dcc")
            ->where("dcc.fkEmpleado", "=",$req->idEmpleado)
            ->where("dcc.fkDistribucion", "=",$req->idDistri)
            ->where("dcc.fkCentroCosto", "=",$idCentroCosto)
            ->first();
            $arrDCC= [
                "fkEmpleado" => $req->idEmpleado,
                "fkDistribucion" => $req->idDistri,
                "fkCentroCosto" => $idCentroCosto,
                "porcentaje" => $req->porcentajeCentro[$row],
            ];
            if(isset($dcCentroCosto)){
                DB::table("distri_centro_costo_centrocosto")
                ->where("idDistriCentroCentro", "=", $dcCentroCosto->idDistriCentroCentro)
                ->update($arrDCC);
            }
            else{
                DB::table("distri_centro_costo_centrocosto")
                ->insert($arrDCC);
            }
        }
        return response()->json([
            "success" => true
        ]);
    }

    public function modificarDistribucion(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'fkNomina' => 'required',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $arrDistri = [
            "fechaInicio" => $req->fechaInicio,
            "fechaFin" => $req->fechaFin,
        ];
        DB::table("distri_centro_costo")->where("id_distri_centro_costo", "=",$req->id_distri_centro_costo)->update($arrDistri);

        return response()->json([
            "success" => true,
        ]);
    }
    
    public function copiarDistri($idDistri){
        $distri = DB::table("distri_centro_costo", "d")
        ->join("nomina as n", "n.idNomina", "=","d.fkNomina")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->first();

        return view('/nomina.distri.copyDistri',[
            'distri' => $distri        
        ]);
    }

    public function copyDistriBd(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'fkNomina' => 'required',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $sqlWhere = "( 
            ('".$req->fechaInicio."' BETWEEN dcc.fechaInicio AND dcc.fechaFin) OR
            ('".$req->fechaFin."' BETWEEN dcc.fechaInicio AND dcc.fechaFin) OR
            (dcc.fechaInicio BETWEEN '".$req->fechaInicio."' AND '".$req->fechaFin."') OR
            (dcc.fechaFin BETWEEN '".$req->fechaInicio."' AND '".$req->fechaFin."')
        )";

        $comparteFecha =  DB::table("distri_centro_costo","dcc")
        ->where("dcc.fkNomina", "=",$req->fkNomina)
        ->whereRaw($sqlWhere)
        ->first();
        if(isset($comparteFecha)){
            return response()->json(['succes'=>false, "mensaje" => "La nueva distribucion comparte fechas con otra"]);
        }



        $arrDistri = [
            "fkNomina" => $req->fkNomina,
            "fechaInicio" => $req->fechaInicio,
            "fechaFin" => $req->fechaFin,
        ];
        $idDistri = DB::table("distri_centro_costo")->insertGetId($arrDistri,  "id_distri_centro_costo");

        $distri_centro_costo_centrocosto = DB::table("distri_centro_costo_centrocosto", "dcc")
        ->where("dcc.fkDistribucion","=",$req->id_distri_centro_costo)
        ->get();

        foreach($distri_centro_costo_centrocosto as $distri_centro_costo_centro){
            $arrDCC= [
                "fkEmpleado" => $distri_centro_costo_centro->fkEmpleado,
                "fkDistribucion" => $idDistri,
                "fkCentroCosto" => $distri_centro_costo_centro->fkCentroCosto,
                "porcentaje" => $distri_centro_costo_centro->porcentaje,
            ];
            DB::table("distri_centro_costo_centrocosto")->insert($arrDCC);
        }


        return response()->json([
            "success" => true            
        ]);
    }
    
    public function subirPlano(Request $req){

        $idDistri = $req->id_distri_centro_costo;
        $distri = DB::table("distri_centro_costo")->where("id_distri_centro_costo","=",$idDistri)->first();
        
        $errors = array();

        $csv = $req->file("archivoCSV");
        $reader = Reader::createFromFileObject($csv->openFile());
        $reader->setDelimiter(';');
        $arrCentroCosto = array();
        foreach($reader as $row => $read){
            if($row == 0){
                foreach($read as $idCol =>$cols){
                    if($idCol > 1){
                        $centroCostoEmpresa = DB::table("centrocosto","cc")
                        ->join("empresa as e", "e.idempresa", "=","cc.fkEmpresa")
                        ->join("nomina as n", "n.fkEmpresa", "=","e.idempresa")                        
                        ->where("n.idNomina", "=",$distri->fkNomina)
                        ->where("cc.idcentroCosto", "=",$cols)
                        ->get();
                        if(sizeof($centroCostoEmpresa)>0){
                            $arrCentroCosto[$idCol] = $cols;
                        }
                    }
                }
            }
            else{
                Log::debug($read);
                $existeEmpleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion","=", $read[1])
                ->where("dp.fkTipoIdentificacion","=", $read[0])
                ->first();
                if(isset($existeEmpleado)){
                    $porcentaje = 0;

                    foreach($read as $idCol =>$cols){
                       
                    
                        if($idCol > 1 && isset($arrCentroCosto[$idCol])){
                            $porcentaje = $porcentaje + floatval($cols);
                        }
                    }
                    
                    
                     
                    if($porcentaje == 100){
                        foreach($read as $idCol =>$cols){
                            
                            if($idCol > 1  && isset($arrCentroCosto[$idCol])){
                                $dcCentroCosto = DB::table("distri_centro_costo_centrocosto", "dcc")
                                ->where("dcc.fkEmpleado", "=",$existeEmpleado->idempleado)
                                ->where("dcc.fkDistribucion", "=",$idDistri)
                                ->where("dcc.fkCentroCosto", "=",$arrCentroCosto[$idCol])
                                ->first();
                                $arrDCC= [
                                    "fkEmpleado" => $existeEmpleado->idempleado,
                                    "fkDistribucion" => $idDistri,
                                    "fkCentroCosto" => $arrCentroCosto[$idCol],
                                    "porcentaje" => floatval($cols),
                                ];
                                if(isset($dcCentroCosto)){
                                    DB::table("distri_centro_costo_centrocosto")
                                    ->where("idDistriCentroCentro", "=", $dcCentroCosto->idDistriCentroCentro)
                                    ->update($arrDCC);
                                }
                                else{
                                    DB::table("distri_centro_costo_centrocosto")
                                    ->insert($arrDCC);
                                }  
                            }
                            
                         }
                        
                    }
                    else if($porcentaje != 100){
                        foreach($read as $idCol =>$cols){
                            if(!isset($arrCentroCosto[$idCol]) && $idCol > 1){
                                array_push($errors, ["idEmpleado" => $read[1], "msj" => "Centro de costo de la columna (".($idCol + 1).") no existe o pertenece a otra empresa"]);
                                break;
                            }
                        }
                        array_push($errors, ["idEmpleado" => $read[1], "msj" => "Porcentaje es diferente a 100 (No se efectuaron cambios)"]);
                    }
                    
                }
                else{
                    array_push($errors, ["idEmpleado" => $read[1], "msj" => "Empleado no existe"]);
                }
                
                
            }
            
        }
        $distri = DB::table("distri_centro_costo", "d")
        ->join("nomina as n", "n.idNomina", "=","d.fkNomina")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->first();
        $empleados = DB::table("empleado", "e")
        ->select("e.idempleado", "dp.primerNombre", "dp.segundoNombre", "dp.primerApellido", "dp.segundoApellido", "dp.numeroIdentificacion", "ti.nombre as tipoDocumento")
        ->join("distri_centro_costo as d", "d.fkNomina", "=","e.fkNomina")
        ->join("datospersonales as dp", "dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti", "ti.idtipoIdentificacion", "=","dp.fkTipoIdentificacion")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->orderByRaw("dp.primerApellido, dp.segundoApellido, dp.primerNombre, dp.segundoNombre")
        ->get();

        $centrosCostoGen = DB::table("centrocosto","cc")
        ->select("cc.*")
        ->join("nomina as n", "n.fkEmpresa", "=","cc.fkEmpresa")
        ->join("distri_centro_costo as d", "d.fkNomina", "=","n.idNomina")
        ->where("d.id_distri_centro_costo", "=",$idDistri)
        ->orderBy("cc.id_uni_centro")
        ->get();

        $arrEmpleadoCC = array();
        foreach($empleados as $empleado){

            $centrosCostoEmpleado = DB::table("distri_centro_costo_centrocosto", "ddc")
            ->where("ddc.fkEmpleado","=",$empleado->idempleado)
            ->where("ddc.fkDistribucion","=",$idDistri)
            ->get();
            $arrCentrosCosto = array();

            if(sizeof($centrosCostoEmpleado) > 0){
                foreach($centrosCostoEmpleado as $centroCostoEmpleado ){
                    array_push($arrCentrosCosto, [
                        "centroCosto" => $centroCostoEmpleado->fkCentroCosto,
                        "porcentaje" => $centroCostoEmpleado->porcentaje
                    ]);
                }
            }
            else{
                $centrosCosto = DB::table("empleado_centrocosto", "ecc")
                ->where("ecc.fkEmpleado", "=",$empleado->idempleado)
                ->get();
                foreach($centrosCosto as $centroCosto){
                    array_push($arrCentrosCosto, [
                        "centroCosto" => $centroCosto->fkCentroCosto,
                        "porcentaje" => $centroCosto->porcentajeTiempoTrabajado
                    ]);
                }
            }

            $arrEmpleadoCC[$empleado->idempleado] = $arrCentrosCosto;
        }


        return view('/nomina.distri.modDistri',[
            'distri' => $distri,
            'arrEmpleadoCC' => $arrEmpleadoCC,
            'centrosCostoGen' => $centrosCostoGen,
            "empleados" => $empleados,
            "errors" => $errors
        ]);
        
        
    }




    public function agregarSolicitudLiquidacion(){
        $nominas = DB::table("nomina")->orderBy("nombre")->get();
        $tiposliquidaciones = DB::table("tipoliquidacion")->get();

        return view('/nomina.solicitudes.agregarSolicitud',[
            'nominas' => $nominas,
            'tiposliquidaciones' => $tiposliquidaciones            
        ]);
    }
    public function cargarFechaPagoxNomina($idNomina, $idTipoLiquidacion){


        
        $nomina = DB::table("nomina")->where("idNomina", "=", $idNomina)->first();
        
        $periodoNomina = $nomina->periodo." ".$nomina->tipoPeriodo;
        
        $liquidacionNomina = DB::table("liquidacionnomina")
            ->where("fkNomina", "=", $idNomina)
            //->where("fkTipoLiquidacion", "=", $idTipoLiquidacion)//Normal
            ->where("fkEstado", "=", "5")//Terminada            
            ->orderBy("idLiquidacionNomina","desc")->first();

        $fechaPagoDeseada="";
        $fechaMinima=date('Y-m-01');
        $fechaProximoInicio = "";
        $fechaProximoFin = "";

        if(isset($liquidacionNomina->fechaLiquida)){            
            $fechaMinima = date('Y-m-d', strtotime($liquidacionNomina->fechaProximaInicio)); 
            $fechaPagoDeseada = date('Y-m-d', strtotime($liquidacionNomina->fechaProximaFin));

            $fechaProximoInicio = date('Y-m-d', strtotime($liquidacionNomina->fechaProximaFin." +1 day")); 
            
            if($nomina->periodo == 30){
                $fechaProximoFin = date('Y-m-t', strtotime($fechaProximoInicio)); 
            }
            else if($nomina->periodo == 15){
                $fechaProximoFin = date('Y-m-d', strtotime($fechaProximoInicio." +".($nomina->periodo - 1)." day"));     
            }
            else{
                $fechaProximoFin = date('Y-m-d', strtotime($fechaProximoInicio." +".($nomina->periodo - 1)." day")); 

            }

        }
        else{
            if($nomina->periodo == 15){
                if(intval(date("d"))>15){
                    $fechaMinima = date('Y-m-16'); 
                    $fechaPagoDeseada = date("Y-m-t");                        
                    $fechaProximoInicio = date('Y-m-d', strtotime($fechaPagoDeseada." +1 day")); 
                    $fechaProximoFin = date('Y-m-d', strtotime($fechaProximoInicio." +".$nomina->periodo." day")); 
                }
                else{
                    $fechaMinima = date('Y-m-01'); 
                    $fechaPagoDeseada = date("Y-m-15");

                    $fechaProximoInicio = date('Y-m-16'); 
                    $fechaProximoFin = date("Y-m-t");    
                }
            }
            else if($nomina->periodo == 30){
                
                $fechaMinima = date('Y-m-01'); 
                $fechaPagoDeseada = date("Y-m-t");
                $fechaProximoInicio = date('Y-m-d', strtotime($fechaPagoDeseada." +1 day")); 
                $fechaProximoFin = date("Y-m-t", strtotime($fechaProximoInicio));
            }
        }
        return response()->json([
            "success" => true,
            "periodo" => $nomina->periodo,
            "periodoNomina" => $periodoNomina,
            "fechaPagoDeseada" => $fechaPagoDeseada,
            "fechaMinima" => $fechaMinima,
            "fechaProximoInicio" => $fechaProximoInicio,
            "fechaProximoFin" => $fechaProximoFin
        ]);
    }

    public function insertarSolicitud(Request $req){
        
        $arrSinEsos = array();
        if(isset($req->excluirEmpleados) && !empty($req->excluirEmpleados)){
            $excluidos = explode(",",$req->excluirEmpleados);
            array_pop($excluidos);
            $arrSinEsos = $excluidos;
        }

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'nomina' => 'required',
            'tipoliquidacion' => 'required',
            'fecha' => 'required|date',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date',
            'fechaInicioProx' => 'required|date',
            'fechaFinProx' => 'required|date'
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $cierreAnte = DB::table("cierre","c")
        ->join("nomina as n","n.fkEmpresa", "=", "c.fkEmpresa")
        ->where("n.idNomina","=",$req->nomina)
        ->where("c.mes","=", date("m",strtotime($req->fecha)))
        ->where("c.anio","=", date("Y",strtotime($req->fecha)))
        ->where("c.fkEstado","=","1")
        ->first();
        //dd($cierreAnte);
        if(isset($cierreAnte)){
            return response()->json(['error'=>["Ya se cerró ese periodo"]]);
        }
        


        $empleados = DB::table('empleado', 'e')
        ->whereNotIn("e.idempleado", $arrSinEsos)
        ->where("e.fkNomina","=", $req->nomina)
        ->where("e.fkEstado","=", "1")//Estado Activo 
        ->where("e.fechaIngreso", "<=", $req->fechaFin);
        if($req->tipoliquidacion == "3"){
            $empleados =  $empleados->whereRaw("e.idempleado in(select n.fkEmpleado from novedad as n where n.fkRetiro is not null and n.fkEmpleado = e.idempleado) ");
        }
        if($req->tipoliquidacion == "7" || $req->tipoliquidacion == "6"){
            $empleados =  $empleados->where("e.tipoRegimen","=","Ley 50");
        }

        if($req->tipoliquidacion != "7" && $req->tipoliquidacion != "3" ){
            $empleados =  $empleados->whereRaw("e.idempleado not in(
                SELECT fkEmpleado from boucherpago as bp WHERE bp.fkLiquidacion in 
                    (SELECT ln.idLiquidacionNomina FROM liquidacionnomina as ln where ln.fechaInicio = '".$req->fechaInicio."' 
                                                                                and fkTipoLiquidacion not in ('7','3')
                    )
            )");
        }
        else{
            $empleados =  $empleados->whereRaw("e.idempleado not in(
                SELECT fkEmpleado from boucherpago as bp WHERE bp.fkLiquidacion in 
                    (SELECT ln.idLiquidacionNomina FROM liquidacionnomina as ln where ln.fechaInicio = '".$req->fechaInicio."' 
                                                                                and fkTipoLiquidacion in ('".$req->tipoliquidacion."')
                    )
            )");
        }
        

        
        $empleados = $empleados->get();




        if(sizeof($empleados)==0){
            return response()->json(['error'=>["No hay empleados para este periodo"]]);
        }

        //Consultar si existe alguna liquidacion previa no terminada 
        $liquidacionesNomina = DB::table("liquidacionnomina")
            ->where("fkNomina", "=", $req->nomina)
            ->where("fkTipoLiquidacion", "=", $req->tipoliquidacion)
            ->where("fkEstado", "<>", "5")//Terminada            
            ->orderBy("idLiquidacionNomina","desc")->get();
        if(sizeof($liquidacionesNomina)>0){
            return response()->json(['error'=>["Esta nomina ya se encuentra en liquidacion, termine primero la liquidacion actual"]]);
        }

        $arrLiquidacionNomina = [
            "fechaInicio" => $req->fechaInicio,
            "fechaFin" => $req->fechaFin,
            "fechaLiquida" => $req->fecha, 
            "fechaProximaInicio" => $req->fechaInicioProx, 
            "fechaProximaFin" => $req->fechaFinProx, 
            "fkNomina" => $req->nomina, 
            "fkTipoLiquidacion" => $req->tipoliquidacion
        ];

        if($req->tipoliquidacion == "6"){
            $arrLiquidacionNomina["tipoliquidacionPrima"] = $req->tipoliquidacionPrima;
            $arrLiquidacionNomina["fechaPrima"] = $req->fechaPrima;
            $arrLiquidacionNomina["porcentajePrima"] = $req->porcentajePrima;
            $arrLiquidacionNomina["valorFijoPrima"] = $req->valorFijoPrima;

        }
        if($req->tipoliquidacion == "7"){
            $arrLiquidacionNomina["fechaPrima"] = $req->fechaPrima;
        }

        $idLiquidacionNomina = DB::table('liquidacionnomina')->insertGetId($arrLiquidacionNomina, "idLiquidacionNomina");

        $variables = DB::table("variable")->where("idVariable","=","1")->first();
        $salarioMinimoDia = $variables->valor / 30;

        $salarioMaximoDia = ($variables->valor * 25) / 30;

        foreach($empleados as $empleado){
            $this->calcularLiquidacionEmpleado($empleado->idempleado, $idLiquidacionNomina);
        }
        
        return response()->json([
            "success" => true
        ]);
        


    }
    public function recalcularNomina($idLiquidacionNomina){
        $boucherpagos = DB::table("boucherpago")->where("fkLiquidacion","=",$idLiquidacionNomina)->get();

    
        foreach($boucherpagos as $boucherpago){
            $this->calcularLiquidacionEmpleado($boucherpago->fkEmpleado, $boucherpago->fkLiquidacion, $boucherpago->idBoucherPago);
        }
        return response()->json([
            "success" => true
        ]);
        
    }

    public function calcularFormulaxArray($idConcepto, $arrValorxConcepto, $tipoSalario, $periodo){
        $formulasConceptos = DB::table("formulaconcepto")
        ->where("fkConcepto","=",$idConcepto)
        ->orderBy("idformulaConcepto")
        ->get();
        $valor1 = 0;
        $valor2 = 0;
        $valorf = 0;
        $variables = DB::table("variable")->where("idVariable","=","1")->first();
        $salarioMaximo = ($variables->valor * 25) / 30;
        $salarioMaximo = $salarioMaximo * $periodo;

        foreach($formulasConceptos as $formulaConcepto){        
            //VALOR 1
            if(isset($formulaConcepto->fkFormulaConcepto)){
                $valor1=$valorf;
            }
            else if(isset($formulaConcepto->fkConceptoInicial)){
                if(isset($arrValorxConcepto[$formulaConcepto->fkConceptoInicial])){
                    $valor1=floatval($arrValorxConcepto[$formulaConcepto->fkConceptoInicial]['valor']);
                }
            }
            else if(isset($formulaConcepto->fkGrupoConceptoInicial)){
                $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                    ->where("gcc.fkGrupoConcepto", "=", $formulaConcepto->fkGrupoConceptoInicial)                       
                    ->get();
                foreach($grupoConceptoCalculo as $grupoConcepto){
                    if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                        $valor1= $valor1 + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                    }
                }
                if($tipoSalario=="Salario Integral" && $valor1 > $salarioMaximo){
                    $valor1 = $salarioMaximo;
                }
            }
            else if(isset($formulaConcepto->fkVariableInicial)){
                $variableCalculo = DB::table('variable')->where("idVariable","=",$formulaConcepto->fkVariableFinal)->first();
                $valor1 = floatval($variableCalculo->valor);
            }
            else if(isset($formulaConcepto->valorInicial)){
                $valor1 = floatval($formulaConcepto->valorInicial);
            }
            //VALOR 2
            if(isset($formulaConcepto->fkConceptoFinal)){
                if(isset($arrValorxConcepto[$formulaConcepto->fkConceptoFinal])){
                    $valor2=floatval($arrValorxConcepto[$formulaConcepto->fkConceptoFinal]['valor']);
                }
            }
            else if(isset($formulaConcepto->fkGrupoConceptoFinal)){
                $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                    ->where("gcc.fkGrupoConcepto", "=", $formulaConcepto->fkGrupoConceptoFinal)                       
                    ->get();

                foreach($grupoConceptoCalculo as $grupoConcepto){
                    if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                        $valor1= $valor1 + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                    }
                }
                
            }
            else if(isset($formulaConcepto->fkVariableFinal)){
                $variableFinal = DB::table('variable')->where("idVariable","=",$formulaConcepto->fkVariableFinal)->first();
                $valor2 = floatval($variableFinal->valor);
            }
            else if(isset($formulaConcepto->valorFinal)){
                $valor2 = floatval($formulaConcepto->valorFinal);
            }

            //VALOR F 
            if($formulaConcepto->fkTipoOperacion=="1"){//Suma
                $valorf = $valor1 + $valor2;
            }
            else if($formulaConcepto->fkTipoOperacion=="2"){//Resta
                $valorf = $valor1 - $valor2;
            }
            else if($formulaConcepto->fkTipoOperacion=="3"){//Multiplicacion
                $valorf = $valor1 * $valor2;
            }
            else if($formulaConcepto->fkTipoOperacion=="4"){//Division
                if($valor2 != 0){
                    $valorf = $valor1 / $valor2;
                }                                
            }    
        }
        return $valorf;
    }


    public function calcularValoresxConceptoxEmpleado($idConcepto, $idEmpleado){
        $formulasConceptos = DB::table("formulaconcepto")
        ->where("fkConcepto","=",$idConcepto)
        ->orderBy("idformulaConcepto")
        ->get();
        $valor1 = 0;
        $valor2 = 0;
        $valorf = 0;
        foreach($formulasConceptos as $formulaConcepto){
        
            //VALOR 1
            if(isset($formulaConcepto->fkFormulaConcepto)){
                $valor1=$valorf;
            }
            else if(isset($formulaConcepto->fkConceptoInicial)){
                $conceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                    ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                    ->where("conceptofijo.fkConcepto","=", $formulaConcepto->fkConceptoInicial)
                    ->where("conceptofijo.fkEstado","=","1")
                    ->first();
                $valor1=floatval($conceptoCalculo->totalValor);
            }
            else if(isset($formulaConcepto->fkGrupoConceptoInicial)){

                $grupoConceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                    ->join("grupoconcepto_concepto as gcc", "gcc.fkConcepto","=","conceptofijo.fkConcepto")
                    ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                    ->where("conceptofijo.fkEstado","=","1")
                    ->where("gcc.fkGrupoConcepto", "=", $formulaConcepto->fkGrupoConceptoInicial)                       
                    ->first();

                $valor1=floatval($grupoConceptoCalculo->totalValor);
            }
            else if(isset($formulaConcepto->fkVariableInicial)){
                $variableCalculo = DB::table('variable')->where("idVariable","=",$formulaConcepto->fkVariableFinal)->first();
                $valor1 = floatval($variableCalculo->valor);
            }
            else if(isset($formulaConcepto->valorInicial)){
                $valor1 = floatval($formulaConcepto->valorInicial);
            }
            //VALOR 2
            if(isset($formulaConcepto->fkConceptoFinal)){
                $conceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                    ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                    ->where("conceptofijo.fkConcepto","=", $formulaConcepto->fkConceptoFinal)
                    ->where("conceptofijo.fkEstado","=","1")
                    ->first();
                $valor2=floatval($conceptoCalculo->totalValor);
            }
            else if(isset($formulaConcepto->fkGrupoConceptoFinal)){
                $grupoConceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                    ->join("grupoconcepto_concepto as gcc", "gcc.fkConcepto","=","conceptofijo.fkConcepto")
                    ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                    ->where("conceptofijo.fkEstado","=","1")
                    ->where("gcc.fkGrupoConcepto", "=", $formulaConcepto->fkGrupoConceptoFinal)                       
                    ->first();

                $valor2=floatval($grupoConceptoCalculo->totalValor);
            }
            else if(isset($formulaConcepto->fkVariableFinal)){
                $variableFinal = DB::table('variable')->where("idVariable","=",$formulaConcepto->fkVariableFinal)->first();
                $valor2 = floatval($variableFinal->valor);
            }
            else if(isset($formulaConcepto->valorFinal)){
                $valor2 = floatval($formulaConcepto->valorFinal);
            }

            //VALOR F 
            if($formulaConcepto->fkTipoOperacion=="1"){//Suma
                $valorf = $valor1 + $valor2;
            }
            else if($formulaConcepto->fkTipoOperacion=="2"){//Resta
                $valorf = $valor1 - $valor2;
            }
            else if($formulaConcepto->fkTipoOperacion=="3"){//Multiplicacion
                $valorf = $valor1 * $valor2;
            }
            else if($formulaConcepto->fkTipoOperacion=="4"){//Division
                if($valor2 != 0){
                    $valorf = $valor1 / $valor2;
                }                                
            }    
        }
        return $valorf;
    }







    public function cargarEmpleadosxNomina($idNomina, $tipoNomina){
        $empleados = DB::table('empleado', 'e')
        ->select(["e.idempleado", "dp.primerNombre", "dp.segundoNombre", "dp.primerApellido", "dp.segundoApellido", "dp.numeroIdentificacion","t.nombre"])
        ->join("datospersonales AS dp", "e.fkDatosPersonales", "=" , "dp.idDatosPersonales")
        ->join("tipoidentificacion AS t", "dp.fkTipoIdentificacion", "=" , "t.idtipoIdentificacion")
        ->where("e.fkNomina","=", $idNomina)
        ->where("e.fkEstado","=", "1");//Estado Activo
        if($tipoNomina == "3"){
            $empleados =  $empleados->whereRaw("e.idempleado in(select n.fkEmpleado from novedad as n where n.fkRetiro is not null and n.fkEmpleado = e.idempleado) ");
        }
        if($tipoNomina == "7" || $tipoNomina == "6"){
            $empleados =  $empleados->where("e.tipoRegimen","=","Ley 50");
        }

        



        $empleados = $empleados->get();
        

        return view('/nomina.ajax.empleadosxNomina',[
            'empleados' => $empleados,
            'tipoNomina' => $tipoNomina
        ]);
        
    }

    public function cargarFechaxNomina($idNomina){

        $nomina = DB::table("nomina")->where("idNomina", "=", $idNomina)->first();
        $periodoNomina = $nomina->periodo." ".$nomina->tipoPeriodo;
        $liquidacionNomina = DB::table("liquidacionnomina")
            ->where("fkNomina", "=", $idNomina)
            ->whereIn("fkTipoLiquidacion", ["1","2","4","5","6"])//Normal
            ->where("fkEstado", "=", "5")//Terminada            
            ->orderBy("fechaLiquida","desc")->first();
        
        if(isset($liquidacionNomina->fechaLiquida)){            
            $fechaInicioDeseada = date('Y-m-d', strtotime($liquidacionNomina->fechaProximaInicio)); 
        }
        else{
            $fechaInicioDeseada = date('Y-m-01');
        }
        return response()->json([
            "success" => true,
            "periodoNomina" => $periodoNomina,
            "fechaInicioDeseada" => $fechaInicioDeseada
        ]);
    }

    public function condicionesxConceptoEnArray($concepto, $idEmpleado,$arrConcepto, $periodo){
        $condiciones = DB::table("condicion")
            ->where("fkConcepto", "=", $concepto)
            ->where("fkTipoResultado", "=", "3")            
            ->get();


        foreach($condiciones as $condicion){
            $itemsCondicion = DB::table("itemcondicion")->where("fkCondicion", "=", $condicion->idcondicion)->get();
            $arrCondicion = array();
            $posArr = 0;
            
            foreach($itemsCondicion as $itemCondicion){
                if($itemCondicion->fkTipoCondicion == "1"){//Inicial
                    if(sizeof($arrCondicion)>0){

                        foreach($arrCondicion as $llave => $arrItemCond){                                
                            if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1'])){
                                if($arrItemCond["fkOperadorComparacion"]=="1"){
                                    if($arrItemCond['inicio'] > $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="2"){
                                    if($arrItemCond['inicio'] < $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="3"){
                                    if($arrItemCond['inicio'] == $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="4"){
                                    if($arrItemCond['inicio'] >= $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="5"){
                                    if($arrItemCond['inicio'] <= $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="6"){
                                    if($arrItemCond['inicio'] != $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                            }
                            if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1']) && isset($arrItemCond['final2'])){
                                if($arrItemCond["fkOperadorComparacion"]=="7"){
                                    if($arrItemCond['inicio'] >= $arrItemCond['final1'] && $arrItemCond['inicio'] <= $arrItemCond['final2']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="8"){
                                    if($arrItemCond['inicio'] < $arrItemCond['final1'] && $arrItemCond['inicio'] > $arrItemCond['final2']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                            }

                            if($arrItemCond["tipoCondicion"] == "or" && isset($arrCondicion[$llave]["valido"]) && $arrCondicion[$llave]["valido"] == true){
                                
                                return response()->json([
                                    "success" => false,
                                    "respuesta" => $condicion->mensajeMostrar
                                ]);
                            }


                        }


                        $cuentaValidos = 0;
                        foreach($arrCondicion as $arrItemCond){
                            if(isset($arrCondicion["valido"]) &&  $arrItemCond["valido"] == true){
                                $cuentaValidos++;
                            }
                        }
                        if(sizeof($arrCondicion) == $cuentaValidos && sizeof($arrCondicion)!=0){
                            return response()->json([
                                "success" => false,
                                "respuesta" => $condicion->mensajeMostrar
                            ]);
                        }
                    }
                    array_push($arrCondicion, array("tipoCondicion" => "Inicial"));
                    $posArr=0;
                }
                else if($itemCondicion->fkTipoCondicion == "2"){//and
                    array_push($arrCondicion, array("tipoCondicion" => "and"));
                    $posArr++;
                }
                else if($itemCondicion->fkTipoCondicion == "3"){//or
                    array_push($arrCondicion, array("tipoCondicion" => "or"));
                    $posArr++;
                }

                $multiplicadorInicial = 1;
                if(isset($itemCondicion->multiplicadorInicial)){
                    $multiplicadorInicial = $itemCondicion->multiplicadorInicial;
                }
                $arrCondicionActual = $arrCondicion[$posArr];

                if(isset($itemCondicion->fkConceptoInicial)){
                    if($itemCondicion->fkConceptoInicial == $concepto){
                        
                        
                    }
                    else{
                        if(isset($arrConcepto[$itemCondicion->fkConceptoInicial]['valor'])){                                
                            $arrCondicionActual["inicio"]= intval($arrConcepto[$itemCondicion->fkConceptoInicial]['valor'])*$multiplicadorInicial;
                        }
                        else{
                            $arrCondicionActual["inicio"]=0;
                        }
                    }
                }
                else if(isset($itemCondicion->fkGrupoConceptoInicial)){
                    $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")                    
                    ->where("gcc.fkGrupoConcepto", "=", $itemCondicion->fkGrupoConceptoInicial)                       
                    ->get();
                    $totalValor = 0;
                    foreach($grupoConceptoCalculo as $grupoConceptoC){
                        
                        if(isset($arrConcepto[$grupoConceptoC->fkConcepto]['valor'])){           
                            if($grupoConceptoC->fkConcepto == 1){
                                if($periodo!=0){
                                    $totalValor = $totalValor + (intval($arrConcepto[$grupoConceptoC->fkConcepto]['valor'] * 30)/$periodo);
                                }
                                
                            }
                            else{
                                $totalValor = $totalValor + intval($arrConcepto[$grupoConceptoC->fkConcepto]['valor']);
                            }
                            
                        }
                    }

                    $totalValor = $totalValor*$multiplicadorInicial;
                    $arrCondicionActual["inicio"]= $totalValor;

                }
                $arrCondicionActual["fkOperadorComparacion"] = $itemCondicion->fkOperadorComparacion;
                $multiplicador1 = 1;
                if(isset($itemCondicion->multiplicador1)){
                    $multiplicador1 = $itemCondicion->multiplicador1;
                }

                if(isset($itemCondicion->fkConceptoFinal1)){
                    if($itemCondicion->fkConceptoFinal1 == $concepto){
                        
                        
                    }
                    else{
                        if(isset($arrConcepto[$itemCondicion->fkConceptoFinal1]['valor'])){                                
                            $arrCondicionActual["final1"]= intval($arrConcepto[$itemCondicion->fkConceptoFinal1]['valor'])*$multiplicador1;
                        }
                        else{
                            $arrCondicionActual["final1"]=0;
                        }
                    }
                }
                else if(isset($itemCondicion->fkGrupoConceptoFinal1)){

                    $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")                    
                    ->where("gcc.fkGrupoConcepto", "=", $itemCondicion->fkGrupoConceptoFinal1)                       
                    ->get();
                    $totalValor = 0;
                    foreach($grupoConceptoCalculo as $grupoConceptoC){
                        if(isset($arrConcepto[$grupoConceptoC->fkConcepto]['valor'])){                                
                            if($grupoConceptoC->fkConcepto == 1){
                                if($periodo!=0){
                                    $totalValor = $totalValor + (intval($arrConcepto[$grupoConceptoC->fkConcepto]['valor'] * 30)/$periodo);
                                }
                            }
                            else{
                                $totalValor = $totalValor + intval($arrConcepto[$grupoConceptoC->fkConcepto]['valor']);
                            }
                            
                        }
                    }
                    $totalValor = $totalValor*$multiplicador1;
                    $arrCondicionActual["final1"]= $totalValor;
                    
                }
                else if(isset($itemCondicion->fkVariableFinal1)){
                    $variableFinal1 = DB::table('variable')->where("idVariable","=",$itemCondicion->fkVariableFinal1)->first();
                    $arrCondicionActual["final1"] = intval($variableFinal1->valor)*$multiplicador1;
                }
                else if(isset($itemCondicion->valorCampo1)){
                    $arrCondicionActual["final1"] = intval($itemCondicion->valorCampo1)*$multiplicador1;
                }
                else{
                    $arrCondicionActual["final1"] = 0;
                }
                

                $multiplicador2 = 1;
                if(isset($itemCondicion->multiplicador2)){
                    $multiplicador2 = $itemCondicion->multiplicador2;
                }

                if(isset($itemCondicion->fkConceptoFinal2)){
                    if($itemCondicion->fkConceptoFinal2 == $concepto){                        
                        
                    }
                    else{
                        if(isset($arrConcepto[$itemCondicion->fkConceptoFinal2]['valor'])){                                
                            $arrCondicionActual["final2"]= intval($arrConcepto[$itemCondicion->fkConceptoFinal2]['valor'])*$multiplicador2;
                        }
                        else{
                            $arrCondicionActual["final2"]=0;
                        }                      
                    }
                }
                else if(isset($itemCondicion->fkGrupoConceptoFinal2)){
                    $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")                    
                    ->where("gcc.fkGrupoConcepto", "=", $itemCondicion->fkGrupoConceptoFinal2)                       
                    ->get();
                    $totalValor = 0;
                    foreach($grupoConceptoCalculo as $grupoConceptoC){
                        if(isset($arrConcepto[$grupoConceptoC->fkConcepto]['valor'])){                                
                            $totalValor = $totalValor + intval($arrConcepto[$grupoConceptoC->fkConcepto]['valor']);
                        }
                    }
                    $totalValor = $totalValor*$multiplicador2;
                    $arrCondicionActual["final2"]= $totalValor;
                    
                    
                }
                else if(isset($itemCondicion->fkVariableFinal2)){
                    $variableFinal2 = DB::table('variable')->where("idVariable","=",$itemCondicion->fkVariableFinal2)->first();
                    $arrCondicionActual["final2"] = intval($variableFinal2->valor)*$multiplicador2;
                }
                else if(isset($itemCondicion->valorCampo2)){
                    $arrCondicionActual["final2"] = intval($itemCondicion->valorCampo2)*$multiplicador2;
                }
                else{
                    $arrCondicionActual["final2"] = 0;
                }


                $arrCondicion[$posArr] = $arrCondicionActual;


            }
            
            foreach($arrCondicion as $llave => $arrItemCond){                                
                if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1'])){
                    if($arrItemCond["fkOperadorComparacion"]=="1"){
                        if($arrItemCond['inicio'] > $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="2"){
                        if($arrItemCond['inicio'] < $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="3"){
                        if($arrItemCond['inicio'] == $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="4"){
                        
                        if($arrItemCond['inicio'] >= $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                            
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="5"){
                        if($arrItemCond['inicio'] <= $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="6"){
                        if($arrItemCond['inicio'] != $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                }
                if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1']) && isset($arrItemCond['final2'])){
                    if($arrItemCond["fkOperadorComparacion"]=="7"){
                        if($arrItemCond['inicio'] >= $arrItemCond['final1'] && $arrItemCond['inicio'] <= $arrItemCond['final2']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="8"){
                        if($arrItemCond['inicio'] < $arrItemCond['final1'] && $arrItemCond['inicio'] > $arrItemCond['final2']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                }

                if($arrItemCond["tipoCondicion"] == "or" && isset($arrCondicion[$llave]["valido"]) && $arrCondicion[$llave]["valido"] == true){
                    
                    return false;
                }


            }
            $cuentaValidos = 0;
            
            foreach($arrCondicion as $arrItemCond){
                
                if(isset($arrItemCond["valido"]) &&  $arrItemCond["valido"] == true){
                    $cuentaValidos++;
                    
                }
            }
            
            if(sizeof($arrCondicion) == $cuentaValidos && sizeof($arrCondicion)!=0){
                return false;
            }                
        }
            return true;

    }

    public function condicionesxConcepto($concepto, $idEmpleado){
    
        $condiciones = DB::table("condicion")
            ->where("fkConcepto", "=", $concepto)
            ->where("fkTipoResultado", "=", "3")            
            ->get();


        foreach($condiciones as $condicion){
            $itemsCondicion = DB::table("itemcondicion")->where("fkCondicion", "=", $condicion->idcondicion)->get();
            $arrCondicion = array();
            $posArr = 0;
            
            foreach($itemsCondicion as $itemCondicion){
                if($itemCondicion->fkTipoCondicion == "1"){//Inicial
                    if(sizeof($arrCondicion)>0){

                        foreach($arrCondicion as $llave => $arrItemCond){                                
                            if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1'])){
                                if($arrItemCond["fkOperadorComparacion"]=="1"){
                                    if($arrItemCond['inicio'] > $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="2"){
                                    if($arrItemCond['inicio'] < $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="3"){
                                    if($arrItemCond['inicio'] == $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="4"){
                                    if($arrItemCond['inicio'] >= $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="5"){
                                    if($arrItemCond['inicio'] <= $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="6"){
                                    if($arrItemCond['inicio'] != $arrItemCond['final1']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                            }
                            if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1']) && isset($arrItemCond['final2'])){
                                if($arrItemCond["fkOperadorComparacion"]=="7"){
                                    if($arrItemCond['inicio'] >= $arrItemCond['final1'] && $arrItemCond['inicio'] <= $arrItemCond['final2']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                                else if($arrItemCond["fkOperadorComparacion"]=="8"){
                                    if($arrItemCond['inicio'] < $arrItemCond['final1'] && $arrItemCond['inicio'] > $arrItemCond['final2']){
                                        $arrCondicion[$llave]["valido"] = true;
                                    }
                                }
                            }

                            if($arrItemCond["tipoCondicion"] == "or" && isset($arrCondicion[$llave]["valido"]) && $arrCondicion[$llave]["valido"] == true){
                                
                                return response()->json([
                                    "success" => false,
                                    "respuesta" => $condicion->mensajeMostrar
                                ]);
                            }


                        }


                        $cuentaValidos = 0;
                        foreach($arrCondicion as $arrItemCond){
                            if(isset($arrCondicion["valido"]) &&  $arrItemCond["valido"] == true){
                                $cuentaValidos++;
                            }
                        }
                        if(sizeof($arrCondicion) == $cuentaValidos && sizeof($arrCondicion)!=0){
                            return response()->json([
                                "success" => false,
                                "respuesta" => $condicion->mensajeMostrar
                            ]);
                        }
                    }
                    array_push($arrCondicion, array("tipoCondicion" => "Inicial"));
                    $posArr=0;
                }
                else if($itemCondicion->fkTipoCondicion == "2"){//and
                    array_push($arrCondicion, array("tipoCondicion" => "and"));
                    $posArr++;
                }
                else if($itemCondicion->fkTipoCondicion == "3"){//or
                    array_push($arrCondicion, array("tipoCondicion" => "or"));
                    $posArr++;
                }

                $multiplicadorInicial = 1;
                if(isset($itemCondicion->multiplicadorInicial)){
                    $multiplicadorInicial = $itemCondicion->multiplicadorInicial;
                }
                $arrCondicionActual = $arrCondicion[$posArr];

                if(isset($itemCondicion->fkConceptoInicial)){
                    if($itemCondicion->fkConceptoInicial == $concepto){
                        
                        
                    }
                    else{
                        $conceptoCalculo = DB::table("conceptofijo")->where("fkEmpleado","=",$idEmpleado)
                                                    ->where("fkEstado","=", "1")
                                                    ->where("fkConcepto","=", $itemCondicion->fkConceptoInicial)->first();
                        if(isset($conceptoCalculo->valor)){                                
                            $arrCondicionActual["inicio"]= intval($conceptoCalculo->valor)*$multiplicadorInicial;
                        }
                        else{
                            $arrCondicionActual["inicio"]=0;
                        }
                    }
                }
                else if(isset($itemCondicion->fkGrupoConceptoInicial)){
                    $grupoConceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                    ->join("grupoconcepto_concepto as gcc", "gcc.fkConcepto","=","conceptofijo.fkConcepto")
                    ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                    ->where("conceptofijo.fkEstado","=","1")
                    ->where("gcc.fkGrupoConcepto", "=", $itemCondicion->fkGrupoConceptoInicial)                       
                    ->first();

                    $arrCondicionActual["inicio"]= intval($grupoConceptoCalculo->totalValor)*$multiplicadorInicial;

                }
                $arrCondicionActual["fkOperadorComparacion"] = $itemCondicion->fkOperadorComparacion;
                $multiplicador1 = 1;
                if(isset($itemCondicion->multiplicador1)){
                    $multiplicador1 = $itemCondicion->multiplicador1;
                }

                if(isset($itemCondicion->fkConceptoFinal1)){
                    if($itemCondicion->fkConceptoFinal1 == $concepto){
                        
                        
                    }
                    else{
                        $conceptoCalculo = DB::table("conceptofijo")->where("fkEmpleado","=",$idEmpleado)
                                                    ->where("fkEstado","=", "1")
                                                    ->where("fkConcepto","=", $itemCondicion->fkConceptoFinal1)->first();
                        if(isset($conceptoCalculo->valor)){                                
                            $arrCondicionActual["final1"]= intval($conceptoCalculo->valor)*$multiplicador1;
                        }
                        else{
                            $arrCondicionActual["final1"]=0;
                        }
                    }
                }
                else if(isset($itemCondicion->fkGrupoConceptoFinal1)){
                    $grupoConceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                    ->join("grupoconcepto_concepto as gcc", "gcc.fkConcepto","=","conceptofijo.fkConcepto")
                    ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                    ->where("conceptofijo.fkEstado","=","1")
                    ->where("gcc.fkGrupoConcepto", "=", $itemCondicion->fkGrupoConceptoFinal1)                       
                    ->first();

                    $arrCondicionActual["final1"]= intval($grupoConceptoCalculo->totalValor)*$multiplicador1;
                    
                    
                }
                else if(isset($itemCondicion->fkVariableFinal1)){
                    $variableFinal1 = DB::table('variable')->where("idVariable","=",$itemCondicion->fkVariableFinal1)->first();
                    $arrCondicionActual["final1"] = intval($variableFinal1->valor)*$multiplicador1;
                }
                else if(isset($itemCondicion->valorCampo1)){
                    $arrCondicionActual["final1"] = intval($itemCondicion->valorCampo1)*$multiplicador1;
                }
                else{
                    $arrCondicionActual["final1"] = 0;
                }
                

                $multiplicador2 = 1;
                if(isset($itemCondicion->multiplicador2)){
                    $multiplicador2 = $itemCondicion->multiplicador2;
                }

                if(isset($itemCondicion->fkConceptoFinal2)){
                    if($itemCondicion->fkConceptoFinal2 == $concepto){
                        
                        
                    }
                    else{
                        $conceptoCalculo = DB::table("conceptofijo")->where("fkEmpleado","=",$idEmpleado)
                                                    ->where("fkEstado","=", "1")
                                                    ->where("fkConcepto","=", $itemCondicion->fkConceptoFinal2)->first();
                        if(isset($conceptoCalculo->valor)){                                
                            $arrCondicionActual["final2"]= intval($conceptoCalculo->valor)*$multiplicador2;
                        }
                        else{
                            $arrCondicionActual["final2"]=0;
                        }
                    }
                }
                else if(isset($itemCondicion->fkGrupoConceptoFinal2)){
                    $grupoConceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                    ->join("grupoconcepto_concepto as gcc", "gcc.fkConcepto","=","conceptofijo.fkConcepto")
                    ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                    ->where("conceptofijo.fkEstado","=","1")
                    ->where("gcc.fkGrupoConcepto", "=", $itemCondicion->fkGrupoConceptoFinal2)                       
                    ->first();

                    $arrCondicionActual["final2"]= intval($grupoConceptoCalculo->totalValor)*$multiplicador2;
                    
                    
                }
                else if(isset($itemCondicion->fkVariableFinal2)){
                    $variableFinal2 = DB::table('variable')->where("idVariable","=",$itemCondicion->fkVariableFinal2)->first();
                    $arrCondicionActual["final2"] = intval($variableFinal2->valor)*$multiplicador2;
                }
                else if(isset($itemCondicion->valorCampo2)){
                    $arrCondicionActual["final2"] = intval($itemCondicion->valorCampo2)*$multiplicador2;
                }
                else{
                    $arrCondicionActual["final2"] = 0;
                }


                $arrCondicion[$posArr] = $arrCondicionActual;


            }
            
            foreach($arrCondicion as $llave => $arrItemCond){                                
                if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1'])){
                    if($arrItemCond["fkOperadorComparacion"]=="1"){
                        if($arrItemCond['inicio'] > $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="2"){
                        if($arrItemCond['inicio'] < $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="3"){
                        if($arrItemCond['inicio'] == $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="4"){
                        
                        if($arrItemCond['inicio'] >= $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                            
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="5"){
                        if($arrItemCond['inicio'] <= $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="6"){
                        if($arrItemCond['inicio'] != $arrItemCond['final1']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                }
                if(isset($arrItemCond['inicio']) && isset($arrItemCond['final1']) && isset($arrItemCond['final2'])){
                    if($arrItemCond["fkOperadorComparacion"]=="7"){
                        if($arrItemCond['inicio'] >= $arrItemCond['final1'] && $arrItemCond['inicio'] <= $arrItemCond['final2']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                    else if($arrItemCond["fkOperadorComparacion"]=="8"){
                        if($arrItemCond['inicio'] < $arrItemCond['final1'] && $arrItemCond['inicio'] > $arrItemCond['final2']){
                            $arrCondicion[$llave]["valido"] = true;
                        }
                    }
                }

                if($arrItemCond["tipoCondicion"] == "or" && isset($arrCondicion[$llave]["valido"]) && $arrCondicion[$llave]["valido"] == true){
                    
                    return false;
                }


            }
            $cuentaValidos = 0;
            
            foreach($arrCondicion as $arrItemCond){
                
                if(isset($arrItemCond["valido"]) &&  $arrItemCond["valido"] == true){
                    $cuentaValidos++;
                    
                }
            }
            
            if(sizeof($arrCondicion) == $cuentaValidos && sizeof($arrCondicion)!=0){
                return false;
            }                
        }
            return true;


    
        }

    public function verSolicitudLiquidacion($idLiquidacion){
        $liquidaciones = DB::table("liquidacionnomina", "ln")
        ->select(["ln.idLiquidacionNomina", "ln.fechaLiquida", "e.razonSocial", "tl.nombre as tipoLiquidacion", "est.nombre as estado"])
        ->join("nomina AS n","ln.fkNomina", "=", "n.idnomina")
        ->join("empresa AS e","n.fkEmpresa","=", "e.idempresa")
        ->join("tipoliquidacion AS tl","ln.fkTipoLiquidacion","=", "tl.idTipoLiquidacion")        
        ->join("estado AS est","ln.fkEstado","=", "est.idestado")
        ->where("ln.idLiquidacionNomina", "=", $idLiquidacion)
        ->first();

        $bouchers = DB::table("boucherpago", "b")
        ->select(["b.idBoucherPago","e.idempleado", "dp.primerNombre", "dp.segundoNombre", "dp.primerApellido", "dp.segundoApellido", "dp.numeroIdentificacion","t.nombre", "b.netoPagar"])
        ->join("empleado AS e", "b.fkEmpleado","=", "e.idempleado")
        ->join("datospersonales AS dp", "e.fkDatosPersonales","=", "dp.idDatosPersonales")
        ->join("tipoidentificacion AS t", "dp.fkTipoIdentificacion", "=" , "t.idtipoIdentificacion")
        ->where("b.fkLiquidacion","=",$idLiquidacion)
        ->orderByRaw("dp.numeroIdentificacion")
        ->get();


        return view('nomina.solicitudes.verSolicitud', ['bouchers' => $bouchers, "liquidaciones" => $liquidaciones]);

    }
    public function verSolicitudLiquidacionSinEdit($idLiquidacion){
        $liquidaciones = DB::table("liquidacionnomina", "ln")
        ->select(["ln.idLiquidacionNomina", "ln.fechaLiquida", "e.razonSocial", "tl.nombre as tipoLiquidacion", "est.nombre as estado", "ln.fkTipoLiquidacion"])
        ->join("nomina AS n","ln.fkNomina", "=", "n.idnomina")
        ->join("empresa AS e","n.fkEmpresa","=", "e.idempresa")
        ->join("tipoliquidacion AS tl","ln.fkTipoLiquidacion","=", "tl.idTipoLiquidacion")        
        ->join("estado AS est","ln.fkEstado","=", "est.idestado")
        ->where("ln.idLiquidacionNomina", "=", $idLiquidacion)
        ->first();

        $bouchers = DB::table("boucherpago", "b")
        ->select(["b.idBoucherPago","e.idempleado", "dp.primerNombre", "dp.segundoNombre", "dp.primerApellido", "dp.segundoApellido", "dp.numeroIdentificacion","t.nombre", "b.netoPagar"])
        ->join("empleado AS e", "b.fkEmpleado","=", "e.idempleado")
        ->join("datospersonales AS dp", "e.fkDatosPersonales","=", "dp.idDatosPersonales")
        ->join("tipoidentificacion AS t", "dp.fkTipoIdentificacion", "=" , "t.idtipoIdentificacion")
        ->where("b.fkLiquidacion","=",$idLiquidacion)
        ->orderByRaw("dp.numeroIdentificacion")
        ->get();


        return view('nomina.solicitudes.verSolicitudSinEdit', ['bouchers' => $bouchers, "liquidaciones" => $liquidaciones]);

    }

    public function cargarInfoxBoucher($idBoucherPago){

        $idItemBoucherPago = DB::table("item_boucher_pago","ibp")
        ->join("concepto AS c","c.idconcepto","=", "ibp.fkConcepto")
        ->where("ibp.fkBoucherPago","=",$idBoucherPago)
        ->get();
        $empleado = DB::table("empleado","e")
            ->select(["e.fechaIngreso", "emp.razonSocial", "e.idempleado"])
            ->join("empresa as emp", "emp.idempresa", "=", "e.fkEmpresa")
            ->join("boucherpago as bp", "bp.fkEmpleado", "=", "e.idempleado")
            ->join("item_boucher_pago as ibp", "ibp.fkBoucherPago", "=", "bp.idBoucherPago")
            ->where("bp.idBoucherPago","=", $idBoucherPago)
            ->first();
            
        $centrosCosto = DB::table("centrocosto","cc")
        ->join("empleado_centrocosto as ecc", "ecc.fkCentroCosto", "=", "cc.idCentroCosto")
        ->where("ecc.fkEmpleado", "=", $empleado->idempleado)->get();
        

        $conceptoSalario = DB::table("conceptofijo")->where("fkEmpleado","=",$empleado->idempleado)
        ->whereIn("fkConcepto",[1,2])->first();
        
        $novedadesRetiro = DB::table("novedad","n")
        ->select("r.fecha")
        ->join("retiro AS r", "r.idRetiro","=","n.fkRetiro")
        ->where("n.fkEmpleado", "=", $empleado->idempleado)
        ->where("n.fkEstado","=","7")
        ->whereNotNull("n.fkRetiro")
        ->first();

        

        
        
        return view('nomina.solicitudes.verItemBoucher', ['novedadesRetiro' => $novedadesRetiro, 'infoBoucher' => $idItemBoucherPago, "centrosCosto" => $centrosCosto, "empleado" => $empleado, 'conceptoSalario' => $conceptoSalario]);
    }
    
    public function reversar($idLiquidacion){

        $liquida = DB::table("liquidacionnomina")
        ->where("idLiquidacionNomina", "=", $idLiquidacion)
        ->first();

        $cierreAnte = DB::table("cierre","c")
        ->join("nomina as n","n.fkEmpresa", "=", "c.fkEmpresa")
        ->where("n.idNomina","=",$liquida->fkNomina)
        ->where("c.mes","=", date("m",strtotime($liquida->fechaLiquida)))
        ->where("c.anio","=", date("Y",strtotime($liquida->fechaLiquida)))
        ->where("c.fkEstado","=", "1")
        ->first();

        if(isset($cierreAnte)){
            return response()->json(['error'=>["Ya se cerró ese periodo"]]);
        }


        $affected = DB::table("liquidacionnomina")
        ->where("idLiquidacionNomina", "=", $idLiquidacion)
        ->update(["fkEstado" => "6"]);

        $arrNovedad = ["fkEstado" => "7"];
        $affected = DB::table('novedad',"n")->
        whereRaw("n.idNovedad in(Select itbn.fkNovedad from item_boucher_pago_novedad as itbn where itbn.fkItemBoucher in(SELECT ibp.idItemBoucherPago from item_boucher_pago as ibp WHERE ibp.fkBoucherPago IN(Select bp.idBoucherPago from boucherpago as bp WHERE bp.fkLiquidacion = '".$idLiquidacion."')))")
        ->update($arrNovedad);

        $affected = DB::table('empleado',"e")->
        whereRaw("e.idempleado in (Select n.fkEmpleado from novedad as n where n.fkRetiro is not null and n.idNovedad in(Select itbn.fkNovedad from item_boucher_pago_novedad as itbn where itbn.fkItemBoucher in(SELECT ibp.idItemBoucherPago from item_boucher_pago as ibp WHERE ibp.fkBoucherPago IN(Select bp.idBoucherPago from boucherpago as bp WHERE bp.fkLiquidacion = '".$idLiquidacion."'))))")
        ->update([
            "fkEstado" => "1"
        ]);



        return redirect('/nomina/solicitudLiquidacion/');
    }
    public function aprobarSolicitud(Request $req){
        $affected = DB::table("liquidacionnomina")
        ->where("idLiquidacionNomina", "=", $req->idLiquidacion)
        ->update(["fkEstado" => "5"]);

        $arrNovedad = ["fkEstado" => "8"];
        $affected = DB::table('novedad',"n")->
        whereRaw("n.idNovedad 
        in(Select itbn.fkNovedad from item_boucher_pago_novedad as itbn where itbn.parcial = 0 and itbn.fkItemBoucher 
            in(SELECT ibp.idItemBoucherPago from item_boucher_pago as ibp WHERE ibp.fkBoucherPago 
                IN(Select bp.idBoucherPago from boucherpago as bp WHERE bp.fkLiquidacion = '".$req->idLiquidacion."')))")
        ->update($arrNovedad);


        $arrNovedad = ["fkEstado" => "16"];
        $affected = DB::table('novedad',"n")->
        whereRaw("n.idNovedad 
        in(Select itbn.fkNovedad from item_boucher_pago_novedad as itbn where itbn.parcial = 1 and itbn.fkItemBoucher 
            in(SELECT ibp.idItemBoucherPago from item_boucher_pago as ibp WHERE ibp.fkBoucherPago 
                IN(Select bp.idBoucherPago from boucherpago as bp WHERE bp.fkLiquidacion = '".$req->idLiquidacion."')))")
        ->update($arrNovedad);

        
        $pagosParciales = DB::table('novedad',"n")->
        whereRaw("n.idNovedad 
        in(Select itbn.fkNovedad from item_boucher_pago_novedad as itbn where itbn.parcial = 1 and itbn.fkItemBoucher 
            in(SELECT ibp.idItemBoucherPago from item_boucher_pago as ibp WHERE ibp.fkBoucherPago 
                IN(Select bp.idBoucherPago from boucherpago as bp WHERE bp.fkLiquidacion = '".$req->idLiquidacion."')))")
        ->get();
        foreach($pagosParciales as $pagoParcial){
            if($pagoParcial->fkVacaciones){
                $vacaciones = DB::table("vacaciones","v")
                ->where("v.idVacaciones","=",$pagoParcial->fkVacaciones)->first();

                $cantidadITBN = DB::table("item_boucher_pago_novedad","itbn")
                ->selectRaw("sum(itbn.cantidad) as suma")
                ->where("itbn.fkNovedad","=",$pagoParcial->idNovedad)
                ->first();


                if($vacaciones->diasCompensar == $cantidadITBN->suma){
                    DB::table('novedad',"n")->where("n.fkNovedad","=",$pagoParcial->idNovedad)->update(["fkEstado" => "8"]);
                }
            }
        }


        $novedadesRetiro = DB::table('novedad',"n")->
        whereRaw("n.idNovedad in(Select itbn.fkNovedad from item_boucher_pago_novedad as itbn where itbn.fkItemBoucher in(SELECT ibp.idItemBoucherPago from item_boucher_pago as ibp WHERE ibp.fkBoucherPago IN(Select bp.idBoucherPago from boucherpago as bp WHERE bp.fkLiquidacion = '".$req->idLiquidacion."')))")
        ->whereNotNull("n.fkRetiro")
        ->get();

        foreach($novedadesRetiro as $novedadRetiro){
            DB::table("empleado","e")->where("e.idempleado","=",$novedadRetiro->fkEmpleado)->update(["fkEstado" => "2"]);    
        }
        
        



        return response()->json([
            "success" => true
        ]);
    }
    public function cancelarSolicitud(Request $req){
        $affected = DB::table('liquidacionnomina')->where("idLiquidacionNomina", "=", $req->idLiquidacion)->delete();
        
        
        return response()->json([
            "success" => true
        ]);
    }

    public function generarCierre(Request $req){


        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'empresa' => 'required',
            'fechaCierre' => 'required|date'            
        ],$messages);

        if ($validator->fails()) {
            return response()->json([
                'error'=>$validator->errors()->all(),
                "success" => false
            ]);
        }

        $cierreAnt = DB::table("cierre")
        ->where("fkEmpresa","=", $req->empresa)
        ->where("mes","=", date("m",strtotime($req->fechaCierre)))
        ->where("anio","=", date("Y",strtotime($req->fechaCierre)))
        ->first();

        if(isset($cierreAnt)){
            return response()->json(["success" => false, "mensaje" => "Error ya existe un cierre de este periodo"]);
        }


        $arrCierre = [
            "fkEmpresa" => $req->empresa,
            "mes" => date("m",strtotime($req->fechaCierre)),
            "anio" => date("Y",strtotime($req->fechaCierre))
        ];

        DB::table("cierre")->insert($arrCierre);


        
        //Lista empleados activos a fecha Cierre
        $empleados = DB::table("empleado", "e")
        ->join("boucherpago as bp","bp.fkEmpleado", "=","e.idempleado")
        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
        ->join("nomina as n","n.idnomina", "=","ln.fkNomina")
        ->where("n.fkEmpresa","=",$req->empresa)
        ->where("e.fkEstado", "=","1")
        ->get();
        $anioFechaDocumento = date("Y",strtotime($req->fechaCierre));
        $mesFechaDocumento = date("m",strtotime($req->fechaCierre));

        foreach($empleados as $empleado){
            if($mesFechaDocumento == 6){
                $datosProvPrima = DB::table('provision','p')
                ->selectRaw("sum(valor) as suma")
                ->where("p.anio","=",$anioFechaDocumento)
                ->where("p.mes","<=",$mesFechaDocumento)
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.fkConcepto","=","73")
                ->first();


                $itemsBoucherPrima = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("MONTH(ln.fechaFin) <= 6 and YEAR(ln.fechaInicio) = '".$anioFechaDocumento."'")
                ->where("ibp.fkConcepto","=","58") //58 - PRIMA SERVICIOS
                ->first();

                $pagoPrima = 0;
                if(isset($itemsBoucherPrima)){
                    if($itemsBoucherPrima->suma == 0){
                        $itemsBoucherAnticipoPrima = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.pago) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->whereRaw("MONTH(ln.fechaFin) <= 6 and YEAR(ln.fechaFin) = '".$anioFechaDocumento."'")
                        ->where("ibp.fkConcepto","=","78") //78 - ANTICIPO PRIMA
                        ->first();
                        if(isset($itemsBoucherAnticipoPrima)){
                            $pagoPrima = $itemsBoucherAnticipoPrima->suma;
                        }
                    }
                    else{
                        $pagoPrima = $itemsBoucherPrima->suma;
                    }                    
                }
                if(isset($datosProvPrima)){
                    $saldoPrima = $datosProvPrima->suma - $pagoPrima;
                    $arrSaldo = [
                        "fkConcepto"  => "73",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoPrima,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","73")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                


            }            
            if($mesFechaDocumento == 12){
                //PRIMA
                $datosProvPrima = DB::table('provision','p')
                ->selectRaw("sum(valor) as suma")
                ->where("p.anio","=",$anioFechaDocumento)
                ->where("p.mes","<=",$mesFechaDocumento)
                ->where("p.mes",">","6")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.fkConcepto","=","73")
                ->first();


                $itemsBoucherPrima = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("MONTH(ln.fechaFin) > 6 and YEAR(ln.fechaInicio) = '".$anioFechaDocumento."'")
                ->where("ibp.fkConcepto","=","58") //58 - PRIMA SERVICIOS
                ->first();

                $pagoPrima = 0;
                if(isset($itemsBoucherPrima)){
                    if($itemsBoucherPrima->suma == 0){
                        $itemsBoucherAnticipoPrima = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.pago) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->whereRaw("MONTH(ln.fechaFin) > 6 and YEAR(ln.fechaFin) = '".$anioFechaDocumento."'")
                        ->where("ibp.fkConcepto","=","78") //78 - ANTICIPO PRIMA
                        ->first();
                        if(isset($itemsBoucherAnticipoPrima)){
                            $pagoPrima = $itemsBoucherAnticipoPrima->suma;
                        }
                    }
                    else{
                        $pagoPrima = $itemsBoucherPrima->suma;
                    }                    
                }
                if(isset($datosProvPrima)){
                    $saldoPrima = $datosProvPrima->suma - $pagoPrima;
                    $arrSaldo = [
                        "fkConcepto"  => "73",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoPrima,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","73")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                else{
                    $saldoPrima = $pagoPrima*-1;
                    $arrSaldo = [
                        "fkConcepto"  => "73",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoPrima,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","73")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                //FIN PRIMA

                //CES
                $datosProvCes = DB::table('provision','p')
                ->selectRaw("sum(valor) as suma")
                ->where("p.anio","=",$anioFechaDocumento)
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.fkConcepto","=","71")
                ->first();


                $itemsBoucherCes = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioFechaDocumento."'")
                ->where("ibp.fkConcepto","=","66") //66 - PRIMA SERVICIOS
                ->first();

                $pagoCes = 0;
                if(isset($itemsBoucherCes)){
                    $pagoCes = $itemsBoucherCes->suma;
                }
                if(isset($datosProvCes)){
                    $saldoCes = $datosProvCes->suma - $pagoCes;

                    $arrSaldo = [
                        "fkConcepto"  => "71",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoCes,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","71")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                else{
                    $saldoCes = $pagoCes*-1;

                    $arrSaldo = [
                        "fkConcepto"  => "71",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoCes,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","71")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                //FIN CES

                //INT CES
                $datosProvIntCes = DB::table('provision','p')
                ->selectRaw("sum(valor) as suma")
                ->where("p.anio","=",$anioFechaDocumento)
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.fkConcepto","=","72")
                ->first();


                $itemsBoucherIntCes = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioFechaDocumento."'")
                ->where("ibp.fkConcepto","=","69") //66 - PRIMA SERVICIOS
                ->first();

                $pagoIntCes = 0;
                if(isset($itemsBoucherIntCes)){
                    $pagoIntCes = $itemsBoucherIntCes->suma;
                }
                if(isset($datosProvIntCes)){
                    $saldoIntCes = $datosProvIntCes->suma - $pagoIntCes;

                    $arrSaldo = [
                        "fkConcepto"  => "72",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoIntCes,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","72")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                else{
                    $saldoIntCes = $pagoIntCes*-1;

                    $arrSaldo = [
                        "fkConcepto"  => "72",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoIntCes,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","72")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                //FIN CES

                //VAC
                $datosProvVac = DB::table('provision','p')
                ->selectRaw("sum(valor) as suma")
                ->where("p.anio","=",$anioFechaDocumento)
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.fkConcepto","=","74")
                ->first();


                $itemsBoucherVac = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioFechaDocumento."'")
                ->where("ibp.fkConcepto",[28,30]) //vac
                ->first();

                $pagoVac = 0;
                if(isset($itemsBoucherVac)){
                    $pagoVac = $itemsBoucherVac->suma;
                }
                if(isset($datosProvVac)){
                    $saldoVac = $datosProvVac->suma - $pagoVac;

                    $arrSaldo = [
                        "fkConcepto"  => "74",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoVac,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","74")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                else{
                    $saldoVac = $pagoVac*-1;

                    $arrSaldo = [
                        "fkConcepto"  => "74",
                        "fkEmpleado"  => $empleado->idempleado,
                        "valor" => $saldoVac,
                        "mesAnterior" => $mesFechaDocumento,
                        "anioAnterior" => $anioFechaDocumento
                    ];
                    $saldoEnPeriodo = DB::table("saldo")
                    ->where("fkEmpleado","=",$empleado->idempleado)
                    ->where("fkConcepto","=","74")
                    ->where("mesAnterior","=",$mesFechaDocumento)
                    ->where("anioAnterior","=",$anioFechaDocumento)->first();
                    if(isset($saldoEnPeriodo)){
                        DB::table("saldo")->where("idSaldo","=",$saldoEnPeriodo->idSaldo)->update($arrSaldo);
                    }
                    else{
                        DB::table("saldo")->insert($arrSaldo);
                    }
                }
                //FIN CES

            }
        }
        return redirect('/nomina/cierre/');

        
    }
    public function indexCierre(){
        $empresas = DB::table("empresa", "e")->get();
        return view('/nomina.cierre',[
            "empresas" => $empresas
        ]);
    }

    public function comoCalculo($idItemBoucherPago){

        $itemBoucherPago = DB::table("item_boucher_pago","ibp")
            ->select(["ibp.*", "bp.fkEmpleado"])
            ->join("boucherpago as bp", "bp.idBoucherPago", "=", "ibp.fkBoucherPago")
            ->where("ibp.idItemBoucherPago","=",$idItemBoucherPago)->first();

        $concepto = DB::table("concepto","c")
            ->join("item_boucher_pago AS ibp","ibp.fkConcepto", "=", "c.idconcepto")
            ->where("ibp.idItemBoucherPago","=",$idItemBoucherPago)->first();
        if($concepto->subTipo == "Formula"){
            $formulasConceptos = DB::table("formulaconcepto")
            ->where("fkConcepto","=",$concepto->idconcepto)
            ->orderBy("idformulaConcepto")
            ->get();

            $arrFormulas = array();
            $valorf = 0;
            $idEmpleado = $itemBoucherPago->fkEmpleado;
            $contador = 0;
            foreach($formulasConceptos as $formulaConcepto){
                //VALOR 1
                
                if(isset($formulaConcepto->fkFormulaConcepto)){
                    $valor1=$valorf;
                    $arrFormulas[$contador]["valor1"] = $valor1;
                    $arrFormulas[$contador]["valor1_tipo"] = "formulaConcepto";
                }
                else if(isset($formulaConcepto->fkConceptoInicial)){
                    $conceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                        ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                        ->where("conceptofijo.fkConcepto","=", $formulaConcepto->fkConceptoInicial)
                        ->where("conceptofijo.fkEstado","=","1")
                        ->first();
                    $valor1=floatval($conceptoCalculo->totalValor);

                    
                    $conceptoValor1 = DB::table("concepto","c")
                    ->where("c.idconcepto","=",$formulaConcepto->fkConceptoInicial)->first();


                    $arrFormulas[$contador]["valor1"] = $valor1;
                    $arrFormulas[$contador]["valor1_tipo"] = "Concepto";
                    $arrFormulas[$contador]["valor1_nombre"] = $conceptoValor1->nombre;
                }
                else if(isset($formulaConcepto->fkGrupoConceptoInicial)){
    
                    $grupoConceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                        ->join("grupoconcepto_concepto as gcc", "gcc.fkConcepto","=","conceptofijo.fkConcepto")
                        ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                        ->where("conceptofijo.fkEstado","=","1")
                        ->where("gcc.fkGrupoConcepto", "=", $formulaConcepto->fkGrupoConceptoInicial)                       
                        ->first();
    
                    $valor1=floatval($grupoConceptoCalculo->totalValor);

                    $grupoValor1 = DB::table("grupoconcepto","gc")
                    ->where("gc.idgrupoConcepto","=",$formulaConcepto->fkGrupoConceptoInicial)->first();

                    $arrFormulas[$contador]["valor1"] = $valor1;
                    $arrFormulas[$contador]["valor1_tipo"] = "Grupo Concepto";
                    $arrFormulas[$contador]["valor1_nombre"] = $grupoValor1->nombre;
                    $arrFormulas[$contador]["valor1_idgrupoConcepto"] = $formulaConcepto->fkGrupoConceptoInicial;
                }
                else if(isset($formulaConcepto->fkVariableInicial)){
                    $variableCalculo = DB::table('variable')->where("idVariable","=",$formulaConcepto->fkVariableFinal)->first();
                    $valor1 = floatval($variableCalculo->valor);

                    $arrFormulas[$contador]["valor1"] = $valor1;
                    $arrFormulas[$contador]["valor1_tipo"] = "Variable";
                    $arrFormulas[$contador]["valor1_nombre"] = $variableCalculo->nombre;

                }
                else if(isset($formulaConcepto->valorInicial)){
                    $valor1 = floatval($formulaConcepto->valorInicial);
                    $arrFormulas[$contador]["valor1"] = $valor1;
                    $arrFormulas[$contador]["valor1_tipo"] = "Valor";
                    $arrFormulas[$contador]["valor1_nombre"] = "Valor Fijo";
                }
                //VALOR 2
                if(isset($formulaConcepto->fkConceptoFinal)){
                    $conceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                        ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                        ->where("conceptofijo.fkConcepto","=", $formulaConcepto->fkConceptoInicial)
                        ->where("conceptofijo.fkEstado","=","1")
                        ->first();
                    $valor2=floatval($conceptoCalculo->totalValor);

                    $conceptoValor2 = DB::table("concepto","c")
                    ->where("c.idconcepto","=",$formulaConcepto->fkConceptoInicial)->first();

                    $arrFormulas[$contador]["valor2"] = $valor2;
                    $arrFormulas[$contador]["valor2_tipo"] = "Concepto";
                    $arrFormulas[$contador]["valor2_nombre"] = $conceptoValor2->nombre;
                    

                }
                else if(isset($formulaConcepto->fkGrupoConceptoFinal)){
                    $grupoConceptoCalculo = DB::table("conceptofijo")->select(DB::raw('SUM(conceptofijo.valor) as totalValor'))
                        ->join("grupoconcepto_concepto as gcc", "gcc.fkConcepto","=","conceptofijo.fkConcepto")
                        ->where("conceptofijo.fkEmpleado","=", $idEmpleado)
                        ->where("conceptofijo.fkEstado","=","1")
                        ->where("gcc.fkGrupoConcepto", "=", $formulaConcepto->fkGrupoConceptoFinal)                       
                        ->first();


                    $grupoValor2 = DB::table("grupoconcepto","gc")
                        ->where("gc.idgrupoConcepto","=",$formulaConcepto->fkGrupoConceptoFinal)->first();

                    $valor2=floatval($grupoConceptoCalculo->totalValor);

                    $arrFormulas[$contador]["valor2"] = $valor2;
                    $arrFormulas[$contador]["valor2_tipo"] = "Grupo Concepto";
                    $arrFormulas[$contador]["valor2_nombre"] = $grupoValor2->nombre;
                    $arrFormulas[$contador]["valor2_idgrupoConcepto"] = $formulaConcepto->fkGrupoConceptoFinal;


                }
                else if(isset($formulaConcepto->fkVariableFinal)){
                    $variableFinal = DB::table('variable')->where("idVariable","=",$formulaConcepto->fkVariableFinal)->first();
                    $valor2 = floatval($variableFinal->valor);

                    $arrFormulas[$contador]["valor2"] = $valor2;
                    $arrFormulas[$contador]["valor2_tipo"] = "Variable";
                    $arrFormulas[$contador]["valor2_nombre"] = $variableFinal->nombre;

                }
                else if(isset($formulaConcepto->valorFinal)){
                    $valor2 = floatval($formulaConcepto->valorFinal);

                    $arrFormulas[$contador]["valor2"] = $valor2;
                    $arrFormulas[$contador]["valor2_tipo"] = "Valor";
                    $arrFormulas[$contador]["valor2_nombre"] = "Valor Fijo";
                }
    
                //VALOR F 
                
                if($formulaConcepto->fkTipoOperacion=="1"){//Suma
                    $valorf = $valor1 + $valor2;
                    $arrFormulas[$contador]["operacion"]="Mas:";
                }
                else if($formulaConcepto->fkTipoOperacion=="2"){//Resta
                    $valorf = $valor1 - $valor2;
                    $arrFormulas[$contador]["operacion"]="Menos:";
                }
                else if($formulaConcepto->fkTipoOperacion=="3"){//Multiplicacion
                    $valorf = $valor1 * $valor2;
                    $arrFormulas[$contador]["operacion"]="Multiplicado por:";
                }
                else if($formulaConcepto->fkTipoOperacion=="4"){//Division
                    if($valor2 != 0){
                        $valorf = $valor1 / $valor2;
                        $arrFormulas[$contador]["operacion"]="Dividido por:";
                    }                                
                }   
                $contador++; 
            }




            return view('nomina.solicitudes.verComoCalculoFormula', [
                'itemBoucherPago' => $itemBoucherPago,  
                'concepto' => $concepto,  
                'arrFormulas' => $arrFormulas
                
                ]);

        }else if($concepto->subTipo == "Tabla"){

            $variable = DB::table("variable")
            ->where("idVariable","=",$concepto->fkVariable)
            ->first();

            return view('nomina.solicitudes.verComoCalculoVariable', [
                'itemBoucherPago' => $itemBoucherPago,  
                'variable' => $variable,
                'concepto' => $concepto,  
                ]);
        }
        else if($concepto->subTipo == "Valor"){
            $conceptosFijos = DB::table("conceptofijo")
            ->where("fkConcepto","=",$concepto->idconcepto)
            ->where("fkEstado","=","1")
            ->where("fkEmpleado","=",$itemBoucherPago->fkEmpleado)
            ->first();


            return view('nomina.solicitudes.verComoCalculoConceptos', [
                'itemBoucherPago' => $itemBoucherPago,  
                'conceptosFijos' => $conceptosFijos,
                'concepto' => $concepto,  
                ]);

        }
        


    }
    public function verDetalleRetencion($idBoucherPago, $tipoRetencion){
        
        $retencion = DB::table('retencionfuente')
        ->where("fkBoucherPago","=",$idBoucherPago)
        ->where("tipoRetencion","=",$tipoRetencion)
        ->first();

        if($tipoRetencion == "NORMAL"){
            $itemBoucherPago = DB::table('item_boucher_pago')
            ->where("fkBoucherPago","=",$idBoucherPago)
            ->where("fkConcepto","=","36")->first();
        }
        else if($tipoRetencion == "INDEMNIZACION"){
            $itemBoucherPago = DB::table('item_boucher_pago')
            ->where("fkBoucherPago","=",$idBoucherPago)
            ->where("fkConcepto","=","76")->first();
        }
        

        return view('nomina.solicitudes.verDetalleRetencion', [
            'retencion' => $retencion,
            'itemBoucherPago' => $itemBoucherPago
        ]);

    }

    public function recalcularBoucher($idBoucherPago){
        $boucherPago = DB::table('boucherpago')
            ->where("idBoucherPago","=",$idBoucherPago)
            ->first();

        //fkEmpleado
        if($this->calcularLiquidacionEmpleado($boucherPago->fkEmpleado, $boucherPago->fkLiquidacion, $idBoucherPago)){

            $boucherPago = DB::table('boucherpago')
            ->where("idBoucherPago","=",$idBoucherPago)
            ->first();

            $totalNomina = DB::table('boucherpago')
            ->selectRaw("sum(netoPagar) as suma")
            ->where("fkLiquidacion","=",$boucherPago->fkLiquidacion)
            ->first();
            

            return response()->json([
                "success" => true,
                "netoPagar" => number_format($boucherPago->netoPagar,0, ",", "."),
                "totalNomina" => number_format($totalNomina->suma,0, ",", ".")
            ]);
        }
        else{
            return response()->json([
                "success" => false,
                "error" => "Error desconocido"
            ]);
        }
    }

    public function calcularLiquidacionEmpleado($idEmpleado, $idLiquidacionNomina, $idBoucherPago = null){

        


        $variables = DB::table("variable")->where("idVariable","=","1")->first();
        $salarioMinimoDia = $variables->valor / 30;

        $empleado = DB::table('empleado')->where("idempleado","=", $idEmpleado)->first();
        $liquidacionNomina = DB::table('liquidacionnomina')->where("idLiquidacionNomina", "=", $idLiquidacionNomina)->first();

        $nomina = DB::table("nomina")->where("idNomina", "=", $liquidacionNomina->fkNomina)->first();
        $periodo = $nomina->periodo;


        $empresa = DB::table('empresa', "em")
        ->join("nomina as n","n.fkEmpresa", "=", "em.idempresa")        
        ->join("liquidacionnomina as ln","ln.fkNomina", "=", "n.idNomina")        
        ->where("ln.idLiquidacionNomina","=", $idLiquidacionNomina)->first();

        $tipoliquidacion = $liquidacionNomina->fkTipoLiquidacion;
        $fechaInicio = $liquidacionNomina->fechaInicio;
        $fechaFin = $liquidacionNomina->fechaFin;


        $diasNoTrabajados = 0;
        $diasNoTrabajadosInjustificados = 0;
        $novedades = DB::table("novedad","n")
            ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","n.fkConcepto")
            ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
            ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)
            ->where("n.fkEmpleado", "=", $empleado->idempleado)
            ->where("n.fkEstado","=","7")
            ->whereBetween("n.fechaRegistro",[$fechaInicio, $fechaFin])->get();
        $arrValorxConcepto = array();
        $arrValorxConceptoOtros = array();
        //Agregar valor de las novedades a la liquidacion actual 
        foreach($novedades as $novedadyconcepto){
            if(isset($novedadyconcepto->fkAusencia)){
                $ausencia = DB::table("ausencia")
                    ->where("idAusencia", "=", $novedadyconcepto->fkAusencia)
                    ->first();  
                $valorFormula = $this->calcularValoresxConceptoxEmpleado($novedadyconcepto->idconcepto, $empleado->idempleado);

                if(isset($arrValorxConcepto[$novedadyconcepto->idconcepto])){
                    $cantidadHorasEnDias = ($ausencia->cantidadHoras/24);
                    $cantidadInt = $arrValorxConcepto[$novedadyconcepto->idconcepto]["cantidad"] + floatval($cantidadHorasEnDias) + floatval($ausencia->cantidadDias);
                    
                    $valorUnit = ($valorFormula*(floatval($cantidadHorasEnDias) + floatval($ausencia->cantidadDias)));


                    $valorInt = $arrValorxConcepto[$novedadyconcepto->idconcepto]["valor"] + $valorUnit;
                    
                    $arrNovedades = $arrValorxConcepto[$novedadyconcepto->idconcepto]["arrNovedades"];
                    array_push($arrNovedades, 
                        [
                            "idNovedad" => $novedadyconcepto->idNovedad,
                            "valor" => $valorUnit
                        ]
                    );
                    $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                        "naturaleza" => $novedadyconcepto->fkNaturaleza,
                        "tipoUnidad"=>"DIA",
                        "cantidad"=> $cantidadInt,
                        "arrNovedades"=> $arrNovedades,
                        "valor" => 0,
                        "valorAus" => $valorInt,
                        "tipoGen" => "novedadAus"
                    );
                    

                    $diasNoTrabajadosInjustificados = $diasNoTrabajadosInjustificados + $cantidadInt;
                }
                else{
                    
                    $cantidadHorasEnDias = ($ausencia->cantidadHoras/24);
                    $valorInt = $valorFormula*(floatval($cantidadHorasEnDias) + floatval($ausencia->cantidadDias));
                    $arrNovedades = array(
                        [
                            "idNovedad" => $novedadyconcepto->idNovedad,
                            "valor" => $valorInt
                        ]
                    );
                    $cantidadInt = floatval($cantidadHorasEnDias) + floatval($ausencia->cantidadDias);
                    $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                        "naturaleza" => $novedadyconcepto->fkNaturaleza,
                        "unidad"=>"DIA",
                        "cantidad"=> $cantidadInt,
                        "arrNovedades"=> $arrNovedades,
                        "valor" => 0,
                        "valorAus" => $valorInt,
                        "tipoGen" => "novedadAus"
                    );
                    $diasNoTrabajadosInjustificados = $diasNoTrabajadosInjustificados + $cantidadInt;
                }                    
            }
            else if(isset($novedadyconcepto->fkHorasExtra)){
                $horas_extra = DB::table("horas_extra")
                    ->where("idHoraExtra", "=", $novedadyconcepto->fkHorasExtra)
                    ->first();
                if($novedadyconcepto->subTipo == "Formula" || $novedadyconcepto->subTipo == "Porcentaje"){
                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($novedadyconcepto->idconcepto, $empleado->idempleado);
                    if(isset($arrValorxConcepto[$novedadyconcepto->idconcepto])){

                        $valorUnit = ($valorFormula * floatval($horas_extra->cantidadHoras));
                        $valorInt = $arrValorxConcepto[$novedadyconcepto->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = $arrValorxConcepto[$novedadyconcepto->idconcepto]["cantidad"] + floatval($horas_extra->cantidadHoras);
                        $arrNovedades=$arrValorxConcepto[$novedadyconcepto->idconcepto]["arrNovedades"];


                        array_push($arrNovedades, [
                            "idNovedad" => $novedadyconcepto->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                            "naturaleza" => $novedadyconcepto->fkNaturaleza,
                            "tipoUnidad"=>"HORAS",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt = $valorFormula * floatval($horas_extra->cantidadHoras);
                        $arrNovedades = array([
                            "idNovedad" => $novedadyconcepto->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $cantidadInt = $horas_extra->cantidadHoras;
                        $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                            "naturaleza" => $novedadyconcepto->fkNaturaleza,
                            "unidad"=>"HORAS",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }
            }
            else if(isset($novedadyconcepto->fkIncapacidad)){
                $incapacidadPTotal = DB::table("incapacidad")
                    ->where("idIncapacidad", "=", $novedadyconcepto->fkIncapacidad)
                    ->where("pagoTotal", "=", "1")
                    ->first();
                if(isset($incapacidadPTotal->numDias)){
                    if($novedadyconcepto->subTipo == "Formula"){      

                        $valorFormula = $this->calcularValoresxConceptoxEmpleado($novedadyconcepto->idconcepto, $empleado->idempleado);
                        
                        if($valorFormula < $salarioMinimoDia){
                            $valorFormula = $salarioMinimoDia;
                        }



                        if(isset($arrValorxConcepto[$novedadyconcepto->idconcepto])){
                            $valorUnit = ($valorFormula * floatval($incapacidadPTotal->numDias));
                            $valorInt = $arrValorxConcepto[$novedadyconcepto->idconcepto]["valor"] + $valorUnit;
                            $cantidadInt = $arrValorxConcepto[$novedadyconcepto->idconcepto]["cantidad"] + floatval($incapacidadPTotal->numDias);
                            $arrNovedades=$arrValorxConcepto[$novedadyconcepto->idconcepto]["arrNovedades"];
                            array_push($arrNovedades, [
                                "idNovedad" => $novedadyconcepto->idNovedad,
                                "valor" => $valorUnit
                            ]);
                            $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                                "naturaleza" => $novedadyconcepto->fkNaturaleza,
                                "unidad"=>"DIA",
                                "cantidad"=> $cantidadInt,
                                "arrNovedades"=> $arrNovedades,
                                "valor" => $valorInt,
                                "tipoGen" => "novedad"
                            );
                        }
                        else{
                            $valorInt = $valorFormula * floatval($incapacidadPTotal->numDias);
                            $arrNovedades = array([
                                "idNovedad" => $novedadyconcepto->idNovedad,
                                "valor" => $valorInt
                            ]);
                            $cantidadInt = $incapacidadPTotal->numDias;

                            $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                                "naturaleza" => $novedadyconcepto->fkNaturaleza,
                                "unidad"=>"DIA",
                                "cantidad"=> $cantidadInt,
                                "arrNovedades"=> $arrNovedades,
                                "valor" => $valorInt,
                                "tipoGen" => "novedad"
                            );
                        }
                    }
                }
            }            
            else if(isset($novedadyconcepto->fkOtros)){
               
                $otrasNovedades = DB::table("otra_novedad")
                    ->where("idOtraNovedad", "=", $novedadyconcepto->fkOtros)
                    ->first();
                    
                    if(isset($arrValorxConceptoOtros[$novedadyconcepto->idconcepto])){
                        $valorUnit =  (floatval($otrasNovedades->valor) * intval($otrasNovedades->sumaResta));
                        $valorInt = $arrValorxConceptoOtros[$novedadyconcepto->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = 0;
                        $arrNovedades=$arrValorxConceptoOtros[$novedadyconcepto->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $novedadyconcepto->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        $arrValorxConceptoOtros[$novedadyconcepto->idconcepto] = array(
                            "naturaleza" => $novedadyconcepto->fkNaturaleza,
                            "unidad"=>"VALOR",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );




                    }
                    else{
                        $valorInt = (floatval($otrasNovedades->valor) * intval($otrasNovedades->sumaResta));
                        $arrNovedades = array([
                            "idNovedad" => $novedadyconcepto->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $cantidadInt = 0;

                        $arrValorxConceptoOtros[$novedadyconcepto->idconcepto] = array(
                            "naturaleza" => $novedadyconcepto->fkNaturaleza,
                            "unidad"=>"VALOR",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                
                
            }
            
        }



        $sqlWhere = "( 
            ('".$fechaInicio."' BETWEEN l.fechaInicial AND l.fechaFinal) OR
            ('".$fechaFin."' BETWEEN l.fechaInicial AND l.fechaFinal) OR
            (l.fechaInicial BETWEEN '".$fechaInicio."' AND '".$fechaFin."') OR
            (l.fechaFinal BETWEEN '".$fechaInicio."' AND '".$fechaFin."')
        )";

        $licenciasPParcial = DB::table("licencia", "l")
        ->select(["l.*","c.*","n.*"])
        ->join("novedad as n", "n.fkLicencia","=","l.idLicencia")
        ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","n.fkConcepto")
        ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
        ->where("n.fkEmpleado", "=", $empleado->idempleado)
        ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)
        ->where("n.fkEstado","=","7")
        ->whereRaw($sqlWhere)
        ->get();

        //Agregar valor de las novedades de licencia ya que todas ellas tienen el pago parcial (lo de cada cosa en cada mes)
        foreach($licenciasPParcial as $licenciaPParcial){

            if(strtotime($licenciaPParcial->fechaInicial)>=strtotime($fechaInicio)
                &&  strtotime($licenciaPParcial->fechaInicial)<=strtotime($fechaFin) 
                &&  strtotime($licenciaPParcial->fechaFinal)>=strtotime($fechaFin))
            {
                $diaI = strtotime($licenciaPParcial->fechaInicial);
                $diaF = strtotime($fechaFin);
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24);
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                $diasNoTrabajados = $diasNoTrabajados + $dias;

                if($licenciaPParcial->subTipo == "Formula"){             

                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($licenciaPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    
                    if(isset($arrValorxConcepto[$licenciaPParcial->idconcepto])){
                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$licenciaPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorUnit
                            ]
                        );                            
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt= $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades = array([
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }                        
            }
            else if(strtotime($licenciaPParcial->fechaFinal)>=strtotime($fechaInicio)  
            &&  strtotime($licenciaPParcial->fechaFinal)<=strtotime($fechaFin) 
            &&  strtotime($licenciaPParcial->fechaInicial)<=strtotime($fechaInicio))
            {
                $diaI = strtotime( $fechaInicio );
                $diaF = strtotime( $licenciaPParcial->fechaFinal );
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24); 
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                $diasNoTrabajados = $diasNoTrabajados + $dias;
                if($licenciaPParcial->subTipo == "Formula"){                

                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($licenciaPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    if(isset($arrValorxConcepto[$licenciaPParcial->idconcepto])){

                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$licenciaPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt = $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades=array([
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }
            }
            else if(strtotime($licenciaPParcial->fechaInicial)<=strtotime($fechaInicio)  
            &&  strtotime($licenciaPParcial->fechaFinal)>=strtotime($fechaFin)) 
            {
                $diaI = strtotime( $fechaInicio );
                $diaF = strtotime( $fechaFin );
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24); 
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                $diasNoTrabajados = $diasNoTrabajados + $dias;
                
                if($licenciaPParcial->subTipo == "Formula"){       

                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($licenciaPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    if(isset($arrValorxConcepto[$licenciaPParcial->idconcepto])){

                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$licenciaPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades,[
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt = $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades=array([
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }

            }
            else if(strtotime($fechaInicio)<=strtotime($licenciaPParcial->fechaInicial)  
            &&  strtotime($fechaFin)>=strtotime($licenciaPParcial->fechaFinal)) 
            {
                $diaI = strtotime($licenciaPParcial->fechaInicial);
                $diaF = strtotime($licenciaPParcial->fechaFinal);
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24); 
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                $diasNoTrabajados = $diasNoTrabajados + $dias;
                if($licenciaPParcial->subTipo == "Formula"){                                           
                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($licenciaPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    if(isset($arrValorxConcepto[$licenciaPParcial->idconcepto])){
                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = $arrValorxConcepto[$licenciaPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$licenciaPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt = $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades=array([
                            "idNovedad" => $licenciaPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$licenciaPParcial->idconcepto] = array(
                            "naturaleza" => $licenciaPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }
            }
        }



        $sqlWhere = "( 
            ('".$fechaInicio."' BETWEEN v.fechaInicio AND v.fechaFin) OR
            ('".$fechaFin."' BETWEEN v.fechaInicio AND v.fechaFin) OR
            (v.fechaInicio BETWEEN '".$fechaInicio."' AND '".$fechaFin."') OR
            (v.fechaFin BETWEEN '".$fechaInicio."' AND '".$fechaFin."')
        )";
        
        $sqlWhere = "( 
            ('".$fechaInicio."' BETWEEN i.fechaInicial AND i.fechaFinal) OR
            ('".$fechaFin."' BETWEEN i.fechaInicial AND i.fechaFinal) OR
            (i.fechaInicial BETWEEN '".$fechaInicio."' AND '".$fechaFin."') OR
            (i.fechaFinal BETWEEN '".$fechaInicio."' AND '".$fechaFin."')
        )";
        $incapacidadesPParcial = DB::table("incapacidad", "i")
        ->select(["i.*","c.*","n.*"])
        ->join("novedad as n", "n.fkIncapacidad","=","i.idIncapacidad")
        ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","n.fkConcepto")
        ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
        ->where("n.fkEmpleado", "=", $empleado->idempleado)
        ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)
        ->where("i.pagoTotal", "=", "0")           
        ->where("n.fkEstado","=","7") 
        ->whereRaw($sqlWhere)
        ->get();

        //Agregar valor de las novedades de incapacidades con pago parcial
        foreach($incapacidadesPParcial as $incapacidadPParcial){

            if(strtotime($incapacidadPParcial->fechaInicial)>=strtotime($fechaInicio)
                &&  strtotime($incapacidadPParcial->fechaInicial)<=strtotime($fechaFin) 
                &&  strtotime($incapacidadPParcial->fechaFinal)>=strtotime($fechaFin))
            {
                $diaI = strtotime($incapacidadPParcial->fechaInicial);
                $diaF = strtotime($fechaFin);
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24);
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                if($incapacidadPParcial->subTipo == "Formula"){             

                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($incapacidadPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    if(isset($arrValorxConcepto[$incapacidadPParcial->idconcepto])){

                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$incapacidadPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorUnit
                        ]
                        );                            
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt= $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades = array([
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }                        
            }
            else if(strtotime($incapacidadPParcial->fechaFinal)>=strtotime($fechaInicio)  
            &&  strtotime($incapacidadPParcial->fechaFinal)<=strtotime($fechaFin) 
            &&  strtotime($incapacidadPParcial->fechaInicial)<=strtotime($fechaInicio))
            {
                $diaI = strtotime( $fechaInicio );
                $diaF = strtotime( $incapacidadPParcial->fechaFinal );
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24); 
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                if($incapacidadPParcial->subTipo == "Formula"){                

                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($incapacidadPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    if(isset($arrValorxConcepto[$incapacidadPParcial->idconcepto])){

                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["valor"] + $valorUnit;
                        $cantidadInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$incapacidadPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt = $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades=array([
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }
            }
            else if(strtotime($incapacidadPParcial->fechaInicial)<=strtotime($fechaInicio)  
            &&  strtotime($incapacidadPParcial->fechaFinal)>=strtotime($fechaFin)) 
            {
                $diaI = strtotime( $fechaInicio );
                $diaF = strtotime( $fechaFin );
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24); 
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                
                if($incapacidadPParcial->subTipo == "Formula"){       

                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($incapacidadPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    if(isset($arrValorxConcepto[$incapacidadPParcial->idconcepto])){

                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["valor"] + ($valorFormula * floatval($dias));
                        $cantidadInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$incapacidadPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt = $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades=array([
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }

            }
            else if(strtotime($fechaInicio)<=strtotime($incapacidadPParcial->fechaInicial)  
            &&  strtotime($fechaFin)>=strtotime($incapacidadPParcial->fechaFinal)) 
            {
                $diaI = strtotime($incapacidadPParcial->fechaInicial);
                $diaF = strtotime($incapacidadPParcial->fechaFinal);
                $diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24); 
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo
                if($incapacidadPParcial->subTipo == "Formula"){                                           
                    $valorFormula = $this->calcularValoresxConceptoxEmpleado($incapacidadPParcial->idconcepto, $empleado->idempleado);
                    if($valorFormula < $salarioMinimoDia){
                        $valorFormula = $salarioMinimoDia;
                    }

                    if(isset($arrValorxConcepto[$incapacidadPParcial->idconcepto])){
                        $valorUnit = ($valorFormula * floatval($dias));
                        $valorInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["valor"] + ($valorFormula * floatval($dias));
                        $cantidadInt = $arrValorxConcepto[$incapacidadPParcial->idconcepto]["cantidad"] + floatval($dias);
                        $arrNovedades=$arrValorxConcepto[$incapacidadPParcial->idconcepto]["arrNovedades"];
                        array_push($arrNovedades, [
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorUnit
                        ]);
                        
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                    else{
                        $valorInt = $valorFormula * floatval($dias);
                        $cantidadInt = floatval($dias);
                        $arrNovedades=array([
                            "idNovedad" => $incapacidadPParcial->idNovedad,
                            "valor" => $valorInt
                        ]);
                        $arrValorxConcepto[$incapacidadPParcial->idconcepto] = array(
                            "naturaleza" => $incapacidadPParcial->fkNaturaleza,
                            "unidad"=>"DIA",
                            "cantidad"=> $cantidadInt,
                            "arrNovedades"=> $arrNovedades,
                            "valor" => $valorInt,
                            "tipoGen" => "novedad"
                        );
                    }
                }
            }
        }

        //Calcular los dias que estuvo en incapacidad independiente si es pago parcial o completo
        $incapacidadesParaCalculoDias = DB::table("incapacidad", "i")
        ->select(["i.*","c.*"])
        ->join("novedad as n", "n.fkIncapacidad","=","i.idIncapacidad")
        ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","n.fkConcepto")
        ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
        ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)     
        ->where("n.fkEmpleado", "=", $empleado->idempleado)  
        ->where("n.fkEstado","=","7")
        ->whereRaw($sqlWhere)
        ->get();
        foreach($incapacidadesParaCalculoDias as $incapacidadCalculos){
            if(strtotime($incapacidadCalculos->fechaInicial)>=strtotime($fechaInicio)  
                &&  strtotime($incapacidadCalculos->fechaInicial)<=strtotime($fechaFin) 
                &&  strtotime($incapacidadCalculos->fechaFinal)>=strtotime($fechaFin))
            {
                $diaI = $incapacidadCalculos->fechaInicial;
                $diaF = $fechaFin;
                if(substr($diaI, 8, 2) == "31" || substr($diaI, 8, 2) == "28" || substr($diaI, 8, 2) == "29" ){
                    $diaI = substr($diaI,0,8)."30";
                }

                if(substr($diaF, 8, 2) == "31" || substr($diaF, 8, 2) == "28" || substr($diaF, 8, 2) == "29" ){
                    $diaF = substr($diaF,0,8)."30";
                }
                

                $dias = $this->days_360($diaI, $diaF);
                $dias ++;
                $diasNoTrabajados = $diasNoTrabajados + $dias;
            }
            else if(strtotime($incapacidadCalculos->fechaFinal)>=strtotime($fechaInicio)  
            &&  strtotime($incapacidadCalculos->fechaFinal)<=strtotime($fechaFin) 
            &&  strtotime($incapacidadCalculos->fechaInicial)<=strtotime($fechaInicio))
            {
                $diaI = $fechaInicio;
                $diaF = $incapacidadCalculos->fechaFinal;
                if(substr($diaI, 8, 2) == "31" || substr($diaI, 8, 2) == "28" || substr($diaI, 8, 2) == "29" ){
                    $diaI = substr($diaI,0,8)."30";
                }

                if(substr($diaF, 8, 2) == "31" || substr($diaF, 8, 2) == "28" || substr($diaF, 8, 2) == "29" ){
                    $diaF = substr($diaF,0,8)."30";
                }
                

                $dias = $this->days_360($diaI, $diaF);
                $dias ++;
                $diasNoTrabajados = $diasNoTrabajados + $dias;
                
            } 
            else if(strtotime($incapacidadCalculos->fechaInicial)<=strtotime($fechaInicio)  
            &&  strtotime($incapacidadCalculos->fechaFinal)>=strtotime($fechaFin)) 
            {
                $diaI = $fechaInicio;
                $diaF = $fechaFin;

                if(substr($diaI, 8, 2) == "31" || substr($diaI, 8, 2) == "28" || substr($diaI, 8, 2) == "29" ){
                    $diaI = substr($diaI,0,8)."30";
                }

                if(substr($diaF, 8, 2) == "31" || substr($diaF, 8, 2) == "28" || substr($diaF, 8, 2) == "29" ){
                    $diaF = substr($diaF,0,8)."30";
                }
                

                $dias = $this->days_360($diaI, $diaF);
                $dias ++;
                $diasNoTrabajados = $diasNoTrabajados + $dias;
            }
            else if(strtotime($fechaInicio)<=strtotime($incapacidadCalculos->fechaInicial)  
            &&  strtotime($fechaFin)>=strtotime($incapacidadCalculos->fechaFinal)) 
            {
                $diaI = $incapacidadCalculos->fechaInicial;
                $diaF = $incapacidadCalculos->fechaFinal;
                
                if(substr($diaI, 8, 2) == "31" || substr($diaI, 8, 2) == "28" || substr($diaI, 8, 2) == "29" ){
                    $diaI = substr($diaI,0,8)."30";
                }

                if(substr($diaF, 8, 2) == "31" || substr($diaF, 8, 2) == "28" || substr($diaF, 8, 2) == "29" ){
                    $diaF = substr($diaF,0,8)."30";
                }
                

                $dias = $this->days_360($diaI, $diaF);
                $dias ++;

                /*$diff = $diaF - $diaI;
                $dias = $diff / ( 60 * 60 * 24); 
                $dias++; //Como se toma desde dia.00:00:00 es para que tome el dia completo*/
                $diasNoTrabajados = $diasNoTrabajados + $dias;
            }
        }
        
        $conceptosFijosEmpl = DB::table("conceptofijo", "cf")
        ->select(["cf.valor","cf.fechaInicio","cf.fechaFin", "cf.fkConcepto","cf.unidad", "c.*"])
        ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","cf.fkConcepto")
        ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
        ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)     
        ->where("cf.fkEmpleado", "=", $empleado->idempleado)  
        ->where("cf.fkEstado", "=", "1")
        ->get();
        //Agregar conceptos fijos a la liquidacion actual
        foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
            if(isset($conceptoFijoEmpl->fechaFin)){
                if(strtotime($conceptoFijoEmpl->fechaInicio)>=strtotime($fechaInicio)  
                    &&  strtotime($conceptoFijoEmpl->fechaInicio)<=strtotime($fechaFin) 
                    &&  strtotime($conceptoFijoEmpl->fechaFin)>=strtotime($fechaFin))
                {
                    if(isset($arrValorxConcepto[$conceptoFijoEmpl->fkConcepto])){
                        $valorInt = $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto]["valor"] + floatval($conceptoFijoEmpl->valor);
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );
                    }
                    else{
                        $valorInt = floatval($conceptoFijoEmpl->valor);                            
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );
                    }                  
                }
                else if(strtotime($conceptoFijoEmpl->fechaFin)>=strtotime($fechaInicio)  
                &&  strtotime($conceptoFijoEmpl->fechaFin)<=strtotime($fechaFin) 
                &&  strtotime($conceptoFijoEmpl->fechaInicio)<=strtotime($fechaInicio))
                {
                    
                    if(isset($arrValorxConcepto[$conceptoFijoEmpl->fkConcepto])){                            
                        $valorInt = $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto]["valor"] + floatval($conceptoFijoEmpl->valor);
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );

                    }
                    else{
                        $valorInt = floatval($conceptoFijoEmpl->valor);                            
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );
                    }
                    
                } 
                else if(strtotime($conceptoFijoEmpl->fechaInicio)<=strtotime($fechaInicio)  
                &&  strtotime($conceptoFijoEmpl->fechaFin)>=strtotime($fechaFin)) 
                {
                    if(isset($arrValorxConcepto[$conceptoFijoEmpl->fkConcepto])){                            
                        $valorInt = $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto]["valor"] + floatval($conceptoFijoEmpl->valor);
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );

                    }
                    else{
                        $valorInt = floatval($conceptoFijoEmpl->valor);                            
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );
                    }
                    
                }
                else if(strtotime($fechaInicio)<=strtotime($conceptoFijoEmpl->fechaInicio)  
                &&  strtotime($fechaFin)>=strtotime($conceptoFijoEmpl->fechaFin)) 
                {
                    if(isset($arrValorxConcepto[$conceptoFijoEmpl->fkConcepto])){                            
                        $valorInt = $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto]["valor"] + floatval($conceptoFijoEmpl->valor);
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );

                    }
                    else{
                        $valorInt = floatval($conceptoFijoEmpl->valor);                            
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );
                    }
                }
            }
            else{
                if(strtotime($fechaInicio)<=strtotime($conceptoFijoEmpl->fechaInicio) &&
                strtotime($fechaFin)>=strtotime($conceptoFijoEmpl->fechaInicio))
                {
                    if(isset($arrValorxConcepto[$conceptoFijoEmpl->fkConcepto])){                            
                        $valorInt = $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto]["valor"] + floatval($conceptoFijoEmpl->valor);
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );

                    }
                    else{
                        $valorInt = floatval($conceptoFijoEmpl->valor);                            
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );
                    }
                }
                else if(strtotime($conceptoFijoEmpl->fechaInicio)<=strtotime($fechaInicio))
                {
                    if(isset($arrValorxConcepto[$conceptoFijoEmpl->fkConcepto])){                            
                        $valorInt = $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto]["valor"] + floatval($conceptoFijoEmpl->valor);
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );

                    }
                    else{
                        $valorInt = floatval($conceptoFijoEmpl->valor);                            
                        $arrValorxConcepto[$conceptoFijoEmpl->fkConcepto] = array(
                            "naturaleza" => $conceptoFijoEmpl->fkNaturaleza,
                            "unidad" => $conceptoFijoEmpl->unidad,
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "conceptoFijo"
                        );
                    }     
                }
            }
        }
        

        //dd($diasNoTrabajados);


        //Calcular el # de dias trabajados desde la fecha de ingreso
        $diaI = strtotime($fechaInicio);
        $diaF = strtotime($empleado->fechaIngreso);
        $diff = $diaF - $diaI;
        $dias = $diff / ( 60 * 60 * 24); 
        $periodoPago = $periodo;
        if($dias>0){
            $periodoPago = $periodo - (floor($dias));
        }
       
        
        $novedadesRetiro = DB::table("novedad","n")
        ->select("r.fecha")
        ->join("retiro AS r", "r.idRetiro","=","n.fkRetiro")
            ->where("n.fkEmpleado", "=", $empleado->idempleado)
        ->where("n.fkEstado","=","7")
        ->whereNotNull("n.fkRetiro")
        ->whereBetween("n.fechaRegistro",[$fechaInicio, $fechaFin])->first();

        

        if(isset($novedadesRetiro)){
            $fechaFinCalc = $fechaFin;
            if(substr($fechaFin, 8, 2) == "31" || substr($fechaFin, 8, 2) == "28" || substr($fechaFin, 8, 2) == "29" ){
                $fechaFinCalc = substr($fechaFin,0,8)."30";
            }
            
            $dias = $this->days_360($novedadesRetiro->fecha, $fechaFinCalc);
            
            if($dias < 30){
                
                if($empleado->fechaIngreso != $novedadesRetiro->fecha){
                    $periodoPago = $periodoPago - (floor($dias));
                }
                else{
                    $periodoPago = $periodoPago - (floor($dias));
                }
                
                
            }
           
        }
               
        

        
        $periodoPagoSinVac = $periodoPago;
        
        $sqlWhere = "( 
            ('".$fechaInicio."' BETWEEN v.fechaInicio AND v.fechaFin) OR
            ('".$fechaFin."' BETWEEN v.fechaInicio AND v.fechaFin) OR
            (v.fechaInicio BETWEEN '".$fechaInicio."' AND '".$fechaFin."') OR
            (v.fechaFin BETWEEN '".$fechaInicio."' AND '".$fechaFin."')
        )";

        $novedadesVacacion = DB::table("novedad","n")
        ->join("vacaciones as v","v.idVacaciones","=","n.fkVacaciones")
        ->where("n.fkEmpleado","=",$empleado->idempleado)
        ->whereRaw($sqlWhere)
        ->whereIn("n.fkEstado",["7", "8","16"]) // Pagada o sin pagar-> no que este eliminada
        ->whereNotNull("n.fkVacaciones")
        ->where("fkConcepto","=","29")
        ->get();
        //$diasVac = $totalPeriodoPagoAnioActual * 15 / 360;
        foreach($novedadesVacacion as $novedadVacacion){

            if(strtotime($novedadVacacion->fechaInicio)>=strtotime($fechaInicio)
                &&  strtotime($novedadVacacion->fechaInicio)<=strtotime($fechaFin) 
                &&  strtotime($novedadVacacion->fechaFin)>=strtotime($fechaFin))
            {
                $diaI = strtotime($novedadVacacion->fechaInicio);
                $diaF = strtotime($fechaFin);
                $diasCompensar = $this->days_360($novedadVacacion->fechaInicio, $fechaFin) + 1;
                if(substr($novedadVacacion->fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                    $diasCompensar--;   
                }
                if( substr($fechaFin, 8, 2) == "31"){
                    $diasCompensar--;  
                }
            }
            else if(strtotime($novedadVacacion->fechaFin)>=strtotime($fechaInicio)  
            &&  strtotime($novedadVacacion->fechaFin)<=strtotime($fechaFin) 
            &&  strtotime($novedadVacacion->fechaInicio)<=strtotime($fechaInicio))
            {
                $diaI = strtotime( $fechaInicio );
                $diaF = strtotime( $novedadVacacion->fechaFin );

                $diasCompensar = $this->days_360($fechaInicio, $novedadVacacion->fechaFin) + 1;
                if(substr($fechaInicio, 8, 2) == "31" && substr($novedadVacacion->fechaFin, 8, 2) == "31"){
                    $diasCompensar--;   
                }
                if( substr($novedadVacacion->fechaFin, 8, 2) == "31"){
                    $diasCompensar--;  
                }
            }
            else if(strtotime($novedadVacacion->fechaInicio)<=strtotime($fechaInicio)  
            &&  strtotime($novedadVacacion->fechaFin)>=strtotime($fechaFin)) 
            {
                $diaI = strtotime( $fechaInicio );
                $diaF = strtotime( $fechaFin );
                $diasCompensar = $this->days_360($fechaInicio, $fechaFin) + 1;
                if(substr($fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                    $diasCompensar--;   
                }
                if( substr($fechaFin, 8, 2) == "31"){
                    $diasCompensar--;  
                }
            }
            else if(strtotime($fechaInicio)<=strtotime($novedadVacacion->fechaInicio)  
            &&  strtotime($fechaFin)>=strtotime($novedadVacacion->fechaFin)) 
            {
                $diaI = strtotime($novedadVacacion->fechaInicio);
                $diaF = strtotime($novedadVacacion->fechaFin);
                $diasCompensar = $this->days_360($novedadVacacion->fechaInicio, $novedadVacacion->fechaFin) + 1;

                if(substr($novedadVacacion->fechaInicio, 8, 2) == "31" && substr($novedadVacacion->fechaFin, 8, 2) == "31"){
                    $diasCompensar--;   
                }
                if( substr($novedadVacacion->fechaFin, 8, 2) == "31"){
                    $diasCompensar--;  
                }
            }

            $periodoPago =  $periodoPago - $diasCompensar;
            
            /*if(strtotime($novedadVacacion->fechaFin) > strtotime($fechaFin)){
                //Termina en un proximo periodo
                $diferenciaDiasInicio = $this->days_360($novedadVacacion->fechaInicio, $fechaFin);
                
                if(substr($fechaFin, 8, 2) != "31"){
                    $diferenciaDiasInicio++;
                }            
                $periodoPago =  $periodoPago - $diferenciaDiasInicio;
            }
            else{
                //Termina este mismo periodo
                $diferenciaDiasPeriodo = $this->days_360($novedadVacacion->fechaInicio, $novedadVacacion->fechaFin);
                
                if(substr($novedadVacacion->fechaInicio, 8, 2) == "31" && substr($novedadVacacion->fechaFin, 8, 2) == "31"){
                    $diferenciaDiasPeriodo--;
                }
                else if( substr($novedadVacacion->fechaFin, 8, 2) != "31"){
                    $diferenciaDiasPeriodo++;
                }
                
               
   
                $periodoPago =  $periodoPago - $diferenciaDiasPeriodo;


                //$periodoPago =  $periodoPago - $novedadVacacion->diasCompensar;
                if($periodoPago < 0){
                    $periodoPago = 0;
                }
            }
*/
            

            
        }

        $periodoGen = $periodoPago - $diasNoTrabajados - $diasNoTrabajadosInjustificados;
        $valorNeto = 0;

        $novedades = DB::table("novedad","n")
            ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","n.fkConcepto")
            ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
            ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)
            ->where("n.fkEmpleado", "=", $empleado->idempleado)
            ->where("n.fkEstado","=","7")
            ->whereBetween("n.fechaRegistro",[$fechaInicio, $fechaFin])->get();
        //Agregar valor de las novedades a la liquidacion actual 
        foreach($novedades as $novedadyconcepto){
            if(isset($novedadyconcepto->fkVacaciones)){
                $vacacionesPTotal = DB::table("vacaciones")
                    ->where("idVacaciones", "=", $novedadyconcepto->fkVacaciones)
                    ->where("pagoAnticipado", "=", "1")
                    ->first();
                

                if(isset($vacacionesPTotal->diasCompensar)){
                    
                        $valorFormula = $this->calcularValoresxConceptoxEmpleado($novedadyconcepto->idconcepto, $empleado->idempleado);
                        if($valorFormula < $salarioMinimoDia){
                            $valorFormula = $salarioMinimoDia;
                        }

                        if(isset($arrValorxConcepto[$novedadyconcepto->idconcepto])){                        
                            $salarialVac = 0;
                            $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                            $grupoConceptoCalculoVac = DB::table("grupoconcepto_concepto","gcc")
                                ->where("gcc.fkGrupoConcepto", "=", "13")//Salarial para provisiones
                                ->get();
                            foreach($grupoConceptoCalculoVac as $grupoConcepto){
                                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                                    $salarialVac = $salarialVac + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                                }
                            }

                            $itemsBoucherSalarialMesAnteriorVac = DB::table("item_boucher_pago", "ibp")
                            ->selectRaw("Sum(ibp.valor) as suma")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","=",$fechaInicioMes)
                            ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                            ->first();
                            if(isset($itemsBoucherSalarialMesAnteriorVac)){
                                $salarialVac = $salarialVac + $itemsBoucherSalarialMesAnteriorVac->suma;
                            }

                            $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$fechaFin) + 1 ;

                            if(isset($novedadesRetiro)){
                                if(strtotime($fechaFin) > strtotime($novedadesRetiro->fecha)){
                                    $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
                                }
                            }
 
                            $anioActual = intval(date("Y",strtotime($fechaInicio)));
                            $mesActual = intval(date("m",strtotime($fechaInicio)));


                            $liquidacionesMesesAnterioresCompleta = DB::table("liquidacionnomina", "ln")
                            ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","<=",$fechaInicioMes)
                            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")       
                            ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])         
                            ->first();

                            //Obtener la primera liquidacion de nomina de la persona 
                            $primeraLiquidacion = DB::table("liquidacionnomina", "ln")
                            ->selectRaw("min(ln.fechaInicio) as primeraFecha")
                            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)->first();

                            $minimaFecha = date("Y-m-d");
                            
                            if(isset($primeraLiquidacion)){
                                $minimaFecha = $primeraLiquidacion->primeraFecha;
                            }
                            $diasAgregar = 0;
                            //Verificar si dicha nomina es menor a la fecha de ingreso
                            if(strtotime($empleado->fechaIngreso) < strtotime($minimaFecha)){
                                $diasAgregar = $this->days_360($empleado->fechaIngreso, $minimaFecha);
                            }
                            if(isset($vacacionesPTotal->fechaInicio)){
                                $periodoNuevo = $this->days_360($fechaInicio,$vacacionesPTotal->fechaInicio);
                            }
                            else{
                                $periodoNuevo = $this->days_360($fechaInicio,$novedadyconcepto->fechaRegistro);
                            }
                            

                            $periodoPagoMesActual = $periodoNuevo + $diasAgregar;
                            $totalPeriodoPagoAnioActual = $periodoPagoMesActual + $liquidacionesMesesAnterioresCompleta->periodPago;

                            $salarioMes = 0;
                            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                                if($conceptoFijoEmpl->fkConcepto=="1"){
                                    $salarioMes = $conceptoFijoEmpl->valor; 
                                }
                            }

                            $itemsBoucherSalarialMesesAnterioresVac = DB::table("item_boucher_pago", "ibp")
                            ->selectRaw("Sum(ibp.valor) as suma")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","<",$fechaInicioMes)
                            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")         
                            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                            ->first();

                            $salarialVac = $salarialVac + $itemsBoucherSalarialMesesAnterioresVac->suma;
                            $salarialVac = ($salarialVac / $totalPeriodoPagoAnioActual)*30;
                            
                            $salarioVac = 0;

                            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                                if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                                    $salarioVac = $conceptoFijoEmpl->valor; 
                                }
                            }

                            
                            $baseVac = $salarioVac + $salarialVac;
                            
                           
                            $valorInt = ($baseVac/30)*$vacacionesPTotal->diasCompensar;

                            $valorUnit = $valorInt;
                            $valorInt = $valorInt + $arrValorxConcepto[$novedadyconcepto->idconcepto]["valor"];
                            $cantidadInt = $arrValorxConcepto[$novedadyconcepto->idconcepto]["cantidad"] + floatval($vacacionesPTotal->diasCompensar);
                            $arrNovedades=$arrValorxConcepto[$novedadyconcepto->idconcepto]["arrNovedades"];
                            array_push($arrNovedades, [
                                "idNovedad" => $novedadyconcepto->idNovedad,
                                "valor" => $valorUnit
                            ]);
                            $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                                "naturaleza" => $novedadyconcepto->fkNaturaleza,
                                "unidad"=>"DIA",
                                "cantidad"=> $cantidadInt,
                                "arrNovedades"=> $arrNovedades,
                                "valor" => $valorInt,
                                "tipoGen" => "novedad"
                            );
                        }
                        else{
                           
                            $salarialVac = 0;
                            
                            $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                            $grupoConceptoCalculoVac = DB::table("grupoconcepto_concepto","gcc")
                                ->where("gcc.fkGrupoConcepto", "=", "13")//Salarial para provisiones
                                ->get();
                            foreach($grupoConceptoCalculoVac as $grupoConcepto){
                                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                                    $salarialVac = $salarialVac + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                                }
                            }


                           


                            $itemsBoucherSalarialMesAnteriorVac = DB::table("item_boucher_pago", "ibp")
                            ->selectRaw("Sum(ibp.valor) as suma")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","=",$fechaInicioMes)
                            ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                            ->first();
                            if(isset($itemsBoucherSalarialMesAnteriorVac)){
                                $salarialVac = $salarialVac + $itemsBoucherSalarialMesAnteriorVac->suma;

                            }
                            

                            $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$fechaFin) + 1 ;

                            if(isset($novedadesRetiro)){
                                if(strtotime($fechaFin) > strtotime($novedadesRetiro->fecha)){
                                    $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
                                }
                            }
                         
                        
                            // $diasVac = $totalPeriodoPagoAnioActual * 15 / 360;
                            
                            $anioActual = intval(date("Y",strtotime($fechaInicio)));
                            $mesActual = intval(date("m",strtotime($fechaInicio)));


                            $liquidacionesMesesAnterioresCompleta = DB::table("liquidacionnomina", "ln")
                            ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","<=",$fechaInicioMes)
                            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")     
                            ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])           
                            ->first();


                            //Obtener la primera liquidacion de nomina de la persona 
                            $primeraLiquidacion = DB::table("liquidacionnomina", "ln")
                            ->selectRaw("min(ln.fechaInicio) as primeraFecha")
                            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)->first();

                            $minimaFecha = date("Y-m-d");
                            
                            if(isset($primeraLiquidacion)){
                                $minimaFecha = $primeraLiquidacion->primeraFecha;
                            }
                            $diasAgregar = 0;
                            //Verificar si dicha nomina es menor a la fecha de ingreso
                            if(strtotime($empleado->fechaIngreso) < strtotime($minimaFecha)){
                                $diasAgregar = $this->days_360($empleado->fechaIngreso, $minimaFecha);
                            }
                            
                            
                            

                            
                            
                            if(isset($vacacionesPTotal->fechaInicio)){
                                $periodoNuevo = $this->days_360($fechaInicio,$vacacionesPTotal->fechaInicio);
                            }
                            else{
                                $periodoNuevo = $this->days_360($fechaInicio,$novedadyconcepto->fechaRegistro);
                            }
                            

                            $periodoPagoMesActual = $periodoNuevo + $diasAgregar;


                            $totalPeriodoPagoAnioActual = $periodoPagoMesActual + $liquidacionesMesesAnterioresCompleta->periodPago;
                            if($totalPeriodoPagoAnioActual>360){
                                $totalPeriodoPagoAnioActual = 360;
                            }
                            $salarioMes = 0;
                            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                                if($conceptoFijoEmpl->fkConcepto=="1"){
                                    $salarioMes = $conceptoFijoEmpl->valor; 
                                }
                            }

                            //$salarioMes = ($salarioMes / 30) * $periodoPagoMesActual;
                            
                            $itemsBoucherSalarialMesesAnterioresVac = DB::table("item_boucher_pago", "ibp")
                            ->selectRaw("Sum(ibp.valor) as suma")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","<",$fechaInicioMes)
                            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                            ->first();

                            /*
                            echo "vacacionesPTotal->fechaInicio este mes => ".$vacacionesPTotal->fechaInicio."<br>";
                            echo "vacacionesPTotal->diasCompensar este mes => ".$vacacionesPTotal->diasCompensar."<br>";
                            
                            echo "salarialVac este mes => ".$salarialVac."<br>";*/
                            $salarialVac = $salarialVac + $itemsBoucherSalarialMesesAnterioresVac->suma;
                            /*echo "salarialVac => ".$salarialVac."<br>";
                            echo "totalPeriodoPagoAnioActual => ".$totalPeriodoPagoAnioActual."<br>";*/
                            $salarialVac = ($salarialVac / $totalPeriodoPagoAnioActual)*30;
                            
                            //$salarioVac = $salarioMes + $liquidacionesMesesAnterioresCompleta->salarioPago;
                            //$salarioVac = ($salarioVac / $totalPeriodoPagoAnioActual)*30;
                            $salarioVac = 0;

                            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                                if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                                    $salarioVac = $conceptoFijoEmpl->valor; 
                                }
                            }
                            
                            $baseVac = $salarioVac + $salarialVac;
                            /*echo "salarioVac => ".intval($salarioVac)."<br>";
                            echo "salarialVac => ".intval($salarialVac)."<br>";
                            echo "baseVac => ".intval($baseVac)."<br>";
                            */
                            
                            $valorInt = ($baseVac/30)*$vacacionesPTotal->diasCompensar;
                            /*
                            echo "valorInt => ".intval($valorInt)."<br>";
                            exit;*/
                            $arrNovedades = array([
                                "idNovedad" => $novedadyconcepto->idNovedad,
                                "valor" => $valorInt
                            ]);
                            $cantidadInt = $vacacionesPTotal->diasCompensar;

                            $arrValorxConcepto[$novedadyconcepto->idconcepto] = array(
                                "naturaleza" => $novedadyconcepto->fkNaturaleza,
                                "unidad"=>"DIA",
                                "cantidad"=> $cantidadInt,
                                "arrNovedades"=> $arrNovedades,
                                "valor" => $valorInt,
                                "tipoGen" => "novedad"
                            );
                        }
                        
                    
                }
            }
        }

       

        $sqlWhere = "( 
            ('".$fechaInicio."' BETWEEN v.fechaInicio AND v.fechaFin) OR
            ('".$fechaFin."' BETWEEN v.fechaInicio AND v.fechaFin) OR
            (v.fechaInicio BETWEEN '".$fechaInicio."' AND '".$fechaFin."') OR
            (v.fechaFin BETWEEN '".$fechaInicio."' AND '".$fechaFin."')
        )";
        $vacacionesPParcial = DB::table("vacaciones", "v")
        ->select(["v.*","c.*","n.*"])
        ->join("novedad as n", "n.fkVacaciones","=","v.idVacaciones")
        ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","n.fkConcepto")
        ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
        ->where("n.fkEmpleado", "=", $empleado->idempleado)
        ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)
        ->where("v.pagoAnticipado", "=", "0")            
        ->whereIn("n.fkEstado",["7","16"])
        ->whereRaw($sqlWhere)
        ->get();
        //dd($vacacionesPParcial);
        //Agregar valor de las novedades de vacaciones con pago parcial
        foreach($vacacionesPParcial as $vacacionPParcial){

            if($vacacionPParcial->subTipo == "Formula"){             

                $valorFormula = $this->calcularValoresxConceptoxEmpleado($vacacionPParcial->idconcepto, $empleado->idempleado);

                if(isset($arrValorxConcepto[$vacacionPParcial->idconcepto])){
                    
                    $salarialVac = 0;
                    $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                    $grupoConceptoCalculoVac = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "13")//Salarial para provisiones
                        ->get();
                    foreach($grupoConceptoCalculoVac as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $salarialVac = $salarialVac + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }

                    $itemsBoucherSalarialMesAnteriorVac = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",$fechaInicioMes)
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                    ->first();
                    if(isset($itemsBoucherSalarialMesAnteriorVac)){
                        $salarialVac = $salarialVac + $itemsBoucherSalarialMesAnteriorVac->suma;
                    }

                    $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$fechaFin) + 1 ;

                    if(isset($novedadesRetiro)){
                        if(strtotime($fechaFin) > strtotime($novedadesRetiro->fecha)){
                            $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
                        }
                    }

                    $anioActual = intval(date("Y",strtotime($fechaInicio)));
                    $mesActual = intval(date("m",strtotime($fechaInicio)));


                    $liquidacionesMesesAnterioresCompleta = DB::table("liquidacionnomina", "ln")
                    ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<=",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")      
                    ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])          
                    ->first();

                    //Obtener la primera liquidacion de nomina de la persona 
                    $primeraLiquidacion = DB::table("liquidacionnomina", "ln")
                    ->selectRaw("min(ln.fechaInicio) as primeraFecha")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)->first();

                    $minimaFecha = date("Y-m-d");
                    
                    if(isset($primeraLiquidacion)){
                        $minimaFecha = $primeraLiquidacion->primeraFecha;
                    }
                    $diasAgregar = 0;
                    //Verificar si dicha nomina es menor a la fecha de ingreso
                    if(strtotime($empleado->fechaIngreso) < strtotime($minimaFecha)){
                        $diasAgregar = $this->days_360($empleado->fechaIngreso, $minimaFecha);
                    }
                    
                    $periodoNuevo = $this->days_360($fechaInicio,$vacacionPParcial->fechaInicio);

                    $periodoPagoMesActual = $periodoNuevo + $diasAgregar;
                    $totalPeriodoPagoAnioActual = $periodoPagoMesActual + $liquidacionesMesesAnterioresCompleta->periodPago;

                    $salarioMes = 0;
                    $conceptosFijosEmpl = DB::table("conceptofijo", "cf")
                    ->select(["cf.valor","cf.fechaInicio","cf.fechaFin", "cf.fkConcepto","cf.unidad", "c.*"])
                    ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","cf.fkConcepto")
                    ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
                    ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)     
                    ->where("cf.fkEmpleado", "=", $empleado->idempleado)  
                    ->where("cf.fkEstado", "=", "1")
                    ->get();
                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1"){
                            $salarioMes = $conceptoFijoEmpl->valor; 
                        }
                    }

                    $itemsBoucherSalarialMesesAnterioresVac = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                    ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                    ->first();

                    $salarialVac = $salarialVac + $itemsBoucherSalarialMesesAnterioresVac->suma;
                    $salarialVac = ($salarialVac / $totalPeriodoPagoAnioActual)*30;
                    
                    $salarioVac = 0;

                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                            $salarioVac = $conceptoFijoEmpl->valor; 
                        }
                    }

                    
                    $baseVac = $salarioVac + $salarialVac;
                    
                    
                    $diasCompensar = 0;

                    if(strtotime($vacacionPParcial->fechaInicio)>=strtotime($fechaInicio)
                        &&  strtotime($vacacionPParcial->fechaInicio)<=strtotime($fechaFin) 
                        &&  strtotime($vacacionPParcial->fechaFin)>=strtotime($fechaFin))
                    {
                        $diaI = strtotime($vacacionPParcial->fechaInicio);
                        $diaF = strtotime($fechaFin);
                        $diasCompensar = $this->days_360($vacacionPParcial->fechaInicio, $fechaFin) + 1;
                        if(substr($vacacionPParcial->fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($vacacionPParcial->fechaFin)>=strtotime($fechaInicio)  
                    &&  strtotime($vacacionPParcial->fechaFin)<=strtotime($fechaFin) 
                    &&  strtotime($vacacionPParcial->fechaInicio)<=strtotime($fechaInicio))
                    {
                        $diaI = strtotime( $fechaInicio );
                        $diaF = strtotime( $vacacionPParcial->fechaFin );

                        $diasCompensar = $this->days_360($fechaInicio, $vacacionPParcial->fechaFin) + 1;
                        if(substr($fechaInicio, 8, 2) == "31" && substr($vacacionPParcial->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($vacacionPParcial->fechaInicio)<=strtotime($fechaInicio)  
                    &&  strtotime($vacacionPParcial->fechaFin)>=strtotime($fechaFin)) 
                    {
                        $diaI = strtotime( $fechaInicio );
                        $diaF = strtotime( $fechaFin );
                        $diasCompensar = $this->days_360($fechaInicio, $fechaFin) + 1;
                        if(substr($fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($fechaInicio)<=strtotime($vacacionPParcial->fechaInicio)  
                    &&  strtotime($fechaFin)>=strtotime($vacacionPParcial->fechaFin)) 
                    {
                        $diaI = strtotime($vacacionPParcial->fechaInicio);
                        $diaF = strtotime($vacacionPParcial->fechaFin);
                        $diasCompensar = $this->days_360($vacacionPParcial->fechaInicio, $vacacionPParcial->fechaFin) + 1;

                        if(substr($vacacionPParcial->fechaInicio, 8, 2) == "31" && substr($vacacionPParcial->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                        
                    }



                    $valorInt = ($baseVac/30)*$diasCompensar;

                    $valorUnit = $valorInt;
                    $valorInt = $valorInt + $arrValorxConcepto[$vacacionPParcial->idconcepto]["valor"];
                    $cantidadInt = $arrValorxConcepto[$vacacionPParcial->idconcepto]["cantidad"] + floatval($diasCompensar);
                    $arrNovedades=$arrValorxConcepto[$vacacionPParcial->idconcepto]["arrNovedades"];
                    array_push($arrNovedades, [
                        "idNovedad" => $vacacionPParcial->idNovedad,
                        "parcial" => 1,
                        "valor" => $valorUnit
                    ]);
                    $arrValorxConcepto[$vacacionPParcial->idconcepto] = array(
                        "naturaleza" => $vacacionPParcial->fkNaturaleza,
                        "unidad"=>"DIA",
                        "cantidad"=> $cantidadInt,
                        "arrNovedades"=> $arrNovedades,
                        "valor" => $valorInt,
                        "tipoGen" => "novedad",
                        "base" => $baseVac
                    );
                }
                else{

                    $salarialVac = 0;
                    $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                    $grupoConceptoCalculoVac = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "13")//Salarial para provisiones
                        ->get();
                    foreach($grupoConceptoCalculoVac as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $salarialVac = $salarialVac + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }


                    $itemsBoucherSalarialMesAnteriorVac = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",$fechaInicioMes)
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                    ->first();
                    if(isset($itemsBoucherSalarialMesAnteriorVac)){
                        $salarialVac = $salarialVac + $itemsBoucherSalarialMesAnteriorVac->suma;

                    }
                    

                    $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$fechaFin) + 1 ;

                    if(isset($novedadesRetiro)){
                        if(strtotime($fechaFin) > strtotime($novedadesRetiro->fecha)){
                            $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
                        }
                    }
                    
                
                    // $diasVac = $totalPeriodoPagoAnioActual * 15 / 360;
                    
                    $anioActual = intval(date("Y",strtotime($fechaInicio)));
                    $mesActual = intval(date("m",strtotime($fechaInicio)));


                    $liquidacionesMesesAnterioresCompleta = DB::table("liquidacionnomina", "ln")
                    ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<=",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                    ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                    ->first();


                    //Obtener la primera liquidacion de nomina de la persona 
                    $primeraLiquidacion = DB::table("liquidacionnomina", "ln")
                    ->selectRaw("min(ln.fechaInicio) as primeraFecha")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)->first();

                    $minimaFecha = date("Y-m-d");
                    
                    if(isset($primeraLiquidacion)){
                        $minimaFecha = $primeraLiquidacion->primeraFecha;
                    }
                    $diasAgregar = 0;
                    //Verificar si dicha nomina es menor a la fecha de ingreso
                    if(strtotime($empleado->fechaIngreso) < strtotime($minimaFecha)){
                        $diasAgregar = $this->days_360($empleado->fechaIngreso, $minimaFecha);
                    }
                    
                    
                    

                    
                    
                    $periodoNuevo = $this->days_360($fechaInicio,$vacacionPParcial->fechaInicio);

                    $periodoPagoMesActual = $periodoNuevo + $diasAgregar;


                    $totalPeriodoPagoAnioActual = $periodoPagoMesActual + $liquidacionesMesesAnterioresCompleta->periodPago;

                    $salarioMes = 0;
                    $conceptosFijosEmpl = DB::table("conceptofijo", "cf")
                    ->select(["cf.valor","cf.fechaInicio","cf.fechaFin", "cf.fkConcepto","cf.unidad", "c.*"])
                    ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","cf.fkConcepto")
                    ->join("concepto AS c", "ctl.fkConcepto","=","c.idconcepto")
                    ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)     
                    ->where("cf.fkEmpleado", "=", $empleado->idempleado)  
                    ->where("cf.fkEstado", "=", "1")
                    ->get();
                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1"){
                            $salarioMes = $conceptoFijoEmpl->valor; 
                        }
                    }

                    //$salarioMes = ($salarioMes / 30) * $periodoPagoMesActual;
                    
                    $itemsBoucherSalarialMesesAnterioresVac = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                    ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                    ->first();


                    $salarialVac = $salarialVac + $itemsBoucherSalarialMesesAnterioresVac->suma;
                    $salarialVac = ($salarialVac / $totalPeriodoPagoAnioActual)*30;
                    
                    $salarioVac = 0;

                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                            $salarioVac = $conceptoFijoEmpl->valor; 
                        }
                    }


                    
                    $baseVac = $salarioVac + $salarialVac;

    
                    
                    $diasCompensar = 0;

                    if(strtotime($vacacionPParcial->fechaInicio)>=strtotime($fechaInicio)
                        &&  strtotime($vacacionPParcial->fechaInicio)<=strtotime($fechaFin) 
                        &&  strtotime($vacacionPParcial->fechaFin)>=strtotime($fechaFin))
                    {
                        $diaI = strtotime($vacacionPParcial->fechaInicio);
                        $diaF = strtotime($fechaFin);
                        $diasCompensar = $this->days_360($vacacionPParcial->fechaInicio, $fechaFin) + 1;
                        if(substr($vacacionPParcial->fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($vacacionPParcial->fechaFin)>=strtotime($fechaInicio)  
                    &&  strtotime($vacacionPParcial->fechaFin)<=strtotime($fechaFin) 
                    &&  strtotime($vacacionPParcial->fechaInicio)<=strtotime($fechaInicio))
                    {
                        $diaI = strtotime( $fechaInicio );
                        $diaF = strtotime( $vacacionPParcial->fechaFin );

                        $diasCompensar = $this->days_360($fechaInicio, $vacacionPParcial->fechaFin) + 1;
                        if(substr($fechaInicio, 8, 2) == "31" && substr($vacacionPParcial->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($vacacionPParcial->fechaInicio)<=strtotime($fechaInicio)  
                    &&  strtotime($vacacionPParcial->fechaFin)>=strtotime($fechaFin)) 
                    {
                        $diaI = strtotime( $fechaInicio );
                        $diaF = strtotime( $fechaFin );
                        $diasCompensar = $this->days_360($fechaInicio, $fechaFin) + 1;
                        if(substr($fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($fechaInicio)<=strtotime($vacacionPParcial->fechaInicio)  
                    &&  strtotime($fechaFin)>=strtotime($vacacionPParcial->fechaFin)) 
                    {
                        $diaI = strtotime($vacacionPParcial->fechaInicio);
                        $diaF = strtotime($vacacionPParcial->fechaFin);
                        $diasCompensar = $this->days_360($vacacionPParcial->fechaInicio, $vacacionPParcial->fechaFin) + 1;

                        if(substr($vacacionPParcial->fechaInicio, 8, 2) == "31" && substr($vacacionPParcial->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                        
                    }

                    $diferenciaDiasPeriodo = $this->days_360($vacacionPParcial->fechaInicio, $vacacionPParcial->fechaFin);
                    
                    
                    
                    
                    
                    $valorInt = ($baseVac/30)*$diasCompensar;

                    /*echo "valorInt => ".intval($valorInt)."<br>";
                    exit;*/
                    $arrNovedades = array([
                        "idNovedad" => $vacacionPParcial->idNovedad,
                        "valor" => $valorInt,
                        "parcial" => 1
                    ]);
                    $cantidadInt = $diasCompensar;

                    $arrValorxConcepto[$vacacionPParcial->idconcepto] = array(
                        "naturaleza" => $vacacionPParcial->fkNaturaleza,
                        "unidad"=>"DIA",
                        "cantidad"=> $cantidadInt,
                        "arrNovedades"=> $arrNovedades,
                        "valor" => $valorInt,
                        "tipoGen" => "novedad",
                        "base" => $baseVac
                    );
                }
            }                        
            
        }


        //En caso de que el empleado tenga un salario integral, todo lo que se comprende dentro de salarios (4) se toma solo el 70%
        $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
            ->where("gcc.fkGrupoConcepto", "=", "4")->get();
        foreach($grupoConceptoCalculo as $grupoConcepto){
            if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                if($empleado->tipoRegimen == "Salario Integral" && $arrValorxConcepto[$grupoConcepto->fkConcepto]["naturaleza"]=="1"){
                    $arrValorxConcepto[$grupoConcepto->fkConcepto]["valor"] =  $arrValorxConcepto[$grupoConcepto->fkConcepto]["valor"] * 0.7;
                }
            }   
        }
        
        foreach($arrValorxConcepto as $idConcepto => $arrConcepto){                
            if($arrConcepto["tipoGen"] != "novedad"){//Si no es una novedad
                if($arrConcepto["unidad"]=="MES"){
                    
                    $arrConcepto["valor"] = $periodoGen * ($arrConcepto["valor"]/30);
                    $arrConcepto["cantidad"]=$periodoGen;
                    $arrConcepto["unidad"] = "DIA";                        
                }
            }
            if($arrConcepto["naturaleza"]=="3"){
                $arrConcepto["valor"] = $arrConcepto["valor"] * -1;
            }            
            $arrValorxConcepto[$idConcepto] = $arrConcepto;
            //Faltan otros tipos de unidades OJO
        }

        $arrValorxConceptoParaSu = $arrValorxConcepto;
        foreach($arrValorxConceptoParaSu as $idConcepto => $arrConcepto){                
            if($arrConcepto["tipoGen"] != "novedad"){//Si no es una novedad
                if($arrConcepto["unidad"]=="MES"){
                    $arrConcepto["valor"] = $periodoGen * ($arrConcepto["valor"]/30);
                    $arrConcepto["cantidad"]=$periodoGen;
                    $arrConcepto["unidad"] = "DIA";                        
                }
            }
            if($arrConcepto["naturaleza"]=="3"){
                $arrConcepto["valor"] = $arrConcepto["valor"] * -1;
            }            
            $arrValorxConceptoParaSu[$idConcepto] = $arrConcepto;
            //Faltan otros tipos de unidades OJO
        }

        foreach($arrValorxConceptoOtros as $idConceptoOtros => $arrConceptoOtros){                
            if(isset($arrValorxConcepto[$idConceptoOtros]))
            {
                $arrValorxConceptoParaSu[$idConceptoOtros]["valor"] = $arrValorxConceptoParaSu[$idConceptoOtros]["valor"] + $arrConceptoOtros['valor'];
            }
            else{
                $arrValorxConceptoParaSu[$idConceptoOtros] = $arrConceptoOtros;
            }
        }
       
        $periodoParaSub = $periodoGen;
        if($periodo == 15){
            if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";

                $itemsAntBoucherPago = DB::table("item_boucher_pago","ibp")
                ->select("ibp.*")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=", $empleado->idempleado)
                ->where("ln.fkEstado","=","5")//Terminada
                ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                ->get();
                foreach($itemsAntBoucherPago as $itemAntBoucherPago){
                    if(isset($arrValorxConceptoParaSu[$itemAntBoucherPago->fkConcepto])){
                        $arrValorxConceptoParaSu[$itemAntBoucherPago->fkConcepto]["valor"] = $arrValorxConceptoParaSu[$itemAntBoucherPago->fkConcepto]["valor"] + $itemAntBoucherPago->valor;
                    }
                    else{
                        $arrValorxConceptoParaSu[$itemAntBoucherPago->fkConcepto]["valor"] = $itemAntBoucherPago->valor;
                    }
                } 
                
                $BoucherPagoAnt = DB::table("boucherpago","bp")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=", $empleado->idempleado)
                ->where("ln.fkEstado","=","5")//Terminada
                ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                ->first();
                if(isset($BoucherPagoAnt)){
                    $periodoParaSub = $periodoParaSub + $BoucherPagoAnt->periodoPago;
                }
                
            }
        }

        $subsidio = DB::table("concepto", "c")
        ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","c.idconcepto")
        ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)     
        ->where("c.idconcepto", "=", "5")
        ->get();


       
        

        //Agregar el valor del subisidio de transporte en caso de que exista dentro de la liquidacion actual
        foreach($subsidio as $automatico){
            if($this->condicionesxConceptoEnArray($automatico->idconcepto, $empleado->idempleado, $arrValorxConceptoParaSu, $periodoParaSub)){
            if($automatico->subTipo == "Tabla"){                        
                    $variableFinal = DB::table('variable')->where("idVariable","=",$automatico->fkVariable)->first();
                    $valorFormula = floatval($variableFinal->valor);

                    if(isset($arrValorxConcepto[$automatico->idconcepto])){
                        
                        $valorInt = $arrValorxConcepto[$automatico->idconcepto]["valor"] + $valorFormula;
                        $arrValorxConcepto[$automatico->idconcepto] = array(
                            "naturaleza" => $automatico->fkNaturaleza,
                            "unidad" => "MES",
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "automaticos"
                        );                            
                    }
                    else{
                        $valorInt = $valorFormula;
                        $arrValorxConcepto[$automatico->idconcepto] = array(
                            "naturaleza" => $automatico->fkNaturaleza,
                            "unidad" => "MES",
                            "cantidad"=> 0,
                            "arrNovedades"=> array(),
                            "valor" => $valorInt,
                            "tipoGen" => "automaticos"
                        );
                    }                
                }    
            }                           
        }
        

        foreach($arrValorxConcepto as $idConcepto => $arrConcepto){                
            if($arrConcepto["tipoGen"] != "novedad"){//Si no es una novedad
                if($arrConcepto["unidad"]=="MES"){
                    $arrConcepto["valor"] = $periodoGen * ($arrConcepto["valor"]/30);
                    $arrConcepto["cantidad"]=$periodoGen;
                    $arrConcepto["unidad"] = "DIA";                        
                }
            }
            if($arrConcepto["naturaleza"]=="3"){
                $arrConcepto["valor"] = $arrConcepto["valor"] * -1;
            }            
            $arrValorxConcepto[$idConcepto] = $arrConcepto;
            //Faltan otros tipos de unidades OJO
        }


        //Añadir novedades "otros" al array de los conceptos
        
    
        foreach($arrValorxConceptoOtros as $idConceptoOtros => $arrConceptoOtros){                
            if($arrConceptoOtros["naturaleza"]=="3"){
                $arrConceptoOtros["valor"] = $arrConceptoOtros["valor"] * -1;
            }       

            if(isset($arrValorxConcepto[$idConceptoOtros]))
            {
                $arrValorxConcepto[$idConceptoOtros]["valor"] = $arrValorxConcepto[$idConceptoOtros]["valor"] + $arrConceptoOtros['valor'];
            }
            else{
                $arrValorxConcepto[$idConceptoOtros] = $arrConceptoOtros;
            }
        }
        
    

        //Calculo maximo 40% para no salarial

        $totalRemuneracion = 0;
        foreach($arrValorxConcepto as $idConcepto => $arrConcepto){
            if($arrConcepto['valor'] > 0 && $idConcepto != 5 && $idConcepto!=36 && $idConcepto!=28){
                $totalRemuneracion = $totalRemuneracion + $arrConcepto['valor'];
            }                
        }

        $totalNoSalarialRemuneracion = 0;
        $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
            ->where("gcc.fkGrupoConcepto", "=", "5")                       
            ->get();
        foreach($grupoConceptoCalculo as $grupoConcepto){
            if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto!=36){
                $totalNoSalarialRemuneracion = $totalNoSalarialRemuneracion + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
            }
        }
        
        //dump($totalRemuneracion);
        
        $limite40Por = $totalRemuneracion * 0.4;
        
        //dump($limite40Por);
        //dump($totalNoSalarialRemuneracion);

        if($totalNoSalarialRemuneracion > $limite40Por){
            $valorInt = $totalNoSalarialRemuneracion - $limite40Por;
            
            $arrValorxConcepto[32] = array(
                "naturaleza" => '1',
                "unidad" => "DIA",
                "cantidad"=> 0,
                "arrNovedades"=> array(),
                "valor" => $valorInt,
                "tipoGen" => "automaticos"
            );   
            //dd($valorInt);
        }




        $arrBoucherPago = array();
        $arrParafiscales = array();
        //Guardar IBC 

        $ibcGeneral = 0;
        $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
        ->where("gcc.fkGrupoConcepto", "=", '4')//4->Salarial Nomina
        ->get();
        $salarioMaximo = ($salarioMinimoDia * 30) * 25;
        


        foreach($grupoConceptoCalculo as $grupoConcepto){
            if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto!=36){
                $ibcGeneral= $ibcGeneral + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                
            }
        }

        
        
        

        if($ibcGeneral > $salarioMaximo){
            $ibcGeneral = $salarioMaximo;
        }
        
        $arrBoucherPago["ibc_afp"] = intval($ibcGeneral);
        $arrBoucherPago["ibc_eps"] = intval($ibcGeneral);
        $arrBoucherPago["ibc_arl"] = intval($ibcGeneral);
        $arrBoucherPago["ibc_ccf"] = intval($ibcGeneral);
        $arrBoucherPago["ibc_otros"] = intval($ibcGeneral);
        

    

        $valorSalarioMinimo = 30 * $salarioMinimoDia;

        $variablesParafiscales = DB::table("variable")
        ->where("idVariable",">=","49")
        ->where("idVariable","<=","56")
        ->get();

        $varParafiscales = array();
        foreach($variablesParafiscales as $variablesParafiscal){
            $varParafiscales[$variablesParafiscal->idVariable] = $variablesParafiscal->valor;
        }

        //Calculo EPS
        



        $valorEpsEmpleado = $arrBoucherPago["ibc_eps"] * $varParafiscales[49];
        /*$valorEpsEmpleado = $valorEpsEmpleado / 100;
        $valorEpsEmpleado = ceil($valorEpsEmpleado);
        $valorEpsEmpleado = $valorEpsEmpleado*100;*/

        $valorEpsEmpleado = round($valorEpsEmpleado);

        if(isset($arrValorxConcepto[18])){
            $arrValorxConcepto[18] = array(
                "naturaleza" => "3",
                "unidad" => "DIA",
                "cantidad"=> $periodoPago,
                "arrNovedades"=> array(),
                "valor" => ($arrValorxConcepto[18]["valor"] - $valorEpsEmpleado),
                "tipoGen" => "automaticos"
            );
        }
        else{
            $arrValorxConcepto[18] = array(
                "naturaleza" => "3",
                "unidad" => "DIA",
                "cantidad"=> $periodoPago,
                "arrNovedades"=> array(),
                "valor" => $valorEpsEmpleado*-1 ,
                "tipoGen" => "automaticos"
            );
    
        }
        if($periodo == 15){
            if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";
            } 
        }
        $ibcGeneral2 = $ibcGeneral;
        if(isset($fechaPrimeraQuincena)){ 
            if($empleado->tipoRegimen != "Salario Integral"){
                $itemsBoucherIbcOtrosMesAnterior = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.valor) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                ->where("gcc.fkGrupoConcepto","=","4") //4 - Salarial nomina
                ->first();
    
                if(isset($itemsBoucherIbcOtrosMesAnterior)){
                    $ibcGeneral2 = $ibcGeneral2 + $itemsBoucherIbcOtrosMesAnterior->suma;
                }
            }           
        }
        $arrBoucherPago["ibc_arl"] = intval($ibcGeneral2);
        $arrBoucherPago["ibc_ccf"] = intval($ibcGeneral2);
        $arrBoucherPago["ibc_otros"] = intval($ibcGeneral2);
        
        $valorEpsEmpleador = $ibcGeneral2 * $varParafiscales[50];
        $valorEpsEmpleador = round($valorEpsEmpleador);


        $arrParafiscales["eps"] = $valorEpsEmpleador;
        
        $existeConceptoUPCLiq = DB::table("conceptosxtipoliquidacion","ctl")
        ->where("ctl.fkConcepto","=","79")
        ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion) 
        ->get();
        
        if($periodo == 15 && sizeof($existeConceptoUPCLiq)>0){
            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                $upcAdicionales = DB::table("upcadicional","u")
                ->select("u.*","ti.siglaPila","ub.zonaUPC")
                ->join("tipoidentificacion as ti","ti.idtipoIdentificacion","=","u.fkTipoIdentificacion")
                ->join("ubicacion as ub", "ub.idubicacion", "=","u.fkUbicacion")
                ->where("u.fkEmpleado","=",$empleado->idempleado)
                ->get();
                $valorUpcAd = 0;
                foreach($upcAdicionales as $upcAdicional){
                    $edad = strtotime("now") - strtotime($upcAdicional->fechaNacimiento);
                    $edad = $edad / (60* 60 * 24 * 360);
                    $edad = intval($edad);


                    $tarifasUpc = DB::table("upcadicionaltarifas", "ut")
                    ->join("upcadicionaledades as ue", "ue.idUpcAdicionalTabla", "=","ut.fkUpcEdad");
                    if($edad == 0){
                        $tarifasUpc = $tarifasUpc->where("ut.fkUpcEdad", "=", "1");
                    }
                    else if($edad >= 75){
                        $tarifasUpc = $tarifasUpc->where("ut.fkUpcEdad", "=", "14");
                    }
                    else{
                        $tarifasUpc = $tarifasUpc->where("ue.edadMinima", "<=", $edad);
                        $tarifasUpc = $tarifasUpc->where("ue.edadMaxima", ">=", $edad);
                    }
                    $tarifasUpc = $tarifasUpc->where("ut.zona", "=", $upcAdicional->zonaUPC)
                    ->get();

                    foreach($tarifasUpc as $tarifaUpc){
                        if(!isset($tarifaUpc->fkGenero) || $tarifaUpc->fkGenero == $upcAdicional->fkGenero){
                            $valorUpcAd = $valorUpcAd + $tarifaUpc->valor;
                        }
                    }
                }
                if($valorUpcAd > 0){
                    if(isset($arrValorxConcepto[79])){
                        $arrValorxConcepto[79] = array(
                            "naturaleza" => "3",
                            "unidad" => "UNIDAD",
                            "cantidad"=> "0",
                            "arrNovedades"=> array(),
                            "valor" => ($arrValorxConcepto[79]["valor"] - $valorUpcAd),
                            "tipoGen" => "automaticos"
                        );
                    }
                    else{
                        $arrValorxConcepto[79] = array(
                            "naturaleza" => "3",
                            "unidad" => "UNIDAD",
                            "cantidad"=> "0",
                            "arrNovedades"=> array(),
                            "valor" => $valorUpcAd*-1 ,
                            "tipoGen" => "automaticos"
                        );
                    }
                   
                }
            }
        }


        //Calculo AFP
        $valorAfpEmpleador = 0;
        $valorAfpEmpleado = 0;
        if($empleado->esPensionado == 0){
            $valorAfpEmpleado = $arrBoucherPago["ibc_afp"] * $varParafiscales[51];
            $valorAfpEmpleado = round($valorAfpEmpleado);
            if(isset($arrValorxConcepto[19])){
                $arrValorxConcepto[19] = array(
                    "naturaleza" => "3",
                    "unidad" => "DIA",
                    "cantidad"=> $periodoPago,
                    "arrNovedades"=> array(),
                    "valor" => ($arrValorxConcepto[19]["valor"] - $valorAfpEmpleado) ,
                    "tipoGen" => "automaticos"
                );
            }
            else{
                $arrValorxConcepto[19] = array(
                    "naturaleza" => "3",
                    "unidad" => "DIA",
                    "cantidad"=> $periodoPago,
                    "arrNovedades"=> array(),
                    "valor" => $valorAfpEmpleado*-1 ,
                    "tipoGen" => "automaticos"
                );
            }
            
            $valorAfpEmpleador = $ibcGeneral2 * $varParafiscales[52];
            $valorAfpEmpleador = round($valorAfpEmpleador);
        }
        else{
            $arrBoucherPago["ibc_afp"] = 0;
        }
        
        $arrParafiscales["afp"] = $valorAfpEmpleador;

        //Calculo ARL
        $arl = DB::table('nivel_arl', 'na')
        ->join("empleado as e", "e.fkNivelArl", "=","na.idnivel_arl")
        ->where("e.idempleado", "=", $empleado->idempleado)
        ->first();
        
        $valorArlEmpleador = $arrBoucherPago["ibc_arl"] * (floatval($arl->porcentaje) / 100);
        $valorArlEmpleador = round($valorArlEmpleador);
        $arrParafiscales["arl"] = $valorArlEmpleador;

        //Calculo CCF
        $valorCCFEmpleador = $arrBoucherPago["ibc_ccf"] * $varParafiscales[53];
        $valorCCFEmpleador = round($valorCCFEmpleador);
        $arrParafiscales["ccf"] = $valorCCFEmpleador;


        if($empresa->exento == "0" || $arrBoucherPago["ibc_otros"] > ($varParafiscales[56] * $valorSalarioMinimo)){

            //Calculo ICBF
            $valorICBFEmpleador = $arrBoucherPago["ibc_otros"] * $varParafiscales[54];
            $valorICBFEmpleador = round($valorICBFEmpleador);
            $arrParafiscales["icbf"] = $valorICBFEmpleador;

            //Calculo SENA
            $valorSenaEmpleador = $arrBoucherPago["ibc_otros"] * $varParafiscales[55];
            $valorSenaEmpleador = round($valorSenaEmpleador);
            $arrParafiscales["sena"] = $valorSenaEmpleador;

           
        }
        else{
            $arrParafiscales["icbf"] = 0;
            $arrParafiscales["sena"] = 0;
            $arrParafiscales["eps"] = 0;
            $arrBoucherPago["ibc_otros"] = 0;
        }




        if($periodo == 15){
            if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";

                $boucherPago = DB::table("boucherpago","bp")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=", $empleado->idempleado)
                ->where("ln.fkEstado","=","5")//Terminada
                ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                ->get();
                
                
                $ibcGeneral = 0;
                if(sizeof($boucherPago)>0){
                    foreach($boucherPago as $boucherPagoG){
                        
                        $ibcGeneral = $boucherPagoG->ibc_afp;
                        

                    }
                }

                $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                ->where("gcc.fkGrupoConcepto", "=", '4')//4->Salarial Nomina
                ->get();
                $salarioMaximo = ($salarioMinimoDia * 30) * 25;
                foreach($grupoConceptoCalculo as $grupoConcepto){
                    if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                        $ibcGeneral= $ibcGeneral + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                    }
                }
           
                if($ibcGeneral > $salarioMaximo){
                    $ibcGeneral = $salarioMaximo;
                }
                
                
             
                $arrBoucherPago["ibc_afp"] = intval($ibcGeneral);
                $arrBoucherPago["ibc_eps"] = intval($ibcGeneral);
                $arrBoucherPago["ibc_arl"] = intval($ibcGeneral);
                $arrBoucherPago["ibc_ccf"] = intval($ibcGeneral);
                $arrBoucherPago["ibc_otros"] = intval($ibcGeneral);
                
                if($empresa->exento == "0" || $arrBoucherPago["ibc_otros"] > ($varParafiscales[56] * $valorSalarioMinimo)){
                    
                    //Calculo ICBF
                    $valorICBFEmpleador = $arrBoucherPago["ibc_otros"] * $varParafiscales[54];
                    
                    /*$valorICBFEmpleador = $valorICBFEmpleador / 100;
                    $valorICBFEmpleador = ceil($valorICBFEmpleador);
                    $valorICBFEmpleador = $valorICBFEmpleador*100;*/
                    $valorICBFEmpleador = round($valorICBFEmpleador);
        
                    $arrParafiscales["icbf"] = $valorICBFEmpleador;
            
                    //Calculo SENA
                    $valorSenaEmpleador = $arrBoucherPago["ibc_otros"] * $varParafiscales[55];
                    
                    /*$valorSenaEmpleador = $valorSenaEmpleador / 100;
                    $valorSenaEmpleador = ceil($valorSenaEmpleador);
                    $valorSenaEmpleador = $valorSenaEmpleador*100;*/
                    $valorSenaEmpleador = round($valorSenaEmpleador);
        
                    $arrParafiscales["sena"] = $valorSenaEmpleador;
        
                }
                else{
                    $arrParafiscales["icbf"] = 0;
                    $arrParafiscales["sena"] = 0;
                    $arrParafiscales["eps"] = 0;
                    $arrBoucherPago["ibc_otros"] = 0;
                }



            }

            
        }
        
        
    

        //Nota: si ya se encuentra pensionado no se aplica este concepto      
        if($empleado->esPensionado == 0){
            $aporteFondoSoliradidad = DB::table("concepto", "c")
            ->join("conceptosxtipoliquidacion AS ctl", "ctl.fkConcepto","=","c.idconcepto")
            ->where("ctl.fkTipoLiquidacion","=",$tipoliquidacion)     
            ->whereIn("c.idconcepto",["33"])
            ->get();

            $porcentajeDescuento = 0;
            
            $valorSalario = $arrBoucherPago["ibc_eps"];            

            $variablesAporteFondo = DB::table("variable")->whereIn("idVariable",[11,12,13,14,15])->get();
            $varAporteFondo = array();
            foreach($variablesAporteFondo as $variablesAporteFond){
                $varAporteFondo[$variablesAporteFond->idVariable] = $variablesAporteFond->valor;
            }

            foreach($aporteFondoSoliradidad as $automatico){
                if($valorSalario > ($valorSalarioMinimo * $varAporteFondo[11])){
                    $porcentajeDescuento = $varAporteFondo[12];
                }

                if($valorSalario > ($valorSalarioMinimo * $varAporteFondo[13])){

                    $diffSalariosMas = $valorSalario - ($valorSalarioMinimo * ($varAporteFondo[13] - 1));
                    $numSalariosMas = floor($diffSalariosMas  / $valorSalarioMinimo);
                    $porcentajeDescuento = $porcentajeDescuento + ($numSalariosMas * $varAporteFondo[14]);
                }
                if($porcentajeDescuento > $varAporteFondo[15]){
                    $porcentajeDescuento = $varAporteFondo[15];
                }
            
                $valorFormula = $valorSalario * $porcentajeDescuento;
                $valorInt = $valorFormula;

                if($valorInt > 0){


                    if($periodo == 30){

                        if(isset($arrValorxConcepto[$automatico->idconcepto])){
                            $arrValorxConcepto[$automatico->idconcepto] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> 0,
                                "arrNovedades"=> array(),
                                "valor" => ($arrValorxConcepto[$automatico->idconcepto]["valor"] - $valorInt),
                                "tipoGen" => "automaticos"
                            );
                        }
                        else{
                            $arrValorxConcepto[$automatico->idconcepto] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> 0,
                                "arrNovedades"=> array(),
                                "valor" => $valorInt*-1 ,
                                "tipoGen" => "automaticos"
                            );
                        }

                        
                    }
                    else{
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){

                            if(isset($arrValorxConcepto[$automatico->idconcepto])){
                                $arrValorxConcepto[$automatico->idconcepto] = array(
                                    "naturaleza" => "3",
                                    "unidad" => "UNIDAD",
                                    "cantidad"=> 0,
                                    "arrNovedades"=> array(),
                                    "valor" => ($arrValorxConcepto[$automatico->idconcepto]["valor"] - $valorInt),
                                    "tipoGen" => "automaticos"
                                );
                            }
                            else{
                                $arrValorxConcepto[$automatico->idconcepto] = array(
                                    "naturaleza" => "3",
                                    "unidad" => "UNIDAD",
                                    "cantidad"=> 0,
                                    "arrNovedades"=> array(),
                                    "valor" => $valorInt*-1 ,
                                    "tipoGen" => "automaticos"
                                );
                            }

                            
                        }
                        else{
                            $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";
                            
                            $itemFPS = DB::table("item_boucher_pago","ibp")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->where("bp.fkEmpleado","=", $empleado->idempleado)
                            ->where("ln.fkEstado","=","5")//Terminada
                            ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                            ->where("ibp.fkConcepto","=","33")//FONDO DE SOLIDARIDAD                
                            ->get();

                            if(sizeof($itemFPS)> 0){
                                $valorInt = $valorInt - $itemFPS[0]->descuento;
                            }
                        
                            if(isset($arrValorxConcepto[$automatico->idconcepto])){
                                $arrValorxConcepto[$automatico->idconcepto] = array(
                                    "naturaleza" => "3",
                                    "unidad" => "UNIDAD",
                                    "cantidad"=> 0,
                                    "arrNovedades"=> array(),
                                    "valor" => ($arrValorxConcepto[$automatico->idconcepto]["valor"] - $valorInt),
                                    "tipoGen" => "automaticos"
                                );
                            }
                            else{
                                $arrValorxConcepto[$automatico->idconcepto] = array(
                                    "naturaleza" => "3",
                                    "unidad" => "UNIDAD",
                                    "cantidad"=> 0,
                                    "arrNovedades"=> array(),
                                    "valor" => $valorInt*-1 ,
                                    "tipoGen" => "automaticos"
                                );
                            }
                        }
                    }
                        
                }
                                
            }
        }


        $variableUVT = DB::table("variable")->where("idVariable","=","10")->first();
        $uvtActual = intval($variableUVT->valor);

        $arrayRetencion = array();
        $arrayRetencionInd = array();
        $arrayRetencionPri = array();
        
        

        $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
        ->where("gcc.fkGrupoConcepto", "=", "4")->get();
        foreach($grupoConceptoCalculo as $grupoConcepto){
            if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                if($empleado->tipoRegimen == "Salario Integral" && $arrValorxConcepto[$grupoConcepto->fkConcepto]["naturaleza"]=="1"){
                    $arrValorxConcepto[$grupoConcepto->fkConcepto]["valor"] =  ($arrValorxConcepto[$grupoConcepto->fkConcepto]["valor"] * 100)/70;
                }
            }   
        }

        $ingreso = 0;
        $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
            ->where("gcc.fkGrupoConcepto", "=", "9")
            ->get();

        
        foreach($grupoConceptoCalculo as $grupoConcepto){
            if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto!=36){
                $ingreso = $ingreso + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
            }
        }
        

        
        

        if($tipoliquidacion == "1" || $tipoliquidacion == "5" || $tipoliquidacion == "6"){
            $variablesRetencion = DB::table("variable")
                ->where("idVariable",">=","16")
                ->where("idVariable","<=","48")
                ->get();
            $varRetencion = array();
            foreach($variablesRetencion as $variablesRetencio){
                $varRetencion[$variablesRetencio->idVariable] = $variablesRetencio->valor;
            }
            $FPS = (isset($arrValorxConcepto[33]) ? $arrValorxConcepto[33]['valor'] : 0);
            $EPS = (isset($arrValorxConcepto[18]) ? $arrValorxConcepto[18]['valor'] : 0);
            $AFP = (isset($arrValorxConcepto[19]) ? $arrValorxConcepto[19]['valor'] : 0);
            $SS = ($FPS + $EPS + $AFP) * -1;


            if($periodo == 15){
                if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                    $itemsBoucherPago = DB::table("item_boucher_pago","ibp")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=", $empleado->idempleado)
                    ->where("ln.fkEstado","=","5")//Terminada
                    ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                    ->where("gcc.fkGrupoConcepto", "=", "9")
                    ->get();
    
                    foreach($itemsBoucherPago as $itemBoucherPago){
                        $ingreso = $ingreso + floatval($itemBoucherPago->pago);
                    }

                    $itemsBoucherPagoSS = DB::table("item_boucher_pago","ibp")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=", $empleado->idempleado)
                    ->where("ln.fkEstado","=","5")//Terminada
                    ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                    ->whereIn("ibp.fkConcepto",["33","18","19"])//SS
                    ->get();
                    foreach($itemsBoucherPagoSS as $itemBoucherPagoSS){
                        $SS = $SS + floatval($itemBoucherPagoSS->descuento);
                        if($itemBoucherPagoSS->fkConcepto == "33"){
                            $FPS = $FPS + $itemBoucherPagoSS->valor;
                        }
                        else if($itemBoucherPagoSS->fkConcepto == "18"){
                            $EPS = $EPS + $itemBoucherPagoSS->valor;
                        }
                        else if($itemBoucherPagoSS->fkConcepto == "19"){
                            $AFP = $AFP + $itemBoucherPagoSS->valor;
                        }

                    }
                }
            }
            

            $rentaLiquida = $ingreso - $SS;

            $interesesVivienda = 0;
            $beneficiosTributarioIntVivienda = DB::table("beneficiotributario", "bt")
                ->selectRaw("sum(bt.valorMensual) as suma")
                ->where("bt.fkEmpleado","=",$empleado->idempleado)
                ->where("bt.fkTipoBeneficio", "=", "2")
                ->whereDate("bt.fechaVigencia",">=", $fechaFin)
                ->get();
            
            $interesesVivienda = intval($beneficiosTributarioIntVivienda[0]->suma);

            if($interesesVivienda > round($uvtActual * $varRetencion[16], -3)){
                $interesesVivienda = round($uvtActual * $varRetencion[16], -3);
            }
            
            $medicinaPrepagada = 0;
            $beneficiosTributarioMedicinaPrepagada = DB::table("beneficiotributario", "bt")
                ->selectRaw("sum(bt.valorMensual) as suma")
                ->where("bt.fkEmpleado","=",$empleado->idempleado)
                ->where("bt.fkTipoBeneficio", "=", "3")
                ->whereDate("bt.fechaVigencia",">=", $fechaFin)
                ->get();
            
            $medicinaPrepagada = intval($beneficiosTributarioMedicinaPrepagada[0]->suma);
            
            if($medicinaPrepagada > round($uvtActual * $varRetencion[17], -3)){
                $medicinaPrepagada = round($uvtActual * $varRetencion[17], -3);
            }

            //Calcular cuanto cuesta este dependiente
            $dependiente = 0;
            $beneficiosTributarioDependiente = DB::table("beneficiotributario", "bt")
                ->select("bt.idBeneficioTributario")
                ->where("bt.fkEmpleado","=",$empleado->idempleado)
                ->where("bt.fkTipoBeneficio", "=", "4")
                ->whereDate("bt.fechaVigencia",">=", $fechaFin)
                ->get();
            
            if(sizeof($beneficiosTributarioDependiente)> 0){
                $dependiente = ($ingreso * $varRetencion[18]);
            }
                        
            //Tope maximo dependencia
            if($dependiente > round($uvtActual * $varRetencion[19], -3)){
                $dependiente = round($uvtActual * $varRetencion[19], -3);
            }

            $aporteVoluntario = 0;
            $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                ->where("gcc.fkGrupoConcepto", "=", "6")
                ->get();
            foreach($grupoConceptoCalculo as $grupoConcepto){
                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                    $aporteVoluntario = $aporteVoluntario + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                }
            }
            $aporteVoluntario = $aporteVoluntario * -1;
            if($aporteVoluntario > round($rentaLiquida * $varRetencion[20], -3)){
                $aporteVoluntario = round($rentaLiquida * $varRetencion[20], -3);
            }
            
            $AFC = 0;
            $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                ->where("gcc.fkGrupoConcepto", "=", "8")
                ->get();
            foreach($grupoConceptoCalculo as $grupoConcepto){
                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                    $AFC = $AFC + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                }
            }
            
            if($AFC > round($rentaLiquida * $varRetencion[21], -3)){
                $AFC = round($rentaLiquida * $varRetencion[21], -3);
            }




            $deducciones = $interesesVivienda + $medicinaPrepagada + $dependiente + $aporteVoluntario + $AFC;
            $deduccionesSinAportes = $interesesVivienda + $medicinaPrepagada + $dependiente;

            $baseNeta = $rentaLiquida - $deducciones;
            $baseNetaSinAportes = $rentaLiquida - $deduccionesSinAportes;

            $exenta = $baseNeta * $varRetencion[22];
            $exentaSinAportes = $baseNetaSinAportes * $varRetencion[22];

    
            if($exenta > round($uvtActual * $varRetencion[23],-3)){
                $exenta = round($uvtActual * $varRetencion[23], -3);
            }
            if($exentaSinAportes > round($uvtActual * $varRetencion[23],-3)){
                $exentaSinAportes = round($uvtActual * $varRetencion[23], -3);
            }

            $totalBeneficiosTributarios = $exenta + $deducciones;
            $totalBeneficiosTributariosSinAportes = $exentaSinAportes + $deduccionesSinAportes;
            
            

            $topeBeneficios= $rentaLiquida*$varRetencion[24];

            if($totalBeneficiosTributarios > ($rentaLiquida*$varRetencion[24])){
                $totalBeneficiosTributarios = $rentaLiquida*$varRetencion[24];
            }


            if($totalBeneficiosTributarios > round($uvtActual*$varRetencion[25],-3)){
                $totalBeneficiosTributarios = round($uvtActual*$varRetencion[25], -3);
                $topeBeneficios= $rentaLiquida*round($uvtActual*$varRetencion[25], -3);
            }


            if($totalBeneficiosTributariosSinAportes > ($rentaLiquida*$varRetencion[24])){
                $totalBeneficiosTributariosSinAportes = $rentaLiquida*$varRetencion[24];
            }
            if($totalBeneficiosTributariosSinAportes > round($uvtActual*$varRetencion[25],-3)){
                $totalBeneficiosTributariosSinAportes = round($uvtActual*$varRetencion[25], -3);
            }



            

            $baseGravable  = $rentaLiquida - $totalBeneficiosTributarios;
            $baseGravableSinAportes  = $rentaLiquida - $totalBeneficiosTributariosSinAportes;
            
            $baseGravableUVTS = round($baseGravable / $uvtActual, 2);
            $baseGravableSinAportesUVTS = round($baseGravableSinAportes / $uvtActual, 2);
            
            


            $impuestoUVT = 0;
            if($baseGravableUVTS > $varRetencion[26] && $baseGravableUVTS <= $varRetencion[27]){
                $impuestoUVT = ($baseGravableUVTS - $varRetencion[26])*$varRetencion[29];
                $impuestoUVT = $impuestoUVT + $varRetencion[28];
            }
            else if($baseGravableUVTS > $varRetencion[30] && $baseGravableUVTS <= $varRetencion[31]){
                $impuestoUVT = ($baseGravableUVTS - $varRetencion[30])*$varRetencion[33];
                $impuestoUVT = $impuestoUVT + $varRetencion[32];
            }
            else if($baseGravableUVTS > $varRetencion[34] && $baseGravableUVTS <= $varRetencion[35]){
                $impuestoUVT = ($baseGravableUVTS - $varRetencion[34])*$varRetencion[37];
                $impuestoUVT = $impuestoUVT + $varRetencion[36];
            }
            else if($baseGravableUVTS > $varRetencion[38] && $baseGravableUVTS <= $varRetencion[39]){
                $impuestoUVT = ($baseGravableUVTS - $varRetencion[38])*$varRetencion[41];
                $impuestoUVT = $impuestoUVT + $varRetencion[40];
            }
            else if($baseGravableUVTS > $varRetencion[42] && $baseGravableUVTS <= $varRetencion[43]){
                $impuestoUVT = ($baseGravableUVTS - $varRetencion[42])*$varRetencion[45];
                $impuestoUVT = $impuestoUVT + $varRetencion[44];
            }
            else if($baseGravableUVTS > $varRetencion[46]){
                $impuestoUVT = ($baseGravableUVTS - $varRetencion[46])*$varRetencion[48];
                $impuestoUVT = $impuestoUVT + $varRetencion[47];
            }
            
            $impuestoSinAportesUVT = 0;
            if($baseGravableSinAportesUVTS > $varRetencion[26] && $baseGravableSinAportesUVTS <= $varRetencion[27]){
                $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[26])*$varRetencion[29];
                $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[28];
            }
            else if($baseGravableSinAportesUVTS > $varRetencion[30] && $baseGravableSinAportesUVTS <= $varRetencion[31]){
                $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[30])*$varRetencion[33];
                $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[32];
            }
            else if($baseGravableSinAportesUVTS > $varRetencion[34] && $baseGravableSinAportesUVTS <= $varRetencion[35]){
                $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[34])*$varRetencion[37];
                $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[36];
            }
            else if($baseGravableSinAportesUVTS > $varRetencion[38] && $baseGravableSinAportesUVTS <= $varRetencion[39]){
                $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[38])*$varRetencion[41];
                $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[40];
            }
            else if($baseGravableSinAportesUVTS > $varRetencion[42] && $baseGravableSinAportesUVTS <= $varRetencion[43]){
                $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[42])*$varRetencion[45];
                $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[44];
            }
            else if($baseGravableSinAportesUVTS > $varRetencion[46]){
                $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[46])*$varRetencion[48];
                $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[47];
            }

            $impuestoValor = round($impuestoUVT * $uvtActual, -3);
            $impuestoValorSinAportes = round($impuestoSinAportesUVT * $uvtActual, -3);
            $valorInt = $impuestoValor;
            if($impuestoValor>0){

                if($periodo == 30){

                    if(isset($arrValorxConcepto[36])){
                        $arrValorxConcepto[36] = array(
                            "naturaleza" => "3",
                            "unidad" => "UNIDAD",
                            "cantidad"=> "0",
                            "arrNovedades"=> array(),
                            "valor" => ($arrValorxConcepto[36]['valor'] - ($valorInt)),
                            "tipoGen" => "automaticos"
                        );
                    }
                    else{
                        $arrValorxConcepto[36] = array(
                            "naturaleza" => "3",
                            "unidad" => "UNIDAD",
                            "cantidad"=> "0",
                            "arrNovedades"=> array(),
                            "valor" => $valorInt*-1 ,
                            "tipoGen" => "automaticos"
                        );
                    }


                    
                }
                else{
                    if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                        

                        if(isset($arrValorxConcepto[36])){
                            $arrValorxConcepto[36] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => ($arrValorxConcepto[36]['valor'] - ($valorInt)),
                                "tipoGen" => "automaticos"
                            );
                        }
                        else{
                            $arrValorxConcepto[36] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => $valorInt*-1 ,
                                "tipoGen" => "automaticos"
                            );
                        }
                    }
                    else{
                        $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";
                        
                        $itemReteFuente = DB::table("item_boucher_pago","ibp")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=", $empleado->idempleado)
                        ->where("ln.fkEstado","=","5")//Terminada
                        ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                        ->where("ibp.fkConcepto","=","36")//RETENCION                
                        ->get();

                        if(sizeof($itemReteFuente)> 0){
                            $valorInt = $valorInt - $itemReteFuente[0]->descuento;
                        }
                    
                        if(isset($arrValorxConcepto[36])){
                            $arrValorxConcepto[36] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => ($arrValorxConcepto[36]['valor'] - ($valorInt)),
                                "tipoGen" => "automaticos"
                            );
                        }
                        else{
                            $arrValorxConcepto[36] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => $valorInt*-1 ,
                                "tipoGen" => "automaticos"
                            );
                        }
                        

                    }
                }
                
            }
            $valorSalario = 0;
            $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                ->where("gcc.fkGrupoConcepto", "=", "1")
                ->get();
            foreach($grupoConceptoCalculo as $grupoConcepto){
                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                    $valorSalario = $valorSalario + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                }
            }
        
            if($periodo == 15){
                if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                    $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";
                    $itemsBoucherPago = DB::table("item_boucher_pago","ibp")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=", $empleado->idempleado)
                    ->where("ln.fkEstado","=","5")//Terminada
                    ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                    ->where("gcc.fkGrupoConcepto", "=", "1")
                    ->get();
        
                    foreach($itemsBoucherPago as $itemBoucherPago){
                        $valorSalario = $valorSalario + floatval($itemBoucherPago->pago);
                    }
                }
            }



            
            $retencionContingente = $impuestoValorSinAportes - $impuestoValor;

            $arrayRetencion["salario"] = $valorSalario;
            $arrayRetencion["ingreso"] = $ingreso;
            $arrayRetencion["EPS"] = $EPS*-1;
            $arrayRetencion["AFP"] = $AFP*-1;
            $arrayRetencion["FPS"] = $FPS*-1;
            $arrayRetencion["seguridadSocial"] = $SS;
            $arrayRetencion["interesesVivienda"] = $interesesVivienda;
            $arrayRetencion["medicinaPrepagada"] = $medicinaPrepagada;
            $arrayRetencion["dependiente"] = $dependiente;
            $arrayRetencion["aporteVoluntario"] = $aporteVoluntario;
            $arrayRetencion["AFC"] = $AFC;
            $arrayRetencion["exenta"] = $exenta;
            $arrayRetencion["exentaSinAportes"] = $exentaSinAportes;
            $arrayRetencion["totalBeneficiosTributarios"] = $totalBeneficiosTributarios;
            $arrayRetencion["totalBeneficiosTributariosSinAportes"] = $totalBeneficiosTributariosSinAportes;
            $arrayRetencion["topeBeneficios"] = $topeBeneficios;
            $arrayRetencion["baseGravableUVTS"] = $baseGravableUVTS;
            $arrayRetencion["impuestoUVT"] = $impuestoUVT;
            $arrayRetencion["impuestoSinAportesUVT"] = $impuestoSinAportesUVT;
            $arrayRetencion["impuestoValor"] = $impuestoValor;
            $arrayRetencion["impuestoValorSinAportes"] = $impuestoValorSinAportes;
            $arrayRetencion["retencionContingente"] = $retencionContingente;
        }
        
        

        $liquidacionPrima = 0;
        $liquidacionCesantias = 0;
        $liquidacionIntCesantias = 0;
        $liquidacionVac = 0;
        
        if($empleado->tipoRegimen=="Ley 50"){
            //Calculo provisiones 
            $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
            $periodoPagoMesActual = $periodoPagoSinVac;
            
            
            $salarial = 0;
            $grupoConceptoCalculoPrimaCes = DB::table("grupoconcepto_concepto","gcc")
                ->where("gcc.fkGrupoConcepto", "=", "11")//Salarial para provisiones
                ->get();
            foreach($grupoConceptoCalculoPrimaCes as $grupoConcepto){
                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                    $salarial = $salarial + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                }
            }


            if($periodo == 15){                
                if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                    $liquidacionAnt = DB::table("liquidacionnomina", "ln")
                    ->select("bp.periodoPago","ln.idLiquidacionNomina")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",$fechaInicioMes)
                    ->first();
                    if(isset($liquidacionAnt)){
                        $periodoPagoMesActual = $periodoPagoMesActual + $liquidacionAnt->periodoPago;
                   
                        
                        $itemsBoucherSalarialMesAnt = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("bp.fkLiquidacion","=",$liquidacionAnt->idLiquidacionNomina)                        
                        ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial Prima
                        ->first();
                    
    
                        $salarial = $salarial + $itemsBoucherSalarialMesAnt->suma;
                    }                   
                }
            }


            $salarioMes = 0;
            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                if($conceptoFijoEmpl->fkConcepto=="1"){
                    $salarioMes = $conceptoFijoEmpl->valor; 
                }
            }
       
            $salarioMes = ($salarioMes / 30) * $periodoPagoMesActual;
            

            $anioActual = intval(date("Y",strtotime($fechaInicio)));
            $mesActual = intval(date("m",strtotime($fechaInicio)));
            $salarioPrima = 0;

        

            $basePrima = 0;
            $totalPeriodoPago = 0;            
            $provisionPrimaValor = 0;
            $fechaInicialPrima = "";
            $fechaFinalPrima = "";

            if($mesActual >= 1 && $mesActual <= 6){
                $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.diasTrabajados) as diasTrabajadosPer, sum(bp.salarioPeriodoPago) as salarioPago, min(ln.fechaInicio) as minimaFecha")
                ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","<",$fechaInicioMes)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")
                ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                ->first();
  
                
                if(isset($liquidacionesMesesAnterioresPrima->minimaFecha)){
                    if(strtotime($empleado->fechaIngreso) > strtotime($liquidacionesMesesAnterioresPrima->minimaFecha)){
                        $fechaInicialPrima = $empleado->fechaIngreso;
                    }
                    else{
                        $fechaInicialPrima = $liquidacionesMesesAnterioresPrima->minimaFecha;
                    }                    
                }
                else{
                    $fechaInicialPrima = $empleado->fechaIngreso;
                }                
                $fechaFinalPrima = $fechaFin;
                
                $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);
                $totalPeriodoPagoParaSalario = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->diasTrabajadosPer) ? $liquidacionesMesesAnterioresPrima->diasTrabajadosPer : 0);
                


                $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                $salarioPrima = ($salarioPrima / $totalPeriodoPagoParaSalario)*30;

                
                



                $variablesSalarioMinimo = DB::table("variable")->where("idVariable","=","1")->first();
                if($salarioPrima < (2 * $variablesSalarioMinimo->valor)){
                    $variablesSubTrans = DB::table("variable")->where("idVariable","=","2")->first();
                    $salarioPrima = $salarioPrima + $variablesSubTrans->valor;
                }

                $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.valor) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","<",$fechaInicioMes)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                ->first();

                $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;
                
                
                $basePrima = $salarioPrima + $salarialPrima;
                   
                $liquidacionPrima = ($basePrima / 360) * $totalPeriodoPago;


                
              
                


                $historicoProvisionPrima = DB::table("provision","p")
                ->selectRaw("sum(p.valor) as sumaValor")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","<",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","73")
                ->first();

                
                $provisionPrimaValor = $liquidacionPrima - (isset($historicoProvisionPrima->sumaValor) ? $historicoProvisionPrima->sumaValor : 0);


            }
            else if($mesActual >= 7 && $mesActual <= 12){



                $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago, min(ln.fechaInicio) as minimaFecha")
                ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","<",$fechaInicioMes)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")
                ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                ->first();
                
                if(isset($liquidacionesMesesAnterioresPrima->minimaFecha)){
                    if(strtotime($empleado->fechaIngreso) > strtotime($liquidacionesMesesAnterioresPrima->minimaFecha)){
                        
                        $fechaInicialPrima = $empleado->fechaIngreso;
                    }
                    else{
                        $fechaInicialPrima = $liquidacionesMesesAnterioresPrima->minimaFecha;
                    }                    
                }
                else{
                    $liquidacionesMesesAnterioresPrimaPrimerSem = DB::table("liquidacionnomina", "ln")
                    ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.diasTrabajados) as diasTrabajadosPer, sum(bp.salarioPeriodoPago) as salarioPago, min(ln.fechaInicio) as minimaFecha")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")
                    ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                    ->first();
    
                    
                    if(isset($liquidacionesMesesAnterioresPrimaPrimerSem->minimaFecha)){
                        if(strtotime($empleado->fechaIngreso) > strtotime($liquidacionesMesesAnterioresPrimaPrimerSem->minimaFecha)){
                            $fechaInicialPrima = $empleado->fechaIngreso;
                        }
                        else{
                            $fechaInicialPrima = $liquidacionesMesesAnterioresPrimaPrimerSem->minimaFecha;
                        }                    
                    }
                    else{
                        $fechaInicialPrima = $empleado->fechaIngreso;
                    }      
                    
                }    



                $fechaFinalPrima = $fechaFin;

                $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);
                $totalPeriodoPagoParaSalario = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                
                $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                $salarioPrima = ($salarioPrima / $totalPeriodoPagoParaSalario)*30;

                $variablesSalarioMinimo = DB::table("variable")->where("idVariable","=","1")->first();
                if($salarioPrima < (2 * $variablesSalarioMinimo->valor)){
                    $variablesSubTrans = DB::table("variable")->where("idVariable","=","2")->first();
                    $salarioPrima = $salarioPrima + $variablesSubTrans->valor;
                }

                $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.valor) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","<",$fechaInicioMes)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                ->first();

                $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;


                  
                


                $basePrima = $salarioPrima + $salarialPrima;

                
                $liquidacionPrima = ($basePrima / 360) * $totalPeriodoPago;

                $historicoProvisionPrima = DB::table("provision","p")
                ->selectRaw("sum(p.valor) as sumaValor")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","<",date("m",strtotime($fechaInicio)))
                ->where("p.mes",">","6")
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","73")
                ->first();
                
                
                $provisionPrimaValor = $liquidacionPrima - (isset($historicoProvisionPrima->sumaValor) ? $historicoProvisionPrima->sumaValor : 0);
            }

           

            $provisionPrima = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","73")
                ->get();

            $arrProvisionPrima = array(
                "fkConcepto" => "73",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval($provisionPrimaValor)                 
            );
            if(sizeof($provisionPrima)>0){
                

                DB::table("provision")
                ->where("idProvision","=", $provisionPrima[0]->idProvision)
                ->update($arrProvisionPrima);

            }
            else{
                DB::table("provision")
                ->insert($arrProvisionPrima);
            }
            
            

        
            //Cesantias 
            $nomina = DB::table("nomina")->where("idNomina", "=", $liquidacionNomina->fkNomina)->first();
            
            


            $liquidacionesMesesAnterioresCesantias = DB::table("liquidacionnomina", "ln")
            ->selectRaw("bp.periodoPago as periodPago, bp.salarioPeriodoPago as salarioPago, bp.diasTrabajados as diasTrabajados")
            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","<",$fechaInicioMes)
            ->orderBy("bp.idBoucherPago","desc")
            ->limit("2")
            ->get();
            
            

            
            
            $periodPagoCesantiasMesesAnt = 0;
            $salarioPagoCesantiasMesesAnt = 0;
            

            foreach($liquidacionesMesesAnterioresCesantias as $liquidacionMesAnteriorCesantias){
                $periodPagoCesantiasMesesAnt = $periodPagoCesantiasMesesAnt + $liquidacionMesAnteriorCesantias->diasTrabajados;
                $salarioPagoCesantiasMesesAnt = $salarioPagoCesantiasMesesAnt + $liquidacionMesAnteriorCesantias->salarioPago;
            }
            
            
            
            $totalPeriodoPagoCes = $periodoPagoMesActual + $periodPagoCesantiasMesesAnt;
            $totalPeriodoPagoCesParaSalario = $periodoPagoMesActual + $periodPagoCesantiasMesesAnt;
            
            $salarioCes = $salarioMes + $salarioPagoCesantiasMesesAnt;
            
            
            
            $salarioCes = ($salarioCes / $totalPeriodoPagoCesParaSalario)*30;
            

            
            if($salarioCes != $salarioMes){
                $liquidacionesMesesAnterioresCesantias = DB::table("liquidacionnomina", "ln")
                ->selectRaw("bp.periodoPago as periodPago, bp.salarioPeriodoPago as salarioPago, bp.diasTrabajados as diasTrabajados")
                ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","<",$fechaInicioMes)
                ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")
                ->orderBy("bp.idBoucherPago","desc")
                ->get();
                
                $periodPagoCesantiasMesesAnt = 0;
                $salarioPagoCesantiasMesesAnt = 0;
                
    
                foreach($liquidacionesMesesAnterioresCesantias as $liquidacionMesAnteriorCesantias){
                    $periodPagoCesantiasMesesAnt = $periodPagoCesantiasMesesAnt + $liquidacionMesAnteriorCesantias->diasTrabajados;
                    $salarioPagoCesantiasMesesAnt = $salarioPagoCesantiasMesesAnt + $liquidacionMesAnteriorCesantias->salarioPago;
                }
                
                $totalPeriodoPagoCes = $periodoPagoMesActual + $periodPagoCesantiasMesesAnt;
                
                $salarioCes = $salarioMes + $salarioPagoCesantiasMesesAnt;
                $salarioCes = ($salarioCes / $totalPeriodoPagoCes)*30;
            }
            else{
                $salarioCes = $salarioMes;
            }   

            $variablesSalarioMinimo = DB::table("variable")->where("idVariable","=","1")->first();
            if($salarioCes < (2 * $variablesSalarioMinimo->valor)){
                $variablesSubTrans = DB::table("variable")->where("idVariable","=","2")->first();
                $salarioCes = $salarioCes + $variablesSubTrans->valor;
            }

            $liquidacionesMesesAnterioresCompleta = DB::table("liquidacionnomina", "ln")
            ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago, min(ln.fechaInicio) as minimaFecha")
            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","<",$fechaInicioMes)
            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")        
            ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
            ->first();
            
           
            if(isset($liquidacionesMesesAnterioresCompleta->minimaFecha)){
                if(strtotime($empleado->fechaIngreso) > strtotime($liquidacionesMesesAnterioresCompleta->minimaFecha)){
                    $fechaInicialCes = $empleado->fechaIngreso;
                }
                else{
                    $fechaInicialCes = $liquidacionesMesesAnterioresCompleta->minimaFecha;
                }                    
            }
            else{
                $fechaInicialCes = $empleado->fechaIngreso;
            }       
            
            $fechaFinalCes = $fechaFin;

           
            $totalPeriodoPagoAnioActual = $periodoPagoMesActual + $liquidacionesMesesAnterioresCompleta->periodPago;
            

            $itemsBoucherSalarialMesesAnterioresCes = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.valor) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","<",$fechaInicioMes)
            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
            ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
            ->first();

            $salarialCes = $salarial + $itemsBoucherSalarialMesesAnterioresCes->suma;
            $salarialCes = ($salarialCes / $totalPeriodoPagoAnioActual)*30;

            
            $baseCes = $salarioCes + $salarialCes;
        


            $liquidacionCesantias = ($baseCes / 30)*(($totalPeriodoPagoAnioActual * $nomina->diasCesantias) / 360);

            $historicoProvisionCesantias = DB::table("provision","p")
            ->selectRaw("sum(p.valor) as sumaValor")
            ->where("p.fkEmpleado","=",$empleado->idempleado)
            ->where("p.mes","<",date("m",strtotime($fechaInicio)))
            ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
            ->where("p.fkConcepto","=","71")
            ->first();
            //(isset($historicoProvisionCesantias->sumaValor) ? $historicoProvisionCesantias->sumaValor : 0)
            $provisionCesantiasValor = $liquidacionCesantias - $historicoProvisionCesantias->sumaValor;
            
        
            
            $provisionCesantias = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","71")
                ->get();

            $arrProvisionCesantias = array(
                "fkConcepto" => "71",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval($provisionCesantiasValor)                 
            );
            if(sizeof($provisionCesantias)>0){
                DB::table("provision")
                ->where("idProvision","=", $provisionCesantias[0]->idProvision)
                ->update($arrProvisionCesantias);
            }
            else{
                DB::table("provision")
                ->insert($arrProvisionCesantias);
            }
            //Intereses
            $interesesPorcen = $totalPeriodoPagoAnioActual * 0.12 / 360;

            $liquidacionIntCesantias = $liquidacionCesantias * $interesesPorcen;

            $historicoProvisionIntCesantias = DB::table("provision","p")
            ->selectRaw("sum(p.valor) as sumaValor")
            ->where("p.fkEmpleado","=",$empleado->idempleado)
            ->where("p.mes","<",date("m",strtotime($fechaInicio)))
            ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
            ->where("p.fkConcepto","=","72")
            ->first();

            $provisionIntCesantiasValor = $liquidacionIntCesantias - $historicoProvisionIntCesantias->sumaValor;


            
            
            
            $provisionIntCesantias = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","72")
                ->get();

            $arrProvisionIntCesantias = array(
                "fkConcepto" => "72",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval($provisionIntCesantiasValor)                 
            );
            if(sizeof($provisionIntCesantias)>0){
                DB::table("provision")
                ->where("idProvision","=", $provisionIntCesantias[0]->idProvision)
                ->update($arrProvisionIntCesantias);
            }
            else{
                DB::table("provision")
                ->insert($arrProvisionIntCesantias);
            }

            //Vacaciones
            
            
            
            $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$fechaFin) + 1 ;

            if(isset($novedadesRetiro)){
                if(strtotime($fechaFin) > strtotime($novedadesRetiro->fecha)){
                    $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
                }
            }
            
            


            
            $salarialVac = 0;
            $grupoConceptoCalculoVac = DB::table("grupoconcepto_concepto","gcc")
                ->where("gcc.fkGrupoConcepto", "=", "13")//Salarial para provisiones
                ->get();
            foreach($grupoConceptoCalculoVac as $grupoConcepto){
                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                    $salarialVac = $salarialVac + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                }
            }
            $itemsBoucherSalarialMesAnteriorVac = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.valor) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","=",$fechaInicioMes)
            ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
            ->first();
            if(isset($itemsBoucherSalarialMesAnteriorVac)){
                $salarialVac = $salarialVac + $itemsBoucherSalarialMesAnteriorVac->suma;
            }
            
            

            $diasVac = $periodoPagoVac * 15 / 360;
        
            // $diasVac = $totalPeriodoPagoAnioActual * 15 / 360;


            $itemsBoucherSalarialMesesAnterioresVac = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.valor) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","<",$fechaInicioMes)
            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
            ->first();

            $salarialVac = $salarialVac + $itemsBoucherSalarialMesesAnterioresVac->suma;
            $salarialVac = ($salarialVac / $totalPeriodoPagoAnioActual)*30;
            
            $salarioVac = 0;

            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                    $salarioVac = $conceptoFijoEmpl->valor; 
                }
            }


            
            $baseVac = $salarioVac + $salarialVac;
            
            $liquidacionVac = ($baseVac/30)*$diasVac;
        

            $historicoProvisionVac = DB::table("provision","p")
            ->selectRaw("sum(p.valor) as sumaValor")
            ->where("p.fkEmpleado","=",$empleado->idempleado)
            ->where("p.mes","<",date("m",strtotime($fechaInicio)))
            ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
            ->where("p.fkConcepto","=","74")
            ->first();

            $provisionVacValor = $liquidacionVac - $historicoProvisionVac->sumaValor;
    

        
            $provisionVacaciones = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","74")
                ->get();

            $arrProvisionVacaciones = array(
                "fkConcepto" => "74",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval($provisionVacValor)                 
            );
            if(sizeof($provisionVacaciones)>0){
                DB::table("provision")
                ->where("idProvision","=", $provisionVacaciones[0]->idProvision)
                ->update($arrProvisionVacaciones);
            }
            else{
                DB::table("provision")
                ->insert($arrProvisionVacaciones);
            }

            if($tipoliquidacion == "7"){
                $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                $periodoPagoMesActual = $periodoPago;
             
                $salarial = 0;
                $grupoConceptoCalculoPrimaCes = DB::table("grupoconcepto_concepto","gcc")
                    ->where("gcc.fkGrupoConcepto", "=", "11")//Salarial para provisiones
                    ->get();
                foreach($grupoConceptoCalculoPrimaCes as $grupoConcepto){
                    if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                        $salarial = $salarial + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                    }
                }


                if($periodo == 15){                
                    if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                        $liquidacionAnt = DB::table("liquidacionnomina", "ln")
                        ->select("bp.periodoPago","ln.idLiquidacionNomina")
                        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","=",$fechaInicioMes)
                        ->first();

                       
                        if(isset($liquidacionAnt)){
                            $periodoPagoMesActual = $periodoPagoMesActual + $liquidacionAnt->periodoPago;
                            $itemsBoucherSalarialMesAnt = DB::table("item_boucher_pago", "ibp")
                            ->selectRaw("Sum(ibp.valor) as suma")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("bp.fkLiquidacion","=",$liquidacionAnt->idLiquidacionNomina)                        
                            ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                            ->first();
        
                            $salarial = $salarial + $itemsBoucherSalarialMesAnt->suma;
                        }                   
                    }
                }
               

                $salarioMes = 0;
                $conceptosFijosEmpl = DB::table("conceptofijo", "cf")
                ->select(["cf.valor","cf.fechaInicio","cf.fechaFin", "cf.fkConcepto","cf.unidad", "c.*"])
                ->join("concepto AS c", "cf.fkConcepto","=","c.idconcepto")
                ->where("cf.fkEmpleado", "=", $empleado->idempleado)  
                ->where("cf.fkEstado", "=", "1")
                ->get();
                
                foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                    if($conceptoFijoEmpl->fkConcepto=="1"){
                        $salarioMes = $conceptoFijoEmpl->valor; 
                    }
                }
        
                $salarioMesSinCambios = $salarioMes;
                $salarioMes = ($salarioMes / 30) * $periodoPagoMesActual;
 

            

                $anioActual = intval(date("Y",strtotime($fechaInicio)));
                
                $mesActual = intval(date("m",strtotime($fechaInicio)));
                $mesProyeccion = intval(date("m",strtotime($liquidacionNomina->fechaPrima)));


                $salarioPrima = 0;

            

                $basePrima = 0;
                $totalPeriodoPago = 0;
                
                $provisionPrimaValor = 0;


                if($mesProyeccion >= 1 && $mesProyeccion <= 6){
                    
                    $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                    ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")
                    ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                    ->first();
                    
                    $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                    $diasFaltantes = 0;
                    if($periodo == 15){                
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                            $diasFaltantes = $diasFaltantes + 15;
                        }
                    }
                    $mesesFaltanes = $mesProyeccion - $mesActual;
                    
                    $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);

                    
                    $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;
                    
                    
                    $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                    $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);

                   
                    
                    $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;
                    
                    $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                    ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                    ->first();

                    $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                    $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;
                    
                    $basePrima = $salarioPrima + $salarialPrima;
                    $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;

                    
                    $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")              
                    ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                    ->first();

                    if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                        $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                        if($liquidacionAnticipoPrima < 0){
                            $liquidacionAnticipoPrima = 0;
                        }
                    }

                }
                else if($mesProyeccion >= 7 && $mesProyeccion <= 12){
                    $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                    ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                    ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")
                    ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                    ->first();
                    
                    $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);
                    
                   
                    $diasFaltantes = 0;
                    if($periodo == 15){                
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                            $diasFaltantes = $diasFaltantes + 15;
                        }
                    }
                    $mesesFaltanes = $mesProyeccion - $mesActual;
                    $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);
                    
                    $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;
                    $salarioPrima = $salarioMesSinCambios + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                    $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);
                    $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;
                    if($salarioPrima < (2 * $variablesSalarioMinimo->valor)){
                        $variablesSubTrans = DB::table("variable")->where("idVariable","=","2")->first();
                        $salarioPrima = $salarioPrima + $variablesSubTrans->valor;
                    }


                    $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                    ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                    ->first();

                    $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                    $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;

              
                    
                    $basePrima = $salarioPrima + $salarialPrima;
                    
                    if(strtotime($empleado->fechaIngreso)< strtotime($anioActual."-07-01")){
                        $totalPeriodoPago = $this->days_360($anioActual."-07-01", $liquidacionNomina->fechaPrima);
                    }
                    else{
                        $totalPeriodoPago = $this->days_360($empleado->fechaIngreso, $liquidacionNomina->fechaPrima);
                    }

                    $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;




                    $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                    ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                    ->first();

                    if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                        $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                        if($liquidacionAnticipoPrima < 0){
                            $liquidacionAnticipoPrima = 0;
                        }
                    }
                }
                
                if( $liquidacionAnticipoPrima > 0){
                 
                        $arrValorxConcepto[58] = array(
                            "naturaleza" => "1",
                            "unidad" => "DIAS",
                            "cantidad"=> $totalPeriodoPago,
                            "arrNovedades"=> array(),
                            "valor" => $liquidacionAnticipoPrima,
                            "tipoGen" => "automaticos",
                            "base" => $basePrima,
                            "fechaInicio" => $fechaInicialPrima,
                            "fechaFin" => $liquidacionNomina->fechaPrima
                        );
                                       
                }
            }


            if($tipoliquidacion == "5"){ //Normal + Prima
                
                $arrValorxConcepto[58] = array(
                    "naturaleza" => "1",
                    "unidad" => "DIAS",
                    "cantidad"=> $totalPeriodoPago,
                    "arrNovedades"=> array(),
                    "valor" => $liquidacionPrima,
                    "tipoGen" => "automaticos",
                    "base" => $basePrima,
                    "fechaInicio" => $fechaInicialPrima,
                    "fechaFin" => $fechaFinalPrima
                    
                );
                $mesActual = intval(date("m",strtotime($fechaInicio)));
                if($mesActual >= 1 && $mesActual <= 6){
                    $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")              
                    ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                    ->first();
                    if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                        $arrValorxConcepto[78] = array(
                            "naturaleza" => "3",
                            "unidad" => "UNIDAD",
                            "cantidad"=> "0",
                            "arrNovedades"=> array(),
                            "valor" => ($itemsBoucherAnticipoNominaMesesAnteriores->suma * -1),
                            "tipoGen" => "automaticos"
                        );
                    }
                }
                else if($mesActual >= 7 && $mesActual <= 12){
                    $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                    ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                    ->first();
                    if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                        $arrValorxConcepto[78] = array(
                            "naturaleza" => "3",
                            "unidad" => "UNIDAD",
                            "cantidad"=> "0",
                            "arrNovedades"=> array(),
                            "valor" => ($itemsBoucherAnticipoNominaMesesAnteriores->suma * -1),
                            "tipoGen" => "automaticos"
                        );
                    }                        
                }      



                $itemsBoucherMismoPeriodoNomin = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("ibp.*")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","=",$fechaInicio)
                ->where("ln.fechaFin","=",$fechaFin)
                ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                ->get();

                foreach($itemsBoucherMismoPeriodoNomin as $itemBoucherMismoPeriodoNomin){                        
                    if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                        $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                        $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                    }
                }


                if($periodo == 15){                
                    if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                        $itemsBoucherPeriodo16 = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("ibp.*")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","=",date("Y-m-16",strtotime($fechaInicio)))
                        ->where("ln.fechaFin","=",date("Y-m-t",strtotime($fechaInicio)))
                        ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                        ->get();
                        
                        foreach($itemsBoucherPeriodo16 as $itemBoucherMismoPeriodoNomin){                        
                            if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                                $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                                $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                            }
                        }
        
                        
                    }
                   /* //Verificar quincena mes anterior
                    $itemsBoucherPeriodoMesAnt16 = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",date("Y-m-16",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.fechaFin","=",date("Y-m-t",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->whereIn("ln.fkTipoLiquidacion",["3","7"])
                    ->get();
                    
                    foreach($itemsBoucherPeriodoMesAnt16 as $itemBoucherMismoPeriodoNomin){                        
                        if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                        }
                    }
                    //Verificar quincena mes anterior
                    $itemsBoucherPeriodoMesAnt01 = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",date("Y-m-01",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.fechaFin","=",date("Y-m-15",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->whereIn("ln.fkTipoLiquidacion",["3","7"])
                    ->get();

                    foreach($itemsBoucherPeriodoMesAnt01 as $itemBoucherMismoPeriodoNomin){                        
                        if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                        }
                    }
                    
                        */
                    
                }
                else{
                    //Verificar mes anterior
                    $itemsBoucherPeriodoMesAnt = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",date("Y-m-01",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.fechaFin","=",date("Y-m-t",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->whereIn("ln.fkTipoLiquidacion",["3","7"])
                    ->get();

                    foreach($itemsBoucherPeriodoMesAnt as $itemBoucherMismoPeriodoNomin){                        
                        if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                        }
                    }
                }
                



                
            }

            if($tipoliquidacion == "6"){
                if(isset($liquidacionNomina->fechaPrima) && $liquidacionNomina->tipoliquidacionPrima=="1"){

                    $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                    $periodoPagoMesActual = $periodoPago;
                    
                    $salarial = 0;
                    $grupoConceptoCalculoPrimaCes = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "11")//Salarial para provisiones
                        ->get();
                    foreach($grupoConceptoCalculoPrimaCes as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $salarial = $salarial + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }


                    if($periodo == 15){                
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                            $liquidacionAnt = DB::table("liquidacionnomina", "ln")
                            ->select("bp.periodoPago","ln.idLiquidacionNomina")
                            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","=",$fechaInicioMes)
                            ->first();
                            if(isset($liquidacionAnt)){
                                $periodoPagoMesActual = $periodoPagoMesActual + $liquidacionAnt->periodoPago;
                                $itemsBoucherSalarialMesAnt = DB::table("item_boucher_pago", "ibp")
                                ->selectRaw("Sum(ibp.valor) as suma")
                                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                                ->where("bp.fkLiquidacion","=",$liquidacionAnt->idLiquidacionNomina)                        
                                ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                                ->first();
            
                                $salarial = $salarial + $itemsBoucherSalarialMesAnt->suma;
                            }                   
                        }
                    }


                    $salarioMes = 0;
                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1"){
                            $salarioMes = $conceptoFijoEmpl->valor; 
                        }
                    }
                    $salarioMesSinCambios = $salarioMes;
                    $salarioMes = ($salarioMes / 30) * $periodoPagoMesActual;


                

                    $anioActual = intval(date("Y",strtotime($fechaInicio)));
                    
                    $mesActual = intval(date("m",strtotime($fechaInicio)));
                    $mesProyeccion = intval(date("m",strtotime($liquidacionNomina->fechaPrima)));


                    $salarioPrima = 0;

                

                    $basePrima = 0;
                    $totalPeriodoPago = 0;
                    
                    $provisionPrimaValor = 0;


                    if($mesProyeccion >= 1 && $mesProyeccion <= 6){
                        
                        $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                        ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")
                        ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                        ->first();
                        
                        $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                        $diasFaltantes = 0;
                        if($periodo == 15){                
                            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                                $diasFaltantes = $diasFaltantes + 15;
                            }
                        }
                        $mesesFaltanes = $mesProyeccion - $mesActual;
                        $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);

                        
                        $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;
                        
                        $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                        $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);


                        
                        $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;
                    
                        $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                        ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                        ->first();

                        $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                        $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;
                        

                        $basePrima = $salarioPrima + $salarialPrima;
                        
                        $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;
                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")              
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();

                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                            $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                            if($liquidacionAnticipoPrima < 0){
                                $liquidacionAnticipoPrima = 0;
                            }
                        }

                    }
                    else if($mesProyeccion >= 7 && $mesProyeccion <= 12){
                        $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                        ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")
                        ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                        ->first();
                        
                        $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                        $diasFaltantes = 0;
                        if($periodo == 15){                
                            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                                $diasFaltantes = $diasFaltantes + 15;
                            }
                        }
                        $mesesFaltanes = $mesProyeccion - $mesActual;
                        $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);
                        
                        $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;

                        $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                        $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);

                        $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;

                        $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                        ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                        ->first();

                        $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                        $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;




                        $basePrima = $salarioPrima + $salarialPrima;

                        $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;
                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();

                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                            $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                            if($liquidacionAnticipoPrima < 0){
                                $liquidacionAnticipoPrima = 0;
                            }
                        }
                    }
                    
                    if( $liquidacionAnticipoPrima > 0){
                        if(!isset($arrValorxConcepto[78])){
                            $arrValorxConcepto[78] = array(
                                "naturaleza" => "1",
                                "unidad" => "DIAS",
                                "cantidad"=> $totalPeriodoPago,
                                "arrNovedades"=> array(),
                                "valor" => $liquidacionAnticipoPrima,
                                "tipoGen" => "automaticos"
                            );
                        }                        
                    }
                    

                }
                else if(isset($liquidacionNomina->porcentajePrima) && $liquidacionNomina->tipoliquidacionPrima == "2"){
                    $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                    $periodoPagoMesActual = $periodoPago;
                    
                    $salarial = 0;
                    $grupoConceptoCalculoPrimaCes = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "11")//Salarial para provisiones
                        ->get();
                    foreach($grupoConceptoCalculoPrimaCes as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $salarial = $salarial + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }


                    if($periodo == 15){                
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                            $liquidacionAnt = DB::table("liquidacionnomina", "ln")
                            ->select("bp.periodoPago","ln.idLiquidacionNomina")
                            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","=",$fechaInicioMes)
                            ->first();
                            if(isset($liquidacionAnt)){
                                $periodoPagoMesActual = $periodoPagoMesActual + $liquidacionAnt->periodoPago;
                                $itemsBoucherSalarialMesAnt = DB::table("item_boucher_pago", "ibp")
                                ->selectRaw("Sum(ibp.valor) as suma")
                                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                                ->where("bp.fkLiquidacion","=",$liquidacionAnt->idLiquidacionNomina)                        
                                ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                                ->first();
            
                                $salarial = $salarial + $itemsBoucherSalarialMesAnt->suma;
                            }                   
                        }
                    }


                    $salarioMes = 0;
                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1"){
                            $salarioMes = $conceptoFijoEmpl->valor; 
                        }
                    }
                    $salarioMesSinCambios = $salarioMes;
                    $salarioMes = ($salarioMes / 30) * $periodoPagoMesActual;


                

                    $anioActual = intval(date("Y",strtotime($fechaInicio)));
                    
                    $mesActual = intval(date("m",strtotime($fechaInicio)));
                    

                    $salarioPrima = 0;

                

                    $basePrima = 0;
                    $totalPeriodoPago = 0;
                    
                    $provisionPrimaValor = 0;


                    if($mesActual >= 1 && $mesActual <= 6){
                        $mesProyeccion = 6;
                        $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                        ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")
                        ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                        ->first();
                        
                        $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                        $diasFaltantes = 0;
                        if($periodo == 15){                
                            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                                $diasFaltantes = $diasFaltantes + 15;
                            }
                        }
                        $mesesFaltanes = $mesProyeccion - $mesActual;
                        $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);

                        
                        $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;
                        
                        $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                        $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);


                        
                        $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;
                    
                        $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                        ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                        ->first();

                        $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                        $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;
                        

                        $basePrima = $salarioPrima + $salarialPrima;
                        
                        $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;

                        $liquidacionAnticipoPrima = $liquidacionAnticipoPrima * ($liquidacionNomina->porcentajePrima / 100);

                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")              
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();

                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                            $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                            if($liquidacionAnticipoPrima < 0){
                                $liquidacionAnticipoPrima = 0;
                            }
                        }

                    }
                    else if($mesActual >= 7 && $mesActual <= 12){
                        $mesProyeccion = 12;
                        
                        $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                        ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")
                        ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                        ->first();
                        
                        $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                        $diasFaltantes = 0;
                        if($periodo == 15){                
                            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                                $diasFaltantes = $diasFaltantes + 15;
                            }
                        }
                        $mesesFaltanes = $mesProyeccion - $mesActual;
                        $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);
                        
                        $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;

                        $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                        $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);

                        $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;

                        $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                        ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                        ->first();

                        $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                        $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;




                        $basePrima = $salarioPrima + $salarialPrima;

                        $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;


                        $liquidacionAnticipoPrima = $liquidacionAnticipoPrima * ($liquidacionNomina->porcentajePrima / 100);


                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();

                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                            $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                            if($liquidacionAnticipoPrima < 0){
                                $liquidacionAnticipoPrima = 0;
                            }
                        }
                    }
                    if( $liquidacionAnticipoPrima > 0){
                        if(!isset($arrValorxConcepto[78])){
                            $arrValorxConcepto[78] = array(
                                "naturaleza" => "1",
                                "unidad" => "PORCENTAJE",
                                "cantidad"=> $liquidacionNomina->porcentajePrima,
                                "arrNovedades"=> array(),
                                "valor" => $liquidacionAnticipoPrima,
                                "tipoGen" => "automaticos"
                            );
                        }
                    }
                    


                    


                }
                else if(isset($liquidacionNomina->valorFijoPrima) && $liquidacionNomina->tipoliquidacionPrima=="3"){
                    $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
                    $periodoPagoMesActual = $periodoPago;
                    
                    $salarial = 0;
                    $grupoConceptoCalculoPrimaCes = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "11")//Salarial para provisiones
                        ->get();
                    foreach($grupoConceptoCalculoPrimaCes as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $salarial = $salarial + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }


                    if($periodo == 15){                
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                            $liquidacionAnt = DB::table("liquidacionnomina", "ln")
                            ->select("bp.periodoPago","ln.idLiquidacionNomina")
                            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                            ->where("bp.fkEmpleado","=",$empleado->idempleado)
                            ->where("ln.fechaInicio","=",$fechaInicioMes)
                            ->first();
                            if(isset($liquidacionAnt)){
                                $periodoPagoMesActual = $periodoPagoMesActual + $liquidacionAnt->periodoPago;
                                $itemsBoucherSalarialMesAnt = DB::table("item_boucher_pago", "ibp")
                                ->selectRaw("Sum(ibp.valor) as suma")
                                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                                ->where("bp.fkLiquidacion","=",$liquidacionAnt->idLiquidacionNomina)                        
                                ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                                ->first();
            
                                $salarial = $salarial + $itemsBoucherSalarialMesAnt->suma;
                            }                   
                        }
                    }


                    $salarioMes = 0;
                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1"){
                            $salarioMes = $conceptoFijoEmpl->valor; 
                        }
                    }
                    $salarioMesSinCambios = $salarioMes;
                    $salarioMes = ($salarioMes / 30) * $periodoPagoMesActual;


                

                    $anioActual = intval(date("Y",strtotime($fechaInicio)));
                    
                    $mesActual = intval(date("m",strtotime($fechaInicio)));
                    

                    $salarioPrima = 0;

                

                    $basePrima = 0;
                    $totalPeriodoPago = 0;
                    
                    $provisionPrimaValor = 0;


                    if($mesActual >= 1 && $mesActual <= 6){
                        $mesProyeccion = 6;
                        $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                        ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")
                        ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                        ->first();
                        
                        $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                        $diasFaltantes = 0;
                        if($periodo == 15){                
                            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                                $diasFaltantes = $diasFaltantes + 15;
                            }
                        }
                        $mesesFaltanes = $mesProyeccion - $mesActual;
                        $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);

                        
                        $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;
                        
                        $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                        $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);


                        
                        $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;
                    
                        $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                        ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                        ->first();

                        $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                        $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;
                        

                        $basePrima = $salarioPrima + $salarialPrima;
                        
                        $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;

                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")              
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();

                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                            $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                            if($liquidacionAnticipoPrima < 0){
                                $liquidacionAnticipoPrima = 0;
                            }
                        }
                        if($liquidacionNomina->valorFijoPrima < $liquidacionAnticipoPrima){
                            $liquidacionAnticipoPrima = $liquidacionNomina->valorFijoPrima;
                        }


                    }
                    else if($mesActual >= 7 && $mesActual <= 12){
                        $mesProyeccion = 12;
                        
                        $liquidacionesMesesAnterioresPrima = DB::table("liquidacionnomina", "ln")
                        ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
                        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")
                        ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])
                        ->first();
                        
                        $totalPeriodoPago = $periodoPagoMesActual + (isset($liquidacionesMesesAnterioresPrima->periodPago) ? $liquidacionesMesesAnterioresPrima->periodPago : 0);

                        $diasFaltantes = 0;
                        if($periodo == 15){                
                            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                                $diasFaltantes = $diasFaltantes + 15;
                            }
                        }
                        $mesesFaltanes = $mesProyeccion - $mesActual;
                        $diasFaltantes = $diasFaltantes + ($mesesFaltanes * 30);
                        
                        $totalPeriodoPago = $totalPeriodoPago + $diasFaltantes;

                        $salarioPrima = $salarioMes + (isset($liquidacionesMesesAnterioresPrima->salarioPago) ? $liquidacionesMesesAnterioresPrima->salarioPago : 0);
                        $salarioPrima = $salarioPrima + ($mesesFaltanes * $salarioMesSinCambios);

                        $salarioPrima = ($salarioPrima / $totalPeriodoPago)*30;

                        $itemsBoucherSalarialMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                        ->where("gcc.fkGrupoConcepto","=","11") //11 - Salarial
                        ->first();

                        $salarialPrima = $salarial + $itemsBoucherSalarialMesesAnteriores->suma;
                        $salarialPrima = ($salarialPrima / $totalPeriodoPago)*30;




                        $basePrima = $salarioPrima + $salarialPrima;

                        $liquidacionAnticipoPrima = ($basePrima / 360) * $totalPeriodoPago;


                    


                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();

                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores)){
                            $liquidacionAnticipoPrima = $liquidacionAnticipoPrima - $itemsBoucherAnticipoNominaMesesAnteriores->suma;
                            if($liquidacionAnticipoPrima < 0){
                                $liquidacionAnticipoPrima = 0;
                            }
                        }
                        if($liquidacionNomina->valorFijoPrima < $liquidacionAnticipoPrima){
                            $liquidacionAnticipoPrima = $liquidacionNomina->valorFijoPrima;
                        }
                    }
                    if( $liquidacionAnticipoPrima > 0){
                        if(!isset($arrValorxConcepto[78])){
                            $arrValorxConcepto[78] = array(
                                "naturaleza" => "1",
                                "unidad" => "VALOR",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => $liquidacionAnticipoPrima,
                                "tipoGen" => "automaticos"
                            );
                        }
                    }
                    
                }
            }            
    
        }
        else{
            //Vacaciones
            $fechaInicioMes = date("Y-m-01", strtotime($fechaInicio));
            $anioActual = intval(date("Y",strtotime($fechaInicio)));
            $mesActual = intval(date("m",strtotime($fechaInicio)));
            
            $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$fechaFin) + 1 ;

            if(isset($novedadesRetiro)){
                if(strtotime($fechaFin) > strtotime($novedadesRetiro->fecha)){
                    $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
                }
            }
            
            


            
            $salarialVac = 0;
            $grupoConceptoCalculoVac = DB::table("grupoconcepto_concepto","gcc")
                ->where("gcc.fkGrupoConcepto", "=", "13")//Salarial para provisiones
                ->get();
            foreach($grupoConceptoCalculoVac as $grupoConcepto){
                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                    $salarialVac = $salarialVac + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                }
            }
            $itemsBoucherSalarialVac = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.valor) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","=",$fechaInicioMes)
            ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
            ->first();
            if(isset($itemsBoucherSalarialVac)){
                $salarialVac = $salarialVac + $itemsBoucherSalarialVac->suma;
            }
            
            

            $diasVac = $periodoPagoVac * 15 / 360;
        
            // $diasVac = $totalPeriodoPagoAnioActual * 15 / 360;

            $liquidacionesMesesAnterioresCompleta = DB::table("liquidacionnomina", "ln")
            ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","<=",$fechaInicioMes)
            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")       
            ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])         
            ->first();
            $primeraLiquidacion = DB::table("liquidacionnomina", "ln")
            ->selectRaw("min(ln.fechaInicio) as primeraFecha")
            ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)->first();

            $minimaFecha = date("Y-m-d");
            
            if(isset($primeraLiquidacion)){
                $minimaFecha = $primeraLiquidacion->primeraFecha;
            }
            $diasAgregar = 0;
            //Verificar si dicha nomina es menor a la fecha de ingreso
            if(strtotime($empleado->fechaIngreso) < strtotime($minimaFecha)){
                $diasAgregar = $this->days_360($empleado->fechaIngreso, $minimaFecha);
            }
            if(isset($vacacionesPTotal->fechaInicio)){
                $periodoNuevo = $this->days_360($fechaInicio,$vacacionesPTotal->fechaInicio);
            }
            else{
                $periodoNuevo = $this->days_360($fechaInicio,$fechaFin);
            }
                

            $periodoPagoMesActual = $periodoNuevo + $diasAgregar;
            $totalPeriodoPagoAnioActual = $periodoPagoMesActual + $liquidacionesMesesAnterioresCompleta->periodPago;

            
            $itemsBoucherSalarialMesesAnterioresVac = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.valor) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->where("ln.fechaInicio","<",$fechaInicioMes)
            ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
            ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
            ->first();

            $salarialVac = $salarialVac + $itemsBoucherSalarialMesesAnterioresVac->suma;
            $salarialVac = ($salarialVac / $totalPeriodoPagoAnioActual)*30;
            
            $salarioVac = 0;

            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                    $salarioVac = $conceptoFijoEmpl->valor; 
                }
            }


            
            $baseVac = $salarioVac + $salarialVac;
            
            $liquidacionVac = ($baseVac/30)*$diasVac;
        

            $historicoProvisionVac = DB::table("provision","p")
            ->selectRaw("sum(p.valor) as sumaValor")
            ->where("p.fkEmpleado","=",$empleado->idempleado)
            ->where("p.mes","<",date("m",strtotime($fechaInicio)))
            ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
            ->where("p.fkConcepto","=","74")
            ->first();

            $provisionVacValor = $liquidacionVac - $historicoProvisionVac->sumaValor;
    

        
            $provisionVacaciones = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","74")
                ->get();

            $arrProvisionVacaciones = array(
                "fkConcepto" => "74",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval($provisionVacValor)                 
            );
            if(sizeof($provisionVacaciones)>0){
                DB::table("provision")
                ->where("idProvision","=", $provisionVacaciones[0]->idProvision)
                ->update($arrProvisionVacaciones);
            }
            else{
                DB::table("provision")
                ->insert($arrProvisionVacaciones);
            }


            //Provisiones en 0
            $provisionPrima = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","73")
                ->get();

            $arrProvisionPrima = array(
                "fkConcepto" => "73",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval(0)                 
            );
            if(sizeof($provisionPrima)>0){
                

                DB::table("provision")
                ->where("idProvision","=", $provisionPrima[0]->idProvision)
                ->update($arrProvisionPrima);

            }
            else{
                DB::table("provision")
                ->insert($arrProvisionPrima);
            }



            
            $provisionCesantias = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","71")
                ->get();

            $arrProvisionCesantias = array(
                "fkConcepto" => "71",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval(0)                 
            );
            if(sizeof($provisionCesantias)>0){
                DB::table("provision")
                ->where("idProvision","=", $provisionCesantias[0]->idProvision)
                ->update($arrProvisionCesantias);
            }
            else{
                DB::table("provision")
                ->insert($arrProvisionCesantias);
            }



            $provisionIntCesantias = DB::table("provision","p")
                ->where("p.fkEmpleado","=",$empleado->idempleado)
                ->where("p.mes","=",date("m",strtotime($fechaInicio)))
                ->where("p.anio","=",date("Y",strtotime($fechaInicio)))
                ->where("p.fkConcepto","=","72")
                ->get();

            $arrProvisionIntCesantias = array(
                "fkConcepto" => "72",
                "fkEmpleado"=> $empleado->idempleado,
                "mes" => date("m",strtotime($fechaInicio)),
                "anio"  => date("Y",strtotime($fechaInicio)),
                "valor" => intval(0)                 
            );
            if(sizeof($provisionIntCesantias)>0){
                DB::table("provision")
                ->where("idProvision","=", $provisionIntCesantias[0]->idProvision)
                ->update($arrProvisionIntCesantias);
            }
            else{
                DB::table("provision")
                ->insert($arrProvisionIntCesantias);
            }


        }


        if($tipoliquidacion == "2" || $tipoliquidacion == "3"){
            //Calcular retiro dias
            $novedadesRetiro = DB::table("novedad","n")
            ->join("retiro AS r", "r.idRetiro","=","n.fkRetiro")
            ->where("n.fkEmpleado", "=", $empleado->idempleado)
            ->where("n.fkEstado","=","7")
            ->whereNotNull("n.fkRetiro")
            ->whereBetween("n.fechaRegistro",[$fechaInicio, $fechaFin])->first();

            if(isset($novedadesRetiro)){

                //Verificar si tien un pago para el mismo periodoPago
                
                



                
                if($liquidacionPrima!=0){
                    $fechaFinalPrima = $novedadesRetiro->fecha;

                    $arrValorxConcepto[58] = array(
                        "naturaleza" => "1",
                        "unidad" => "DIAS",
                        "cantidad"=> $totalPeriodoPago,
                        "arrNovedades"=> array([
                            "idNovedad" => $novedadesRetiro->idNovedad,
                            "valor" => $liquidacionPrima
                        ]),
                        "valor" => $liquidacionPrima,
                        "tipoGen" => "automaticos",
                        "base" => $basePrima,
                        "fechaInicio" => $fechaInicialPrima,
                        "fechaFin" => $fechaFinalPrima
                    );

                    $mesActual = intval(date("m",strtotime($fechaInicio)));
                    if($mesActual >= 1 && $mesActual <= 6 && $empleado->tipoRegimen!="Salario Integral"){
                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")              
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();
                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores) && $itemsBoucherAnticipoNominaMesesAnteriores->suma > 0){
                            
                            $arrValorxConcepto[78] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => ($itemsBoucherAnticipoNominaMesesAnteriores->suma * -1),
                                "tipoGen" => "automaticos"
                            );
                        }
                    }
                    else if($mesActual >= 7 && $mesActual <= 12 && $empleado->tipoRegimen!="Salario Integral"){
                        $itemsBoucherAnticipoNominaMesesAnteriores = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("Sum(ibp.valor) as suma")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","<",$fechaInicioMes)
                        ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."' and MONTH(ln.fechaInicio) > 6")                
                        ->where("ibp.fkConcepto","=","78") //78 - Anticipo prima
                        ->first();
                        if(isset($itemsBoucherAnticipoNominaMesesAnteriores) && $itemsBoucherAnticipoNominaMesesAnteriores->suma > 0){
                            $arrValorxConcepto[78] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => ($itemsBoucherAnticipoNominaMesesAnteriores->suma * -1),
                                "tipoGen" => "automaticos"
                            );
                        }                        
                    }                    
                }
                if($liquidacionCesantias>0){

                    

                    $fechaFinalCes = $novedadesRetiro->fecha;
                    $arrValorxConcepto[66] = array(
                        "naturaleza" => "1",
                        "unidad" => "DIAS",
                        "cantidad"=> $totalPeriodoPagoAnioActual,
                        "arrNovedades"=> array([
                            "idNovedad" => $novedadesRetiro->idNovedad,
                            "valor" => $liquidacionCesantias
                            ]),
                        "valor" => $liquidacionCesantias,
                        "tipoGen" => "automaticos",
                        "base" => $baseCes,
                        "fechaInicio" => $fechaInicialCes,
                        "fechaFin" => $fechaFinalCes
                    );
                }
                if($liquidacionIntCesantias>0){
                    $fechaFinalCes = $novedadesRetiro->fecha;
                    $arrValorxConcepto[69] = array(
                        "naturaleza" => "1",
                        "unidad" => "DIAS",
                        "cantidad"=> $totalPeriodoPagoAnioActual,
                        "arrNovedades"=> array([
                            "idNovedad" => $novedadesRetiro->idNovedad,
                            "valor" => $liquidacionIntCesantias
                        ]),
                        "valor" => $liquidacionIntCesantias,
                        "tipoGen" => "automaticos",
                        "base" => $baseCes,
                        "fechaInicio" => $fechaInicialCes,
                        "fechaFin" => $fechaFinalCes
                    );
                }
                if($empleado->tipoRegimen!="Salario Integral"){
                    $salarialVac = 0;
                    $grupoConceptoCalculoVac = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "13")//Salarial para provisiones
                        ->get();
                    foreach($grupoConceptoCalculoVac as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $salarialVac = $salarialVac + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }

                    $itemsBoucherSalarialMesAnteriorVac = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",$fechaInicioMes)
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                    ->first();
                    if(isset($itemsBoucherSalarialMesAnteriorVac)){
                        $salarialVac = $salarialVac + $itemsBoucherSalarialMesAnteriorVac->suma;
                    }


                    $novedadesVacacion = DB::table("novedad","n")
                    ->join("vacaciones as v","v.idVacaciones","=","n.fkVacaciones")
                    ->where("n.fkEmpleado","=",$empleado->idempleado)
                    ->whereIn("n.fkEstado",["7", "8","16"]) // Pagada o sin pagar-> no que este eliminada
                    ->whereNotNull("n.fkVacaciones")
                    ->get();
                    //$diasVac = $totalPeriodoPagoAnioActual * 15 / 360;


                    foreach($novedadesVacacion as $novedadVacacion){
                        $diasVac = $diasVac - $novedadVacacion->diasCompletos;
                    }
                    if(isset($diasVac) && $diasVac < 0 && $empresa->vacacionesNegativas == 0){
                        $diasVac = 0;
                    }
                    
                    $itemsBoucherSalarialMesesAnterioresVac = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("Sum(ibp.valor) as suma")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","<",$fechaInicioMes)
                    ->whereRaw("YEAR(ln.fechaInicio) = '".$anioActual."'")                
                    ->where("gcc.fkGrupoConcepto","=","13") //13 - Salarial vacaciones
                    ->first();

                    $salarialVac = $salarialVac + $itemsBoucherSalarialMesesAnterioresVac->suma;
                    $salarialVac = ($salarialVac / $totalPeriodoPagoAnioActual)*30;
                    $salarioVac = 0;

                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                            $salarioVac = $conceptoFijoEmpl->valor; 
                        }
                    }


                    
                    $baseVac = $salarioVac + $salarialVac;
                    
                    


                    $liquidacionVac = ($baseVac/30)*$diasVac;
                    




                    if($liquidacionVac > 0 || $empresa->vacacionesNegativas == 1){
                        $arrValorxConcepto[30] = array(
                            "naturaleza" => "1",
                            "unidad" => "DIAS",
                            "cantidad"=> $diasVac,
                            "arrNovedades"=> array([
                                "idNovedad" => $novedadesRetiro->idNovedad,
                                "valor" => $liquidacionVac
                            ]),
                            "valor" => $liquidacionVac,
                            "tipoGen" => "automaticos",
                            "base" => $baseVac
                        );     
                    }

                }
                if($novedadesRetiro->indemnizacion == 1){
                    $periodoTrab = $this->days_360($empleado->fechaIngreso,$fechaFin);
                    $periodoAnios = ($periodoTrab / 360);
                    $salarioMes = 0;
                    foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                        if($conceptoFijoEmpl->fkConcepto=="1" || $conceptoFijoEmpl->fkConcepto=="2"){
                            $salarioMes = $conceptoFijoEmpl->valor; 
                        }
                    }

                    $variables = DB::table("variable")->where("idVariable","=","1")->first();
                    $valorSalarioMinimo = $variables->valor;
                    $diasIndemnizacion = 0;

                    $codigoIndem = 0;

                    if($salarioMes < (10 * $valorSalarioMinimo)){
                        $diasIndemnizacion = 30;
                        $periodoAnios = $periodoAnios - 1;
                        if($periodoAnios > 0){
                            $diasIndemnizacion = $diasIndemnizacion + (20 * $periodoAnios);
                        }
                        
                        $codigoIndem = 27;
                    }
                    else{
                        $diasIndemnizacion = 20;
                        $periodoAnios = $periodoAnios - 1;

                        if($periodoAnios > 0){
                            $diasIndemnizacion = $diasIndemnizacion + (15 * $periodoAnios);
                        }
                        $codigoIndem = 26;
                    }


                    $valorDia = $salarioMes / 30;
                    $indemnizacion = $valorDia * $diasIndemnizacion;



                    if($indemnizacion > 0){
                        

                        $arrValorxConcepto[$codigoIndem] = array(
                            "naturaleza" => "1",
                            "unidad" => "DIAS",
                            "cantidad"=> $diasIndemnizacion,
                            "arrNovedades"=> array([
                                "idNovedad" => $novedadesRetiro->idNovedad,
                                "valor" => $indemnizacion
                            ]),
                            "valor" => $indemnizacion,
                            "tipoGen" => "automaticos"
                        );     

            
                        $variablesRetInd = DB::table("variable")->whereIn("idVariable",["10","62","63","64","65"])->get();
                        $variablesRetInde = array();
                        foreach($variablesRetInd as $variableRetInd){
                            $variablesRetInde[$variableRetInd->idVariable] = $variableRetInd->valor;
                        }
                        
                        $uvtActual = $variablesRetInde[10];
                        $salarioUvts = $salarioMes/ $uvtActual;
                        if($salarioUvts > $variablesRetInde[62]){
                            $variablesRetencion = DB::table("variable")
                                ->where("idVariable",">=","16")
                                ->where("idVariable","<=","48")
                                ->get();
                            $varRetencion = array();
                            foreach($variablesRetencion as $variablesRetencio){
                                $varRetencion[$variablesRetencio->idVariable] = $variablesRetencio->valor;
                            }
                            $ingreso = $indemnizacion; 
                            $FPS = 0;
                            $EPS = 0;
                            $AFP = 0;
                            $SS = 0;
                            $rentaLiquida = $ingreso - $SS;
                    
                            $interesesVivienda = 0;
                            $medicinaPrepagada = 0;
                            $dependiente = 0;
                            $aporteVoluntario = 0;
                            $AFC = 0;
                                                
                
                
                            $deducciones = $interesesVivienda + $medicinaPrepagada + $dependiente + $aporteVoluntario + $AFC;
                            $deduccionesSinAportes = $interesesVivienda + $medicinaPrepagada + $dependiente;
                
                            $baseNeta = $rentaLiquida - $deducciones;
                            $baseNetaSinAportes = $rentaLiquida - $deduccionesSinAportes;
                
                            $exenta = $baseNeta * $variablesRetInde[63];


                            $exentaSinAportes = $baseNetaSinAportes * $variablesRetInde[63];
                
                    
                            $totalBeneficiosTributarios = $exenta + $deducciones;
                            $totalBeneficiosTributariosSinAportes = $exentaSinAportes + $deduccionesSinAportes;
                            
                            
                
                            $topeBeneficios= $rentaLiquida*$varRetencion[24];
                
                            if($totalBeneficiosTributarios > ($rentaLiquida*$varRetencion[24])){
                                $totalBeneficiosTributarios = $rentaLiquida*$varRetencion[24];
                            }
                
                
                            if($totalBeneficiosTributarios > round($uvtActual*$variablesRetInde[64],-3)){

                                $totalBeneficiosTributarios = round($uvtActual*$variablesRetInde[64], -3);
                                $topeBeneficios= $rentaLiquida*round($uvtActual*$variablesRetInde[64], -3);
                            }
                
                
                            if($totalBeneficiosTributariosSinAportes > ($rentaLiquida*$varRetencion[24])){
                                $totalBeneficiosTributariosSinAportes = $rentaLiquida*$varRetencion[24];
                            }
                            if($totalBeneficiosTributariosSinAportes > round($uvtActual*$variablesRetInde[64],-3)){
                                $totalBeneficiosTributariosSinAportes = round($uvtActual*$variablesRetInde[64], -3);
                            }
                
                
                
                            
                
                            $baseGravable  = $rentaLiquida - $totalBeneficiosTributarios;
                            $baseGravableSinAportes  = $rentaLiquida - $totalBeneficiosTributariosSinAportes;
                            
                            $baseGravableUVTS = round($baseGravable / $uvtActual, 2);
                            $baseGravableSinAportesUVTS = round($baseGravableSinAportes / $uvtActual, 2);
                            
                            $impuestoValor = round($baseGravable * $variablesRetInde[65], -3);
                            $impuestoValorSinAportes = round($baseGravableSinAportes * $variablesRetInde[65], -3);
                
                
                            $impuestoUVT = round($impuestoValor / $uvtActual,2);
                            $impuestoSinAportesUVT = round($impuestoValorSinAportes / $uvtActual, 2);

                            $valorInt = $impuestoValor;
                            if($impuestoValor>0){
                                $arrValorxConcepto[76] = array(
                                    "naturaleza" => "3",
                                    "unidad" => "UNIDAD",
                                    "cantidad"=> "0",
                                    "arrNovedades"=> array(),
                                    "valor" => $valorInt*-1 ,
                                    "tipoGen" => "automaticos"
                                );

                                $arrayRetencionInd["tipoRetencion"] = "INDEMNIZACION";
                                $arrayRetencionInd["salario"] = $salarioMes;
                                $arrayRetencionInd["ingreso"] = $ingreso;
                                $arrayRetencionInd["EPS"] = $EPS*-1;
                                $arrayRetencionInd["AFP"] = $AFP*-1;
                                $arrayRetencionInd["FPS"] = $FPS*-1;
                                $arrayRetencionInd["seguridadSocial"] = $SS;
                                $arrayRetencionInd["interesesVivienda"] = $interesesVivienda;
                                $arrayRetencionInd["medicinaPrepagada"] = $medicinaPrepagada;
                                $arrayRetencionInd["dependiente"] = $dependiente;
                                $arrayRetencionInd["aporteVoluntario"] = $aporteVoluntario;
                                $arrayRetencionInd["AFC"] = $AFC;
                                $arrayRetencionInd["exenta"] = $exenta;
                                $arrayRetencionInd["exentaSinAportes"] = $exentaSinAportes;
                                $arrayRetencionInd["totalBeneficiosTributarios"] = $totalBeneficiosTributarios;
                                $arrayRetencionInd["totalBeneficiosTributariosSinAportes"] = $totalBeneficiosTributariosSinAportes;
                                $arrayRetencionInd["topeBeneficios"] = $topeBeneficios;
                                $arrayRetencionInd["baseGravableUVTS"] = $baseGravableUVTS;
                                $arrayRetencionInd["impuestoUVT"] = $impuestoUVT;
                                $arrayRetencionInd["impuestoSinAportesUVT"] = $impuestoSinAportesUVT;
                                $arrayRetencionInd["impuestoValor"] = $impuestoValor;
                                $arrayRetencionInd["impuestoValorSinAportes"] = $impuestoValorSinAportes;
                                $arrayRetencionInd["retencionContingente"] = 0;
                            }
                            
                        }


                    }




                }

                //Calculo retencion retiro
                if( $tipoliquidacion == "3"){
                    $ingreso = 0;
                    $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "9")
                        ->get();


                    foreach($grupoConceptoCalculo as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $ingreso = $ingreso + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }
                    $variablesRetencion = DB::table("variable")
                        ->where("idVariable",">=","16")
                        ->where("idVariable","<=","48")
                        ->get();
                    $varRetencion = array();
                    foreach($variablesRetencion as $variablesRetencio){
                        $varRetencion[$variablesRetencio->idVariable] = $variablesRetencio->valor;
                    }
                    $FPS = (isset($arrValorxConcepto[33]) ? $arrValorxConcepto[33]['valor'] : 0);
                    $EPS = (isset($arrValorxConcepto[18]) ? $arrValorxConcepto[18]['valor'] : 0);
                    $AFP = (isset($arrValorxConcepto[19]) ? $arrValorxConcepto[19]['valor'] : 0);
                    $SS = ($FPS + $EPS + $AFP) * -1;
        
        
                    if($periodo == 15){
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                            $itemsBoucherPago = DB::table("item_boucher_pago","ibp")
                            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->where("bp.fkEmpleado","=", $empleado->idempleado)
                            ->where("ln.fkEstado","=","5")//Terminada
                            ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                            ->where("gcc.fkGrupoConcepto", "=", "9")
                            ->get();
            
                            foreach($itemsBoucherPago as $itemBoucherPago){
                                $ingreso = $ingreso + floatval($itemBoucherPago->pago);
                            }
        
                            $itemsBoucherPagoSS = DB::table("item_boucher_pago","ibp")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->where("bp.fkEmpleado","=", $empleado->idempleado)
                            ->where("ln.fkEstado","=","5")//Terminada
                            ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                            ->whereIn("ibp.fkConcepto",["33","18","19"])//SS
                            ->get();
                            foreach($itemsBoucherPagoSS as $itemBoucherPagoSS){
                                $SS = $SS + floatval($itemBoucherPagoSS->descuento);
                                if($itemBoucherPagoSS->fkConcepto == "33"){
                                    $FPS = $FPS + $itemBoucherPagoSS->valor;
                                }
                                else if($itemBoucherPagoSS->fkConcepto == "18"){
                                    $EPS = $EPS + $itemBoucherPagoSS->valor;
                                }
                                else if($itemBoucherPagoSS->fkConcepto == "19"){
                                    $AFP = $AFP + $itemBoucherPagoSS->valor;
                                }
        
                            }
                        }
                    }
                    
        
                    $rentaLiquida = $ingreso - $SS;
        
                    $interesesVivienda = 0;
                    $beneficiosTributarioIntVivienda = DB::table("beneficiotributario", "bt")
                        ->selectRaw("sum(bt.valorMensual) as suma")
                        ->where("bt.fkEmpleado","=",$empleado->idempleado)
                        ->where("bt.fkTipoBeneficio", "=", "2")
                        ->whereDate("bt.fechaVigencia",">=", $fechaFin)
                        ->get();
                    
                    $interesesVivienda = intval($beneficiosTributarioIntVivienda[0]->suma);
        
                    if($interesesVivienda > round($uvtActual * $varRetencion[16], -3)){
                        $interesesVivienda = round($uvtActual * $varRetencion[16], -3);
                    }
                    
                    $medicinaPrepagada = 0;
                    $beneficiosTributarioMedicinaPrepagada = DB::table("beneficiotributario", "bt")
                        ->selectRaw("sum(bt.valorMensual) as suma")
                        ->where("bt.fkEmpleado","=",$empleado->idempleado)
                        ->where("bt.fkTipoBeneficio", "=", "3")
                        ->whereDate("bt.fechaVigencia",">=", $fechaFin)
                        ->get();
                    
                    $medicinaPrepagada = intval($beneficiosTributarioMedicinaPrepagada[0]->suma);
                    
                    if($medicinaPrepagada > round($uvtActual * $varRetencion[17], -3)){
                        $medicinaPrepagada = round($uvtActual * $varRetencion[17], -3);
                    }
        
                    //Calcular cuanto cuesta este dependiente
                    $dependiente = 0;
                    $beneficiosTributarioDependiente = DB::table("beneficiotributario", "bt")
                        ->select("bt.idBeneficioTributario")
                        ->where("bt.fkEmpleado","=",$empleado->idempleado)
                        ->where("bt.fkTipoBeneficio", "=", "4")
                        ->whereDate("bt.fechaVigencia",">=", $fechaFin)
                        ->get();
                    
                    if(sizeof($beneficiosTributarioDependiente)> 0){
                        $dependiente = ($ingreso * $varRetencion[18]);
                    }
                                
                    //Tope maximo dependencia
                    if($dependiente > round($uvtActual * $varRetencion[19], -3)){
                        $dependiente = round($uvtActual * $varRetencion[19], -3);
                    }
        
                    $aporteVoluntario = 0;
                    $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "6")
                        ->get();
                    foreach($grupoConceptoCalculo as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $aporteVoluntario = $aporteVoluntario + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }
                    $aporteVoluntario = $aporteVoluntario * -1;
                    if($aporteVoluntario > round($rentaLiquida * $varRetencion[20], -3)){
                        $aporteVoluntario = round($rentaLiquida * $varRetencion[20], -3);
                    }
                    
                    $AFC = 0;
                    $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "8")
                        ->get();
                    foreach($grupoConceptoCalculo as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $AFC = $AFC + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }
                    
                    if($AFC > round($rentaLiquida * $varRetencion[21], -3)){
                        $AFC = round($rentaLiquida * $varRetencion[21], -3);
                    }
        
        
        
        
                    $deducciones = $interesesVivienda + $medicinaPrepagada + $dependiente + $aporteVoluntario + $AFC;
                    $deduccionesSinAportes = $interesesVivienda + $medicinaPrepagada + $dependiente;
        
                    $baseNeta = $rentaLiquida - $deducciones;
                    $baseNetaSinAportes = $rentaLiquida - $deduccionesSinAportes;
        
                    $exenta = $baseNeta * $varRetencion[22];
                    $exentaSinAportes = $baseNetaSinAportes * $varRetencion[22];
        
            
                    if($exenta > round($uvtActual * $varRetencion[23],-3)){
                        $exenta = round($uvtActual * $varRetencion[23], -3);
                    }
                    if($exentaSinAportes > round($uvtActual * $varRetencion[23],-3)){
                        $exentaSinAportes = round($uvtActual * $varRetencion[23], -3);
                    }
        
                    $totalBeneficiosTributarios = $exenta + $deducciones;
                    $totalBeneficiosTributariosSinAportes = $exentaSinAportes + $deduccionesSinAportes;
                    
                    
        
                    $topeBeneficios= $rentaLiquida*$varRetencion[24];
        
                    if($totalBeneficiosTributarios > ($rentaLiquida*$varRetencion[24])){
                        $totalBeneficiosTributarios = $rentaLiquida*$varRetencion[24];
                    }
        
        
                    if($totalBeneficiosTributarios > round($uvtActual*$varRetencion[25],-3)){
                        $totalBeneficiosTributarios = round($uvtActual*$varRetencion[25], -3);
                        $topeBeneficios= $rentaLiquida*round($uvtActual*$varRetencion[25], -3);
                    }
        
        
                    if($totalBeneficiosTributariosSinAportes > ($rentaLiquida*$varRetencion[24])){
                        $totalBeneficiosTributariosSinAportes = $rentaLiquida*$varRetencion[24];
                    }
                    if($totalBeneficiosTributariosSinAportes > round($uvtActual*$varRetencion[25],-3)){
                        $totalBeneficiosTributariosSinAportes = round($uvtActual*$varRetencion[25], -3);
                    }
        
        
        
                    
        
                    $baseGravable  = $rentaLiquida - $totalBeneficiosTributarios;
                    $baseGravableSinAportes  = $rentaLiquida - $totalBeneficiosTributariosSinAportes;
                    
                    $baseGravableUVTS = round($baseGravable / $uvtActual, 2);
                    $baseGravableSinAportesUVTS = round($baseGravableSinAportes / $uvtActual, 2);
                    
                    
        
        
                    $impuestoUVT = 0;
                    if($baseGravableUVTS > $varRetencion[26] && $baseGravableUVTS <= $varRetencion[27]){
                        $impuestoUVT = ($baseGravableUVTS - $varRetencion[26])*$varRetencion[29];
                        $impuestoUVT = $impuestoUVT + $varRetencion[28];
                    }
                    else if($baseGravableUVTS > $varRetencion[30] && $baseGravableUVTS <= $varRetencion[31]){
                        $impuestoUVT = ($baseGravableUVTS - $varRetencion[30])*$varRetencion[33];
                        $impuestoUVT = $impuestoUVT + $varRetencion[32];
                    }
                    else if($baseGravableUVTS > $varRetencion[34] && $baseGravableUVTS <= $varRetencion[35]){
                        $impuestoUVT = ($baseGravableUVTS - $varRetencion[34])*$varRetencion[37];
                        $impuestoUVT = $impuestoUVT + $varRetencion[36];
                    }
                    else if($baseGravableUVTS > $varRetencion[38] && $baseGravableUVTS <= $varRetencion[39]){
                        $impuestoUVT = ($baseGravableUVTS - $varRetencion[38])*$varRetencion[41];
                        $impuestoUVT = $impuestoUVT + $varRetencion[40];
                    }
                    else if($baseGravableUVTS > $varRetencion[42] && $baseGravableUVTS <= $varRetencion[43]){
                        $impuestoUVT = ($baseGravableUVTS - $varRetencion[42])*$varRetencion[45];
                        $impuestoUVT = $impuestoUVT + $varRetencion[44];
                    }
                    else if($baseGravableUVTS > $varRetencion[46]){
                        $impuestoUVT = ($baseGravableUVTS - $varRetencion[46])*$varRetencion[48];
                        $impuestoUVT = $impuestoUVT + $varRetencion[47];
                    }
                    
                    $impuestoSinAportesUVT = 0;
                    if($baseGravableSinAportesUVTS > $varRetencion[26] && $baseGravableSinAportesUVTS <= $varRetencion[27]){
                        $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[26])*$varRetencion[29];
                        $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[28];
                    }
                    else if($baseGravableSinAportesUVTS > $varRetencion[30] && $baseGravableSinAportesUVTS <= $varRetencion[31]){
                        $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[30])*$varRetencion[33];
                        $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[32];
                    }
                    else if($baseGravableSinAportesUVTS > $varRetencion[34] && $baseGravableSinAportesUVTS <= $varRetencion[35]){
                        $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[34])*$varRetencion[37];
                        $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[36];
                    }
                    else if($baseGravableSinAportesUVTS > $varRetencion[38] && $baseGravableSinAportesUVTS <= $varRetencion[39]){
                        $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[38])*$varRetencion[41];
                        $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[40];
                    }
                    else if($baseGravableSinAportesUVTS > $varRetencion[42] && $baseGravableSinAportesUVTS <= $varRetencion[43]){
                        $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[42])*$varRetencion[45];
                        $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[44];
                    }
                    else if($baseGravableSinAportesUVTS > $varRetencion[46]){
                        $impuestoSinAportesUVT = ($baseGravableSinAportesUVTS - $varRetencion[46])*$varRetencion[48];
                        $impuestoSinAportesUVT = $impuestoSinAportesUVT + $varRetencion[47];
                    }
        
                    $impuestoValor = round($impuestoUVT * $uvtActual, -3);
                    $impuestoValorSinAportes = round($impuestoSinAportesUVT * $uvtActual, -3);
                    $valorInt = $impuestoValor;
                    if($impuestoValor>0){
        
                        if($periodo == 30){
                            $arrValorxConcepto[36] = array(
                                "naturaleza" => "3",
                                "unidad" => "UNIDAD",
                                "cantidad"=> "0",
                                "arrNovedades"=> array(),
                                "valor" => $valorInt*-1 ,
                                "tipoGen" => "automaticos"
                            );
                        }
                        else{
                            if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                                $arrValorxConcepto[36] = array(
                                    "naturaleza" => "3",
                                    "unidad" => "UNIDAD",
                                    "cantidad"=> "0",
                                    "arrNovedades"=> array(),
                                    "valor" => $valorInt*-1 ,
                                    "tipoGen" => "automaticos"
                                );
                            }
                            else{
                                $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";
                                
                                $itemReteFuente = DB::table("item_boucher_pago","ibp")
                                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                                ->where("bp.fkEmpleado","=", $empleado->idempleado)
                                ->where("ln.fkEstado","=","5")//Terminada
                                ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                                ->where("ibp.fkConcepto","=","36")//RETENCION                
                                ->get();
        
                                if(sizeof($itemReteFuente)> 0){
                                    $valorInt = $valorInt - $itemReteFuente[0]->descuento;
                                }
                            
        
                                $arrValorxConcepto[36] = array(
                                    "naturaleza" => "3",
                                    "unidad" => "UNIDAD",
                                    "cantidad"=> "0",
                                    "arrNovedades"=> array(),
                                    "valor" => $valorInt*-1 ,
                                    "tipoGen" => "automaticos"
                                );
        
                            }
                        }
                        
                    }
                    $valorSalario = 0;
                    $grupoConceptoCalculo = DB::table("grupoconcepto_concepto","gcc")
                        ->where("gcc.fkGrupoConcepto", "=", "1")
                        ->get();
                    foreach($grupoConceptoCalculo as $grupoConcepto){
                        if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                            $valorSalario = $valorSalario + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                        }
                    }
                
                    if($periodo == 15){
                        if(substr($liquidacionNomina->fechaInicio,8,2) == "16"){
                            $fechaPrimeraQuincena = substr($liquidacionNomina->fechaInicio,0,8)."01";
                            $itemsBoucherPago = DB::table("item_boucher_pago","ibp")
                            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")
                            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                            ->where("bp.fkEmpleado","=", $empleado->idempleado)
                            ->where("ln.fkEstado","=","5")//Terminada
                            ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                            ->where("gcc.fkGrupoConcepto", "=", "1")
                            ->get();
                
                            foreach($itemsBoucherPago as $itemBoucherPago){
                                $valorSalario = $valorSalario + floatval($itemBoucherPago->pago);
                            }
                        }
                    }
        
        
        
                    
                    $retencionContingente = $impuestoValorSinAportes - $impuestoValor;
        
                    $arrayRetencion["salario"] = $valorSalario;
                    $arrayRetencion["ingreso"] = $ingreso;
                    $arrayRetencion["EPS"] = $EPS*-1;
                    $arrayRetencion["AFP"] = $AFP*-1;
                    $arrayRetencion["FPS"] = $FPS*-1;
                    $arrayRetencion["seguridadSocial"] = $SS;
                    $arrayRetencion["interesesVivienda"] = $interesesVivienda;
                    $arrayRetencion["medicinaPrepagada"] = $medicinaPrepagada;
                    $arrayRetencion["dependiente"] = $dependiente;
                    $arrayRetencion["aporteVoluntario"] = $aporteVoluntario;
                    $arrayRetencion["AFC"] = $AFC;
                    $arrayRetencion["exenta"] = $exenta;
                    $arrayRetencion["exentaSinAportes"] = $exentaSinAportes;
                    $arrayRetencion["totalBeneficiosTributarios"] = $totalBeneficiosTributarios;
                    $arrayRetencion["totalBeneficiosTributariosSinAportes"] = $totalBeneficiosTributariosSinAportes;
                    $arrayRetencion["topeBeneficios"] = $topeBeneficios;
                    $arrayRetencion["baseGravableUVTS"] = $baseGravableUVTS;
                    $arrayRetencion["impuestoUVT"] = $impuestoUVT;
                    $arrayRetencion["impuestoSinAportesUVT"] = $impuestoSinAportesUVT;
                    $arrayRetencion["impuestoValor"] = $impuestoValor;
                    $arrayRetencion["impuestoValorSinAportes"] = $impuestoValorSinAportes;
                    $arrayRetencion["retencionContingente"] = $retencionContingente;
                }



                $itemsBoucherMismoPeriodoNomin = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("ibp.*")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","=",$fechaInicio)
                ->where("ln.fechaFin","=",$fechaFin)
                ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                ->get();

                foreach($itemsBoucherMismoPeriodoNomin as $itemBoucherMismoPeriodoNomin){                        
                    if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                        $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                        $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                    }
                }


                if($periodo == 15){                
                    if(substr($liquidacionNomina->fechaInicio,8,2) == "01"){
                        $itemsBoucherPeriodo16 = DB::table("item_boucher_pago", "ibp")
                        ->selectRaw("ibp.*")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->where("ln.fechaInicio","=",date("Y-m-16",strtotime($fechaInicio)))
                        ->where("ln.fechaFin","=",date("Y-m-t",strtotime($fechaInicio)))
                        ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                        ->get();
                        
                        foreach($itemsBoucherPeriodo16 as $itemBoucherMismoPeriodoNomin){                        
                            if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                                $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                                $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                            }
                        }
        
                        
                    }
                    /*//Verificar quincena mes anterior
                    $itemsBoucherPeriodoMesAnt16 = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",date("Y-m-16",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.fechaFin","=",date("Y-m-t",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->whereIn("ln.fkTipoLiquidacion",["3","7"])
                    ->get();
                    
                    
                    foreach($itemsBoucherPeriodoMesAnt16 as $itemBoucherMismoPeriodoNomin){                        
                        if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                        }
                    }
                    //Verificar quincena mes anterior
                    $itemsBoucherPeriodoMesAnt01 = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",date("Y-m-01",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.fechaFin","=",date("Y-m-15",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->whereIn("ln.fkTipoLiquidacion",["3","7"])
                    ->get();

                    foreach($itemsBoucherPeriodoMesAnt01 as $itemBoucherMismoPeriodoNomin){                        
                        if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                        }
                    }*/
                    
                        
                    
                }
                else{
                    //Verificar mes anterior
                    $itemsBoucherPeriodoMesAnt = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->where("ln.fechaInicio","=",date("Y-m-01",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.fechaFin","=",date("Y-m-t",strtotime($fechaInicio."- 1 month")))
                    ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                    ->whereIn("ln.fkTipoLiquidacion",["3","7"])
                    ->get();

                    foreach($itemsBoucherPeriodoMesAnt as $itemBoucherMismoPeriodoNomin){                        
                        if(isset($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto])){
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["valor"]) - $itemBoucherMismoPeriodoNomin->valor;
                            $arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"] = round($arrValorxConcepto[$itemBoucherMismoPeriodoNomin->fkConcepto]["cantidad"]) - $itemBoucherMismoPeriodoNomin->cantidad;
                        }
                    }
                }
                





                            
            }
            
            
        }
        
        








        if(isset($arrValorxConcepto[32])){            
            unset($arrValorxConcepto[32]);            
        }

        

       
        $salarioMaximo = ($salarioMinimoDia * 30) * 25;
        $ibcOtros = 0;
        $ibcCCF = 0;

        
        if(isset($fechaPrimeraQuincena)){ 
            if($empleado->tipoRegimen != "Salario Integral"){
                $itemsBoucherIbcOtrosMesAnterior = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.valor) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->where("ln.fechaInicio","=",$fechaPrimeraQuincena)
                ->where("ln.idLiquidacionNomina","<>",$idLiquidacionNomina)
                ->where("gcc.fkGrupoConcepto","=","19") //19 - IBC Otros
                ->first();
    
                if(isset($itemsBoucherIbcOtrosMesAnterior)){
                    $ibcOtros = $itemsBoucherIbcOtrosMesAnterior->suma;
                    $ibcCCF = $itemsBoucherIbcOtrosMesAnterior->suma;
                }
            }
            else{
                $ibcOtros = $arrBoucherPago["ibc_eps"];
                $ibcCCF = $arrBoucherPago["ibc_eps"];
            }
            
        }
        
        if($empleado->tipoRegimen != "Salario Integral"){
            $grupoConceptoIBCOtros = DB::table("grupoconcepto_concepto","gcc")
            ->where("gcc.fkGrupoConcepto", "=", '19')//19->IBC Otros
            ->get();
            foreach($grupoConceptoIBCOtros as $grupoConcepto){
                if(isset($arrValorxConcepto[$grupoConcepto->fkConcepto]) && $grupoConcepto->fkConcepto != 36){
                    $ibcOtros= $ibcOtros + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                    $ibcCCF= $ibcCCF + floatval($arrValorxConcepto[$grupoConcepto->fkConcepto]['valor']);
                }
            }
            
            
            if(isset($arrValorxConcepto[30]) && isset($arrValorxConcepto[28])){
                $ibcOtros = $arrBoucherPago["ibc_eps"];
                $ibcCCF = $arrBoucherPago["ibc_eps"];
                if($arrValorxConcepto[28]['valor']>0){
                    $ibcOtros = $ibcOtros + $arrValorxConcepto[28]['valor'];
                    $ibcCCF =  $ibcCCF + $arrValorxConcepto[28]['valor'];
                }
                if( $arrValorxConcepto[30]['valor']>0){
                    $ibcOtros = $ibcOtros +  $arrValorxConcepto[30]['valor'];
                    $ibcCCF =  $ibcCCF +  $arrValorxConcepto[30]['valor'];
                }



            }
            else if(isset($arrValorxConcepto[30])){
                $ibcOtros = $arrBoucherPago["ibc_eps"];
                $ibcCCF = $arrBoucherPago["ibc_eps"];                
                if( $arrValorxConcepto[30]['valor']>0){
                    $ibcOtros = $ibcOtros +  $arrValorxConcepto[30]['valor'];
                    $ibcCCF =  $ibcCCF +  $arrValorxConcepto[30]['valor'];
                }
            }
            else if(isset($arrValorxConcepto[28])){
                $ibcOtros = $arrBoucherPago["ibc_eps"];
                $ibcCCF = $arrBoucherPago["ibc_eps"];                
                if( $arrValorxConcepto[28]['valor']>0){
                    $ibcOtros = $ibcOtros +  $arrValorxConcepto[28]['valor'];
                    $ibcCCF =  $ibcCCF +  $arrValorxConcepto[28]['valor'];
                }
            }

            

            if($ibcOtros > $salarioMaximo){
                $ibcOtros = $salarioMaximo;
            }
            if($ibcCCF > $salarioMaximo){
                $ibcCCF = $salarioMaximo;
            }
            
            $arrBoucherPago["ibc_otros"] = intval($ibcOtros);
            $arrBoucherPago["ibc_ccf"] = intval($ibcCCF);
            //Calculo CCF
            $valorCCFEmpleador = $arrBoucherPago["ibc_ccf"] * $varParafiscales[53];
            $valorCCFEmpleador = round($valorCCFEmpleador);
            $arrParafiscales["ccf"] = $valorCCFEmpleador;
        }
        else{

            $ibcOtros = $arrBoucherPago["ibc_eps"] + ((isset($arrValorxConcepto[30]) && $arrValorxConcepto[30]['valor']>0) ? $arrValorxConcepto[30]['valor'] : 0);
            
        }

        $calculoOtrosExcento = $arrBoucherPago["ibc_otros"];
        if($arrBoucherPago["ibc_eps"]<0){
            $salarioMes = 0;
            $conceptosFijosEmpl = DB::table("conceptofijo", "cf")
            ->select(["cf.valor","cf.fechaInicio","cf.fechaFin", "cf.fkConcepto","cf.unidad", "c.*"])
            ->join("concepto AS c", "cf.fkConcepto","=","c.idconcepto")
            ->where("cf.fkEmpleado", "=", $empleado->idempleado)  
            ->where("cf.fkEstado", "=", "1")
            ->get();
            
            foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
                if($conceptoFijoEmpl->fkConcepto=="1"){
                    $salarioMes = $conceptoFijoEmpl->valor; 
                }
            }
            $calculoOtrosExcento = intval($salarioMes / 30);
        }
        
        if(isset($arrValorxConcepto[30]) && $arrValorxConcepto[30]['valor']>0){
            $calculoOtrosExcento = $calculoOtrosExcento - $arrValorxConcepto[30]['valor'];
        }
       
        if($empresa->exento == "0" || $calculoOtrosExcento > ($varParafiscales[56] * $valorSalarioMinimo)){
            
            //Calculo ICBF
            $valorICBFEmpleador = $arrBoucherPago["ibc_otros"] * $varParafiscales[54];
            $valorICBFEmpleador = round($valorICBFEmpleador);
            $arrParafiscales["icbf"] = $valorICBFEmpleador;
    
            //Calculo SENA
            $valorSenaEmpleador = $arrBoucherPago["ibc_otros"] * $varParafiscales[55];            
            $valorSenaEmpleador = round($valorSenaEmpleador);
            $arrParafiscales["sena"] = $valorSenaEmpleador;

            $valorEpsEmpleador = $arrBoucherPago["ibc_eps"] * $varParafiscales[50];
            $valorEpsEmpleador = round($valorEpsEmpleador);
    
    
            $arrParafiscales["eps"] = $valorEpsEmpleador;
            

        }
        else{
            $arrParafiscales["icbf"] = 0;
            $arrParafiscales["sena"] = 0;
            $arrParafiscales["eps"] = 0;
            $arrBoucherPago["ibc_otros"] = 0;
        }   
        
    
        
        
        


        foreach($arrValorxConcepto as $idConcepto => $arrConcepto){
            if($arrConcepto["valor"] == 0){
                unset($arrValorxConcepto[$idConcepto]);
            }
            $valorNeto = $valorNeto + $arrConcepto["valor"];
        }







        $salarioMes = 0;
        foreach($conceptosFijosEmpl as $conceptoFijoEmpl){
            if($conceptoFijoEmpl->fkConcepto=="1"){
                $salarioMes = $conceptoFijoEmpl->valor; 
            }
        }
        $salarioMes = intval(($salarioMes / 30)*$periodoPago);

        
        $periodoPago = $periodoPagoSinVac;

        $arrBoucherPago["salarioPeriodoPago"] = $salarioMes;

    
        $arrBoucherPago["fkEmpleado"] = $empleado->idempleado;
        $arrBoucherPago["fkLiquidacion"] = $idLiquidacionNomina;
        $arrBoucherPago["periodoPago"] = $periodoPago;
        $arrBoucherPago["diasTrabajados"] = $periodoGen;
        $arrBoucherPago["diasIncapacidad"] = $diasNoTrabajados;
        $arrBoucherPago["diasInjustificados"] = $diasNoTrabajadosInjustificados;
        $arrBoucherPago["netoPagar"] = $valorNeto;
        
        
        
        if($idBoucherPago!=null){
        
            DB::table('boucherpago')->where("idBoucherPago","=",$idBoucherPago)->update($arrBoucherPago);
        }
        else{
            
            $idBoucherPago = DB::table('boucherpago')->insertGetId($arrBoucherPago, "idBoucherPago");
        }
    
        

        

        if(sizeof($arrayRetencion)>0){
            
            $retencionfuente = DB::table('retencionfuente')
            ->where("fkBoucherPago","=",$idBoucherPago)
            ->where("tipoRetencion","=","NORMAL")            
            ->get();
            
            if(sizeof($retencionfuente)>0){
                DB::table('retencionfuente')->where("idRetencionFuente", "=",$retencionfuente[0]->idRetencionFuente)->update($arrayRetencion);
            }
            else{
                $arrayRetencion["fkBoucherPago"] = $idBoucherPago;
                DB::table('retencionfuente')->insert($arrayRetencion);
            }
            
        }
        else{
            $retencionfuente = DB::table('retencionfuente')
            ->where("fkBoucherPago","=",$idBoucherPago)
            ->where("tipoRetencion","=","NORMAL")            
            ->get();
            if(sizeof($retencionfuente)>0){
                DB::table('retencionfuente')
                ->where("fkBoucherPago","=",$idBoucherPago)
                ->where("tipoRetencion","=","NORMAL")        
                ->delete();
            }
            
        }
        if(sizeof($arrayRetencionInd)>0){
            
            $retencionfuente = DB::table('retencionfuente')
            ->where("fkBoucherPago","=",$idBoucherPago)
            ->where("tipoRetencion","=","INDEMNIZACION")            
            ->get();
            
            if(sizeof($retencionfuente)>0){
                DB::table('retencionfuente')->where("idRetencionFuente", "=",$retencionfuente[0]->idRetencionFuente)->update($arrayRetencionInd);
            }
            else{
                $arrayRetencionInd["fkBoucherPago"] = $idBoucherPago;
                DB::table('retencionfuente')->insert($arrayRetencionInd);
            }
            
        }
        else{
            $retencionfuente = DB::table('retencionfuente')
            ->where("fkBoucherPago","=",$idBoucherPago)
            ->where("tipoRetencion","=","INDEMNIZACION")            
            ->get();
            if(sizeof($retencionfuente)>0){
                DB::table('retencionfuente')
                ->where("fkBoucherPago","=",$idBoucherPago)
                ->where("tipoRetencion","=","INDEMNIZACION")        
                ->delete();
            }
            
        }
        


        $parafiscales = DB::table('parafiscales')->where("fkBoucherPago","=",$idBoucherPago)->get();
        if(sizeof($parafiscales)>0){
            DB::table('parafiscales')->where("idParafiscales", "=",$parafiscales[0]->idParafiscales)->update($arrParafiscales);
        }
        else{
            $arrParafiscales["fkBoucherPago"] = $idBoucherPago;
            DB::table('parafiscales')->insert($arrParafiscales);
        }

        DB::table('item_boucher_pago')->where("fkBoucherPago","=",$idBoucherPago)->delete();        

        foreach($arrValorxConcepto as $idConcepto => $arrConcepto){

            $arrInsertItemBoucher = array(
                "fkBoucherPago" => $idBoucherPago, 
                "fkConcepto" => $idConcepto, 
                "cantidad" => $arrConcepto["cantidad"],
                "tipoUnidad" => $arrConcepto["unidad"],
                "valor" => $arrConcepto["valor"],
                "tipo" => $arrConcepto["tipoGen"]
            );
            if($arrConcepto["naturaleza"]=="1"){
                $arrInsertItemBoucher["pago"] = $arrConcepto["valor"];
            }
            else{
                $arrInsertItemBoucher["descuento"] = $arrConcepto["valor"]*-1;
            }

            if(isset($arrConcepto["base"])){
                $arrInsertItemBoucher["base"] = $arrConcepto["base"];
            }

            if(isset($arrConcepto["fechaInicio"])){
                $arrInsertItemBoucher["fechaInicio"] = $arrConcepto["fechaInicio"];
            }
            if(isset($arrConcepto["fechaFin"])){
                $arrInsertItemBoucher["fechaFin"] = $arrConcepto["fechaFin"];
            }


            

            if($arrConcepto["tipoGen"]=="novedadAus"){
                $arrInsertItemBoucher["descuento"] = $arrConcepto["valorAus"];
            }

            $idItemBoucherPago  = DB::table('item_boucher_pago')->insertGetId($arrInsertItemBoucher, "idItemBoucherPago");

            foreach($arrConcepto["arrNovedades"] as $datosNovedad){

                
                DB::table('item_boucher_pago_novedad')->insert(
                    [
                        "fkItemBoucher" => $idItemBoucherPago,
                        "fkNovedad"=> $datosNovedad['idNovedad'],
                        "valor"=> $datosNovedad['valor'],
                        "parcial" => (isset($datosNovedad['parcial']) ? $datosNovedad['parcial'] : 0)
                    ]
                );
            }                
        }
        return true;
    }
    public function nominasLiquidadas(Request $req){

        $liquidaciones = DB::table("liquidacionnomina", "ln")
        ->select(["ln.idLiquidacionNomina", "ln.fechaLiquida", "e.razonSocial", "tl.nombre as tipoLiquidacion", "est.nombre as estado", "n.nombre as nomNombre"])
        ->join("nomina AS n","ln.fkNomina", "=", "n.idnomina")
        ->join("empresa AS e","n.fkEmpresa","=", "e.idempresa")
        ->join("tipoliquidacion AS tl","ln.fkTipoLiquidacion","=", "tl.idTipoLiquidacion")        
        ->join("estado AS est","ln.fkEstado","=", "est.idestado")
        ->where("ln.fkEstado", "=", "5")
        ->orderBy("ln.fkNomina","desc")
        ->orderBy("ln.fechaLiquida","desc");

        if(isset($req->fechaInicio)){
            $liquidaciones = $liquidaciones->where("ln.fechaLiquida",">=",$req->fechaInicio);
        }
        
        if(isset($req->fechaFin)){
            $liquidaciones = $liquidaciones->where("ln.fechaLiquida","<=",$req->fechaFin);
        }

        if(isset($req->nomina)){
            $liquidaciones = $liquidaciones->where("ln.fkNomina","=",$req->nomina);
        }
        if(isset($req->tipoLiquidacion)){
            $liquidaciones = $liquidaciones->where("ln.fkTipoLiquidacion","=",$req->tipoLiquidacion);
        }

        $liquidaciones = $liquidaciones->paginate(15);

        $nominas = DB::table("nomina")->orderBy("nombre")->get();
        $tipoLiquidaciones = DB::table("tipoliquidacion")->orderBy("nombre")->get();

        
        $arrConsulta = array("fechaInicio"=> $req->fechaInicio, "fechaFin"=> $req->fechaFin, "nomina"=> $req->nomina, "tipoLiquidacion"=> $req->tipoLiquidacion);
        return view('/nomina.liquidada.listaNominas',[
            "liquidaciones" => $liquidaciones,
            "arrConsulta" => $arrConsulta,
            "req" => $req,
            "nominas" => $nominas,
            "tipoLiquidaciones" => $tipoLiquidaciones
        ]);
    }

    public function documentoRetencion($idLiquidacion){


        $retenciones = DB::table("retencionfuente", "rf")
        ->select(["rf.*","dp.numeroIdentificacion","dp.primerNombre","dp.segundoNombre","dp.primerApellido","dp.segundoApellido"])
        ->join("boucherpago as bp","bp.idBoucherPago","=","rf.fkBoucherPago")
        ->join("empleado as e","e.idempleado","=","bp.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales","=","e.fkDatosPersonales")
        ->where("bp.fkLiquidacion","=",$idLiquidacion)
        //->where("rf.impuestoValor",">",0)
        ->get();
        $arrDatos = array();
        $arrDatos[0] = array(
            "ID",
            "NOMBRE",
            "SALARIO",
            "OTROS_INGRESOS",
            "TOTAL_INGRESOS",
            "",
            "EPS",
            "AFP",
            "FPS",
            "TOTAL",
            "",
            "RENTA LIQUIDA",
            "",
            "INT",
            "MP",
            "DEP",
            "VOLUN",
            "TOTAL",
            "",
            "BASE NETA",
            "",
            "EXCENTO",
            "",
            "BENEFICIOS_TOTAL",
            "BENEFICIOS_TOPE",
            "",
            "BASE_GRAVABLE",
            "UVT",
            "",
            "RETENCION",
            "RETENCION SIN APORTES",
            "RETENCION CONTINGENTE"
        );//Diferencial entre retenciones

        foreach($retenciones as $retencion){
            $deducciones = $retencion->interesesVivienda + $retencion->medicinaPrepagada + $retencion->dependiente + $retencion->aporteVoluntario + $retencion->AFC;
            $rentaLiquida = $retencion->ingreso - $retencion->seguridadSocial;


            $baseGravable  = $rentaLiquida - $retencion->totalBeneficiosTributarios;

            $arrDatosGen = array(
                $retencion->numeroIdentificacion,
                $retencion->primerApellido." ".$retencion->segundoApellido." ".$retencion->primerNombre." ".$retencion->segundoNombre,
                $retencion->salario,
                $retencion->ingreso - $retencion->salario,
                $retencion->ingreso,
                "",
                $retencion->EPS,
                $retencion->AFP,
                $retencion->FPS,
                $retencion->seguridadSocial,
                "",
                $rentaLiquida,
                "",
                $retencion->interesesVivienda,
                $retencion->medicinaPrepagada,
                $retencion->dependiente,
                $retencion->aporteVoluntario + $retencion->AFC,
                $deducciones,
                "",
                $rentaLiquida - $deducciones,
                "",
                $retencion->exenta,
                "",
                $retencion->totalBeneficiosTributarios,
                $retencion->topeBeneficios,
                "",
                $baseGravable,
                $retencion->baseGravableUVTS,
                "",
                $retencion->impuestoValor,
                $retencion->impuestoValorSinAportes,
                $retencion->retencionContingente,
            );
            array_push($arrDatos, $arrDatosGen);
        }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=Informe_Retencion_Fuente_'.$idLiquidacion.'.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($arrDatos);
        $csv->output('Informe_Retencion_Fuente_'.$idLiquidacion.'.csv');


    }
    
    public function documentoSS($idLiquidacion){
        $arrDatos = array();
        $arrDatos[0] = array(
            "TIPO REGISTRO",
            "SECUENCIA",
            "TIPO DOCUMENTO",
            "IDENTIFICACION",
            "TIPO COTIZANTE",
            "SUBTIPO COTIZANTE",
            "EXTRANJERO NO COTIZA PENSION",
            "RESIDENTE EXTERIOR",
            "CODIGO DEPARTAMENTO",
            "CODIGO MUNICIPIO",
            "PRIMER APELLIDO",
            "SEGUNDO APELLIDO",
            "PRIMER NOMBRE",
            "SEGUNDO NOMBRE",
            "ING",
            "RET",
            "TDE",
            "TAE",
            "TDP",
            "TAP",
            "VSP",
            "VTE",
            "VST",
            "SLN",
            "IGE",
            "LMA",
            "VAC",
            "AVP",
            "VCT",
            "IRP",
            "CODIGO PENSION",
            "NOMBRE ADMINISTRADORA PENSION",
            "CODIGO PENSION TRASLADO",
            "NOMBRE PENSION TRASLADO",
            "CODIGO SALUD",
            "NOMBRE ADMINISTRADORA SALUD",
            "CODIGO SALUD TRASLADO",
            "NOMBRE SALUD TRASLADO",
            "CODIGO CCF",
            "NOMBRE ADMINISTRADORA CCF",
            "DIAS PENSION",
            "DIAS SALUD",
            "DIAS RIESGOS",
            "DIAS CCF",
            "SALARIO BASICO",
            "TIPO SALARIO",
            "IBC PENSION",
            "IBC SALUD",
            "IBC RIESGOS",
            "IBC CCF",
            "IBC SENA e ICBF",
            "TARIFA AFP",
            "COTIZACION OBLIGATORIA PENSION",
            "APORTE VOLUNTARIO AFILIADO",
            "COTIZACION VOLUNTARIA APORTANTE",
            "TOTAL COTIZACION",
            "FSP SOLIDARIDAD",
            "FSP SUBSISTENCIA",
            "VALOR NO RETENIDO",
            "TARIFA EPS",
            "COTIZACION SALUD",
            "VALOR UPC",
            "NUMERO IGE",
            "VALOR IGE",
            "NUMERO LMA",
            "VALOR LMA",
            "TARIFA RIESGOS",
            "CENTRO TRABAJO",
            "COTIZACION RIESGOS",
            "TARIFA CCF",
            "VALOR CCF",
            "TARIFA SENA",
            "VALOR SENA",
            "TARIFA ICBF",
            "VALOR ICBF",
            "TARIFA ESAP",
            "VALOR ESAP",
            "TARIFA MINISTERIO",
            "VALOR MINISTERIO",
            "FECHA INGRESO",
            "FECHA RETIRO",
            "FECHA TRASLADO EPS",
            "FECHA TRASLADO AFP",
            "FECHA CAMBIO SALARIO",
            "FECHA CAMBIO CENTRO TRABAJO",
            "FECHA SANCION INICIO",
            "FECHA SANCION FIN",
            "FECHA INCAPACIDAD INICIO",
            "FECHA INCAPACIDAD FIN",
            "FECHA LICENCIA INICIO",
            "FECHA LICENCIA FIN",
            "FECHA VACACIONES INICIO",
            "FECHA VACACIONES FIN",
            "FECHA INCAPACIDAD RIESGOS LAB. INICIO",
            "FECHA  INCAPACIDAD RIESGOS LAB. FIN",
        );
        $empleados = DB::table("empleado", "e")
        ->select(["e.*","dp.*", "ti.nombre as tipoDocumento", "bp.*"])
        ->join("boucherpago as bp","bp.fkEmpleado","=","e.idempleado")
        ->join("datospersonales as dp","dp.idDatosPersonales","=","e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","dp.fkTipoIdentificacion")
        ->where("bp.fkLiquidacion","=",$idLiquidacion)->get();

        $liquidacionNomina = DB::table('liquidacionnomina')->where("idLiquidacionNomina", "=", $idLiquidacion)->first();


        $arlEmpresa = DB::table("tercero", "t")
        ->select(["t.*"])
        ->join("empresa as e", "e.fkTercero_ARL", "=", "t.idTercero")
        ->join("nomina as n", "n.fkEmpresa", "=", "e.idempresa")
        ->join("liquidacionnomina as ln", "ln.fkNomina", "=", "n.idNomina")
        ->where("ln.idLiquidacionNomina", "=",$idLiquidacion)
        ->first();

        

        $incremental = 1;




        foreach($empleados as $empleado){
            $arrayFila = array();

            for ($i=0; $i <= 94; $i++) { 
                $arrayFila[$i] = "";
            }

            $arrayFila[0] = "02";
            $arrayFila[1] = $incremental;
            $arrayFila[2] = $empleado->tipoDocumento;
            $arrayFila[3] = $empleado->numeroIdentificacion;
            $arrayFila[4] = "01"; // Cuando deja de ser 01?
            $arrayFila[5] = "00"; // Cuando deja de ser 00?
            if($empleado->fkTipoIdentificacion == "4" || $empleado->fkTipoIdentificacion == "7"){
                $arrayFila[6] = "X";
            }
            if(substr($empleado->fkUbicacionResidencia,0,2) != "57"){
                $arrayFila[7] = "X";
            }
            $arrayFila[8] = substr("0".substr($empleado->fkUbicacionResidencia,2,2),-2);
            $arrayFila[9] = substr("00".substr($empleado->fkUbicacionResidencia,4),-3);
            $arrayFila[10] = $empleado->primerApellido;
            $arrayFila[11] = $empleado->segundoApellido;
            $arrayFila[12] = $empleado->primerNombre;
            $arrayFila[13] = $empleado->segundoNombre;
            

            if(strtotime($liquidacionNomina->fechaInicio) < strtotime($empleado->fechaIngreso)){
                $arrayFila[14] = "X";
            }
            
            $novedadesRetiro = DB::table("novedad","n")
                ->where("n.fkEmpleado","=", $empleado->idempleado)
                ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
                ->whereNotNull("n.fkRetiro")
                ->whereBetween("n.fechaRegistro",[$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
                ->get();

            $fechaRetiro = "";
            foreach($novedadesRetiro as $retiro){
                $arrayFila[15] = "X";
                $retiroTabla = DB::table("retiro", "r")
                    ->where("idRetiro","=",$retiro->fkRetiro)
                    ->first();

                $fechaRetiro = $retiroTabla->fechaReal;
            }
            



            //TDE
            $fechaInicioParaMesAntes = date("Y-m-01", strtotime($liquidacionNomina->fechaInicio."  -1 month"));
            $fechaFinParaMesAntes = date("Y-m-t", strtotime($fechaInicioParaMesAntes));
            
            $cambioAfiliacionEps = DB::table("cambioafiliacion","ca")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "3") //3-Salud
                ->whereBetween("ca.fechaCambio", [$fechaInicioParaMesAntes, $fechaFinParaMesAntes])->get();
            $fechaCambioAfiliacionEPS = "";
            foreach($cambioAfiliacionEps as $cambioAfi){
                $arrayFila[16] = "X";
                $fechaCambioAfiliacionEPS = $cambioAfi->fechaCambio;
            }
            //TAE
            $cambioAfiliacionEps2 = DB::table("cambioafiliacion","ca")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "3") //3-Salud
                ->whereBetween("ca.fechaCambio", [$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
                ->get();

            $fechaCambioAfiliacionEPS2 = "";
            foreach($cambioAfiliacionEps2 as $cambioAfi){
                $arrayFila[17] = "X";
                $fechaCambioAfiliacionEPS2 = $cambioAfi->fechaCambio;
            }

            //TDP
            $cambioAfiliacionPension = DB::table("cambioafiliacion","ca")
                ->join("tercero as t", "t.idTercero", "=", "ca.fkTerceroNuevo")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "4") //4-Pension
                ->whereBetween("ca.fechaCambio", [$fechaInicioParaMesAntes, $fechaFinParaMesAntes])->get();
            $fechaCambioAfiliacionAFP = "";
            foreach($cambioAfiliacionPension as $cambioAfi){
                $arrayFila[18] = "X";
                $fechaCambioAfiliacionAFP = $cambioAfi->fechaCambio;
            }

            //TAP
            $cambioAfiliacionPension2 = DB::table("cambioafiliacion","ca")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "4") //4-Pension
                ->whereBetween("ca.fechaCambio", [$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
                ->get();

            $fechaCambioAfiliacionAFP2 = "";
            foreach($cambioAfiliacionPension2 as $cambioAfi){
                $arrayFila[19] = "X";
                $fechaCambioAfiliacionAFP2 = $cambioAfi->fechaCambio;
            }

            //VSP
            $cambioSalario = DB::table("cambiosalario","cs")
                ->where("cs.fkEmpleado", "=", $empleado->idempleado)
                ->whereBetween("cs.fechaCambio", [$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
                ->get();

            if(sizeof($cambioSalario)>0){
                $arrayFila[20] = "X";                
            }
            
            //VTE
            $cambioCentroTrab = DB::table("cambiocentrotrabajo","cct")
                ->where("cct.fkEmpleado", "=", $empleado->idempleado)
                ->whereBetween("cct.fechaCambio", [$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
                ->get();
            
            if(sizeof($cambioCentroTrab)>0){
                $arrayFila[21] = "X";
            }
            

            //VST
            $itemsBoucherPago = DB::table("item_boucher_pago", "ibp")
                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","<>","ibp.fkConcepto")
                ->where("ibp.fkBoucherPago","=",$empleado->idBoucherPago)
                ->where("ibp.pago",">","0")
                ->where("gcc.fkConcepto","=","10") //10 - CONCEPTOS QUE GENERAN VST	
                ->get();
            if(sizeof($itemsBoucherPago)>0){
                $arrayFila[22] = "X";
            }


            //SLN
            $novedadesSancion = DB::table("novedad","n")
                ->join("ausencia AS a","a.idAusencia", "=", "n.fkAusencia")
                ->where("a.cantidadDias",">=", "1")
                ->where("n.fkEmpleado","=", $empleado->idempleado)
                ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
                ->whereNotNull("n.fkAusencia")
                ->whereBetween("n.fechaRegistro",[$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
                ->get();

            if(sizeof($novedadesSancion)>0){
                $arrayFila[23] = "X";
            }
            
            //IGE
            $novedadesIncapacidadNoLab = DB::table("novedad","n")
            ->join("incapacidad as i","i.idIncapacidad","=", "n.fkIncapacidad")
            ->where("i.fkTipoAfilicacion","=","3") //3- Salud
            ->whereNotIn("i.tipoIncapacidad",["Maternidad", "Paternidad"])
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
            ->whereNotNull("n.fkIncapacidad")
            ->whereBetween("n.fechaRegistro",[$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
            ->get();

            if(sizeof($novedadesIncapacidadNoLab)>0){
                $arrayFila[24] = "X";
            }

            //LMA
            $novedadesIncapacidadNoLaMat = DB::table("novedad","n")
            ->join("incapacidad as i","i.idIncapacidad","=", "n.fkIncapacidad")
            ->whereIn("i.tipoIncapacidad",["Maternidad", "Paternidad"])
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
            ->whereNotNull("n.fkIncapacidad")
            ->whereBetween("n.fechaRegistro",[$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
            ->get();

            if(sizeof($novedadesIncapacidadNoLaMat)>0){
                $arrayFila[25] = "X";
            }


            //VAC
            $novedadesVac = DB::table("novedad","n")
            ->join("vacaciones as v","v.idVacaciones","=", "n.fkVacaciones")
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
            ->whereNotNull("n.fkVacaciones")
            ->whereBetween("n.fechaRegistro",[$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
            ->get();

            if(sizeof($novedadesVac)>0){
                $arrayFila[26] = "X";
            }

            //AVP
            $itemsBoucherAVP = DB::table("item_boucher_pago", "ibp")
                ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")
                ->where("ibp.fkBoucherPago","=",$empleado->idBoucherPago)
                ->where("gcc.fkGrupoConcepto","=","6") //6 - APORTE VOLUNTARIO PENSION	
                ->get();
            if(sizeof($itemsBoucherAVP)>0){
                $arrayFila[27] = "X";
            }

            //IRL
            $arrayFila[28] = "";

            $novedadesIncapacidadLab = DB::table("novedad","n")
            ->join("incapacidad as i","i.idIncapacidad","=", "n.fkIncapacidad")
            ->whereNull("i.fkTipoAfilicacion") // NULL - Accidente laboral
            ->whereNotIn("i.tipoIncapacidad",["Maternidad", "Paternidad"])
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
            ->whereNotNull("n.fkIncapacidad")
            ->whereBetween("n.fechaRegistro",[$liquidacionNomina->fechaInicio, $liquidacionNomina->fechaFin])
            ->get();

            if(sizeof($novedadesIncapacidadLab)>0){
                $arrayFila[29] = "X";
            }

            $pension = DB::table("afiliacion","a")
            ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
            ->where("a.fkEmpleado", "=", $empleado->idempleado)
            ->where("a.fkTipoAfilicacion", "=", "4") // 4 - Tipo Afiliacion = Pension
            ->first();

            
            $arrayFila[30] = $pension->codigoTercero;
            $arrayFila[31] = $pension->razonSocial;
            foreach($cambioAfiliacionPension as $cambioAfi){
                $arrayFila[32] = $cambioAfi->codigoTercero;
                $arrayFila[33] = $cambioAfi->razonSocial;
            }
            
            $salud = DB::table("afiliacion","a")
            ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
            ->where("a.fkEmpleado", "=", $empleado->idempleado)
            ->where("a.fkTipoAfilicacion", "=", "3") // 3 - Tipo Afiliacion = Pension
            ->first();
            
            $arrayFila[34] = $salud->codigoTercero;
            $arrayFila[35] = $salud->razonSocial;
            foreach($cambioAfiliacionEps as $cambioAfi){
                $arrayFila[36] = $cambioAfi->codigoTercero;
                $arrayFila[37] = $cambioAfi->razonSocial;
            }

            $arrayFila[38] = $arlEmpresa->codigoTercero;
            $arrayFila[39] = $arlEmpresa->razonSocial;            

            $periodo_completo = $empleado->diasTrabajados + $empleado->diasIncapacidad + $empleado->diasInjustificados;
            
            $arrayFila[40] = $periodo_completo;
            $arrayFila[41] = $periodo_completo;
            $arrayFila[42] = $periodo_completo;
            $arrayFila[43] = $periodo_completo;

            //Salario
            $itemsBoucherSalario = DB::table("conceptofijo", "cf")
                ->whereIn("cf.fkConcepto",["1","2"])
                ->where("cf.fkEmpleado", "=", $empleado->idempleado)
                ->first();
            $arrayFila[44] = intval($itemsBoucherSalario->valor);



            //TIPO SALARIO
            $arrayFila[45] = "F";
            if($empleado->tipoRegimen=="Salario Integral"){
                $arrayFila[45] = "X";
            }

            $arrayFila[46] = $empleado->ibc_afp;
            $arrayFila[47] = $empleado->ibc_eps;
            $arrayFila[48] = $empleado->ibc_arl;
            $arrayFila[49] = $empleado->ibc_ccf;
            $arrayFila[50] = $empleado->ibc_otros;

            //TARIFA AFP
            if($empleado->esPensionado==0){
                $varsPension = DB::table("variable", "v")->whereIn("v.idVariable",["51","52"])->get();
                $totalPorcentajePension = 0;
                foreach($varsPension as $varPension){
                    $totalPorcentajePension = $totalPorcentajePension + floatval($varPension->valor);
                }

                $arrayFila[51] = $totalPorcentajePension;
                
            }
            else{
                $arrayFila[51] = "0";   
            }
            
            //COTIZACION OBLIGATORIA PENSION

            $itemsBoucherPension = DB::table("item_boucher_pago", "ibp")
            ->where("ibp.fkBoucherPago","=",$empleado->idBoucherPago)
            ->whereIn("ibp.fkConcepto",["19"])
            ->get();
            
            $parafiscales = DB::table("parafiscales","p")
            ->where("p.fkBoucherPago","=",$empleado->idBoucherPago)
            ->get();
            $cotizacionPension = 0;
            foreach($itemsBoucherPension as $itemBoucherPension){
                if( $itemBoucherPension->valor < 0){
                    $cotizacionPension = $cotizacionPension + ($itemBoucherPension->valor * -1);
                }
                else{
                    $cotizacionPension = $cotizacionPension + $itemBoucherPension->valor;
                }
                //$cotizacionPension = $cotizacionPension + $itemBoucherPension->valor;
            }

            foreach($parafiscales as $parafiscal){
                $cotizacionPension = $cotizacionPension + $parafiscal->afp;
            }

            $arrayFila[52] = $cotizacionPension;

            //APORTE VOLUNTARIO AFILIADO
            $aporteVoluntarioPension = 0;
            foreach($itemsBoucherAVP as $itemBoucherAVP){
                if( $itemBoucherAVP->valor < 0){
                    $aporteVoluntarioPension = $aporteVoluntarioPension + ($itemBoucherAVP->valor * -1);
                }
                else{
                    $aporteVoluntarioPension = $aporteVoluntarioPension + $itemBoucherAVP->valor;
                }
                //$aporteVoluntarioPension = $aporteVoluntarioPension + $itemBoucherAVP->valor;
            }

            $arrayFila[53] = $aporteVoluntarioPension;
            $arrayFila[54] = 0;
            $arrayFila[55] = $cotizacionPension + $aporteVoluntarioPension;

            //FSP SOLIDARIDAD	            
            $itemsBoucherFPS = DB::table("item_boucher_pago", "ibp")
            ->where("ibp.fkBoucherPago","=",$empleado->idBoucherPago)
            ->whereIn("ibp.fkConcepto",["33"])
            ->get();
            $totalFPS = 0;
            foreach($itemsBoucherFPS as $itemBoucherFPS){
                if( $itemBoucherFPS->valor < 0){
                    $totalFPS = $totalFPS + ($itemBoucherFPS->valor * -1);
                }
                else{
                    $totalFPS = $totalFPS + $itemBoucherFPS->valor;
                }
                
            }
            $arrayFila[56] = $totalFPS;

            //FSP SUBSISTENCIA	
            $arrayFila[57] = "0";
            //VALOR NO RETENIDO
            $arrayFila[58] = "0";
            //TARIFA EPS
            $varsEPS = DB::table("variable", "v")->whereIn("v.idVariable",["49"])->get();
            $totalPorcentajeEPS = 0;
            foreach($varsEPS as $varEPS){
                
                $totalPorcentajeEPS = $totalPorcentajeEPS + floatval($varEPS->valor);
            }
            $arrayFila[59] = $totalPorcentajeEPS;    
            //COTIZACION SALUD
            $itemsBoucherEPS = DB::table("item_boucher_pago", "ibp")
            ->where("ibp.fkBoucherPago","=",$empleado->idBoucherPago)
            ->whereIn("ibp.fkConcepto",["18"])
            ->get();
            $totalEPS = 0;
            foreach($itemsBoucherEPS as $itemBoucherEPS){

                if( $itemBoucherEPS->valor < 0){
                    $totalEPS = $totalEPS + ($itemBoucherEPS->valor * -1);
                }
                else{
                    $totalEPS = $totalEPS + $itemBoucherEPS->valor;
                }
                
            }
            $arrayFila[60] = $totalEPS;   
            //VALOR UPC	
            $arrayFila[61] = "0";   
            //NUMERO IGE	
            $arrayFila[62] = "";   
            //VALOR IGE
            $arrayFila[63] = "0";   
            //NUMERO LMA	
            $arrayFila[64] = "";   
            //VALOR LMA
            $arrayFila[65] = "0";   
            //TARIFA RIESGOS
            $nivelesArl = DB::table("nivel_arl","na")
            ->where("na.idnivel_arl","=",$empleado->fkNivelArl)
            ->first();
            $arrayFila[66] = $nivelesArl->porcentaje / 100;   

            $centroTrabajo = DB::table("centrotrabajo","ct")
            ->where("ct.idCentroTrabajo","=",$empleado->fkCentroTrabajo)
            ->first();
            $arrayFila[67] = $centroTrabajo->codigo;
            //COTIZACION RIESGOS
            $arrayFila[68] = "";
            //TARIFA CCF
            $varsCCF = DB::table("variable", "v")->whereIn("v.idVariable",["53"])->get();
            $totalPorcentajeCCF = 0;
            foreach($varsCCF as $varCCF){
                $totalPorcentajeCCF = $totalPorcentajeCCF + floatval($varCCF->valor);
            }
            $arrayFila[69] = $totalPorcentajeCCF;    
            //VALOR CCF
            $ccfFinal = 0;
            foreach($parafiscales as $parafiscal){
                $ccfFinal = $ccfFinal + $parafiscal->ccf;
            }

            $arrayFila[70] = $ccfFinal;

            //TARIFA SENA
            $varsSENA = DB::table("variable", "v")->whereIn("v.idVariable",["55"])->get();
            $totalPorcentajeSENA = 0;
            foreach($varsSENA as $varSENA){
                $totalPorcentajeSENA = $totalPorcentajeSENA + floatval($varSENA->valor);
            }
            if($empleado->ibc_otros==0){
                $totalPorcentajeSENA = 0;
            }
            $arrayFila[71] = $totalPorcentajeSENA;


            //VALOR SENA
            $SENAFinal = 0;
            foreach($parafiscales as $parafiscal){
                $SENAFinal = $SENAFinal + $parafiscal->sena;
            }

            $arrayFila[72] = $SENAFinal;

            //TARIFA ICBF
            $varsICBF = DB::table("variable", "v")->whereIn("v.idVariable",["54"])->get();
            $totalPorcentajeICBF = 0;
            foreach($varsICBF as $varICBF){
                $totalPorcentajeICBF = $totalPorcentajeICBF + floatval($varICBF->valor);
            }
            if($empleado->ibc_otros==0){
                $totalPorcentajeICBF = 0;
            }
            $arrayFila[73] = $totalPorcentajeICBF;


            //VALOR ICBF
            $ICBFFinal = 0;
            foreach($parafiscales as $parafiscal){
                $ICBFFinal = $ICBFFinal + $parafiscal->icbf;
            }

            $arrayFila[74] = $ICBFFinal;
            //TARIFA ESAP	
            $arrayFila[75] = "0";
            //VALOR ESAP
            $arrayFila[76] = "0";
            //TARIFA MINISTERIO
            $arrayFila[77] = "0";
            //VALOR MINISTERIO
            $arrayFila[78] = "0";
            
        

            //FECHA INGRESO
            if(strtotime($liquidacionNomina->fechaInicio) < strtotime($empleado->fechaIngreso)){
                $arrayFila[79] = $empleado->fechaIngreso;
            }
            //FECHA RETIRO
            $arrayFila[80] = $fechaRetiro;
            //FECHA TRASLADO EPS
            $arrayFila[81] = ($fechaCambioAfiliacionEPS == "" ? ($fechaCambioAfiliacionEPS2 == "" ? "" : $fechaCambioAfiliacionEPS2) : $fechaCambioAfiliacionEPS);
            //FECHA TRASLADO AFP
            $arrayFila[82] = ($fechaCambioAfiliacionAFP == "" ? ($fechaCambioAfiliacionAFP2 == "" ? "" : $fechaCambioAfiliacionAFP2) : $fechaCambioAfiliacionAFP);
            //FECHA CAMBIO SALARIO
            foreach($cambioSalario as $cambioSal){
                $arrayFila[83] = $cambioSal->fechaCambio;
            }
            //FECHA CAMBIO CENTRO TRABAJO
            foreach($cambioCentroTrab as $cambioCen){
                $arrayFila[84] = $cambioCen->fechaCambio;
            }
            $entrar = true;
            $contadorInterno = 0;


            while($entrar){
                $entrar = false;
                if(isset($novedadesSancion[$contadorInterno])){
                    $entrar = true;
                    $arrayFila[23] = "X";
                    $arrayFila[85] = date("Y-m-d",strtotime($novedadesSancion[$contadorInterno]->fechaInicio));
                    $arrayFila[86] = date("Y-m-d",strtotime($novedadesSancion[$contadorInterno]->fechaFin));

                }
                
                if(isset($novedadesIncapacidadNoLab[$contadorInterno])){
                    $entrar = true;
                    $arrayFila[24] = "X";
                    $arrayFila[87] = date("Y-m-d",strtotime($novedadesIncapacidadNoLab[$contadorInterno]->fechaRealI));
                    $arrayFila[88] = date("Y-m-d",strtotime($novedadesIncapacidadNoLab[$contadorInterno]->fechaRealF));

                }
                if(isset($novedadesIncapacidadNoLaMat[$contadorInterno])){
                    $entrar = true;
                    $arrayFila[25] = "X";
                    $arrayFila[89] = date("Y-m-d",strtotime($novedadesIncapacidadNoLaMat[$contadorInterno]->fechaRealI));
                    $arrayFila[90] = date("Y-m-d",strtotime($novedadesIncapacidadNoLaMat[$contadorInterno]->fechaRealF));
                }
                if(isset($novedadesVac[$contadorInterno])){
                    $entrar = true;
                    $arrayFila[26] = "X";
                    $arrayFila[91] = date("Y-m-d",strtotime($novedadesVac[$contadorInterno]->fechaInicio));
                    $arrayFila[92] = date("Y-m-d",strtotime($novedadesVac[$contadorInterno]->fechaFin));
                }
                if(isset($novedadesIncapacidadLab[$contadorInterno])){
                    $entrar = true;
                    $arrayFila[29] = "X";
                    $arrayFila[93] = date("Y-m-d",strtotime($novedadesIncapacidadLab[$contadorInterno]->fechaRealI));
                    $arrayFila[94] = date("Y-m-d",strtotime($novedadesIncapacidadLab[$contadorInterno]->fechaRealF));
                }
            
                
                if($entrar){
                    $arrayFila = $this->upperCaseAllArray($arrayFila);
                    array_push($arrDatos, $arrayFila);    
                    $incremental++;
                    $contadorInterno ++;    
                    $arrayFila[1] = $incremental;
                    $arrayFila[23] = "";
                    $arrayFila[24] = "";
                    $arrayFila[25] = "";
                    $arrayFila[26] = "";
                    $arrayFila[29] = "";
                    $arrayFila[14] = "";
                    $arrayFila[15] = "";
                    $arrayFila[16] = "";
                    $arrayFila[17] = "";
                    $arrayFila[18] = "";
                    $arrayFila[19] = "";
                    $arrayFila[20] = "";   
                    $arrayFila[21] = "";
                    $arrayFila[22] = "";
                    $arrayFila[79] = "";
                    $arrayFila[80] = "";
                    $arrayFila[81] = "";
                    $arrayFila[82] = "";
                    $arrayFila[83] = "";
                    $arrayFila[84] = "";
                    $arrayFila[85] = "";
                    $arrayFila[86] = "";
                    $arrayFila[87] = "";
                    $arrayFila[88] = "";
                    $arrayFila[89] = "";
                    $arrayFila[90] = "";
                    $arrayFila[91] = "";
                    $arrayFila[92] = "";
                    $arrayFila[93] = "";
                    $arrayFila[94] = "";
                }
            }

            if($contadorInterno==0){
                $incremental++;
                $arrayFila = $this->upperCaseAllArray($arrayFila);
                array_push($arrDatos, $arrayFila);    
            }
            
            
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=Informe_ss_'.$idLiquidacion.'.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($arrDatos);
        $csv->output('Informe_ss_'.$idLiquidacion.'.csv');

    }
    public function verDetalleVacacion($idItemBoucherPago){

        $itemBoucherPago = DB::table("item_boucher_pago","ibp")
        ->select("ln.*", "ibp.fkBoucherPago")
        ->join("boucherpago as bp", "bp.idBoucherPago","=","ibp.fkBoucherPago")
        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina", "=","bp.fkLiquidacion")
        ->where("ibp.idItemBoucherPago","=",$idItemBoucherPago)->first();
        


        /*$vacaciones = DB::table("item_boucher_pago_novedad","ibpn")
        ->select("v.*")
        ->join("novedad as n", "n.idNovedad","=","ibpn.fkNovedad")
        ->join("vacaciones as v", "v.idVacaciones","=","n.fkVacaciones")
        ->where("ibpn.fkItemBoucher","=",$idItemBoucherPago)->first();*/

        $empleado = DB::table("empleado", "e")
        ->select("e.*","ccfijo.valor as valorSalario")
        ->join("conceptofijo as ccfijo","ccfijo.fkEmpleado", "=", "e.idempleado")
        ->join("boucherpago as bp", "bp.fkEmpleado","=","e.idempleado")
        ->whereIn("ccfijo.fkConcepto",["1","2"])
        ->where("idBoucherPago","=",$itemBoucherPago->fkBoucherPago)->first();





        $fechaInicio = $empleado->fechaIngreso;
        $fechaFinGen = $itemBoucherPago->fechaFin;
        


        
        $entrar=true;
        $periodo = 1;

        //Dias trabajados en este periodo


        //Obtener la primera liquidacion de nomina de la persona 
        $primeraLiquidacion = DB::table("liquidacionnomina", "ln")
        ->selectRaw("min(ln.fechaInicio) as primeraFecha")
        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
        ->where("bp.fkEmpleado","=",$empleado->idempleado)->first();

        $minimaFecha = date("Y-m-d");
        
        if(isset($primeraLiquidacion)){
            $minimaFecha = $primeraLiquidacion->primeraFecha;
        }
        $diasAgregar = 0;
        //Verificar si dicha nomina es menor a la fecha de ingreso
        if(strtotime($empleado->fechaIngreso) < strtotime($minimaFecha)){
            $diasAgregar = $this->days_360($empleado->fechaIngreso, $minimaFecha);
        }
        $liquidacionesMesesAnterioresCompleta = DB::table("liquidacionnomina", "ln")
        ->selectRaw("sum(bp.periodoPago) as periodPago, sum(bp.salarioPeriodoPago) as salarioPago")
        ->join("boucherpago as bp","bp.fkLiquidacion","=","ln.idLiquidacionNomina")                
        ->where("bp.fkEmpleado","=",$empleado->idempleado)
        ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6"])         
        ->first();

        
        $diasTrabajados = $this->days_360($fechaInicio, $fechaFinGen);
        //$diasTrabajados = $diasAgregar + (isset($liquidacionesMesesAnterioresCompleta->periodPago) ? $liquidacionesMesesAnterioresCompleta->periodPago : 0);
        

        $novedadesLIC = DB::table("novedad","n")
        ->selectRaw("sum(a.cantidadDias) as suma")
        ->join("ausencia as a","a.idAusencia","=","n.fkAusencia")
        ->where("n.fkEmpleado","=",$empleado->idempleado)
        ->whereIn("n.fkEstado",["8"]) // Pagada -> no que este eliminada
        ->whereBetween("n.fechaRegistro",[$fechaInicio, $fechaFinGen])
        ->where("a.cantidadDias",">","0")
        ->whereNotNull("n.fkAusencia")
        ->first();

        $diasNeto = ($diasTrabajados - (isset($novedadesLIC->suma) ? $novedadesLIC->suma : 0));
        
        
        $diasVacGen = $diasNeto * 15 / 360;
        


        $novedadesVacacionGen = DB::table("novedad","n")
        ->selectRaw("sum(v.diasCompletos) as suma")
        ->join("vacaciones as v","v.idVacaciones","=","n.fkVacaciones")
        ->where("n.fkEmpleado","=",$empleado->idempleado)
        ->whereIn("n.fkEstado",["8"]) // Pagada -> no que este eliminada
        ->whereBetween("n.fechaRegistro",[$fechaInicio, $fechaFinGen])
        ->whereNotNull("n.fkVacaciones")
        ->first();
        
        
        $arrDatos = array();
        $rowspan = 1;
        while($entrar){
            $arrFila = array();
            $fechaFinInt = date("Y-m-d",strtotime($fechaInicio." +1 year"));
            if(strtotime($fechaFinGen) < strtotime($fechaFinInt)){
                $fechaFinInt = $fechaFinGen;
            }
            $periodoPagoVac = $this->days_360($fechaInicio, $fechaFinInt);
            
            //Proceso de vacaciones
            //Con esos dias calcular los que me pertenecen en vacaciones
            $diasVac = $periodoPagoVac * 15 / 360;
            //Cargar en este periodo las vacaciones tomadas
            $novedadesVacacion = DB::table("novedad","n")
            ->join("vacaciones as v","v.idVacaciones","=","n.fkVacaciones")
            ->where("n.fkEmpleado","=",$empleado->idempleado)
            ->whereIn("n.fkEstado",["8"]) // Pagada -> no que este eliminada
            ->whereBetween("n.fechaRegistro",[$fechaInicio, $fechaFinInt])
            ->whereNotNull("n.fkVacaciones")
            ->get();
            $diasTomadosPeriodo = 0;
            $arrFila['disfrute'] = array();
            foreach($novedadesVacacion as $novedadVacacion){
                $arrFilaInt = array();
                $arrFilaInt['diaIni'] = (isset($novedadVacacion->fechaInicio) ? date("d/m/Y",strtotime($novedadVacacion->fechaInicio)) : "");
                $arrFilaInt['diaFin'] = (isset($novedadVacacion->fechaFin) ? date("d/m/Y",strtotime($novedadVacacion->fechaFin)) : "");
                $arrFilaInt['diaTom'] = $novedadVacacion->diasCompletos;
                array_push($arrFila['disfrute'], $arrFilaInt);
                $diasTomadosPeriodo = $diasTomadosPeriodo + $novedadVacacion->diasCompletos;    
            }
            $rowspan = $rowspan + (sizeof($novedadesVacacion) > 0 ? (sizeof($novedadesVacacion) - 1) : 0);
            $diasPendientesPeriodo = $diasVac - $diasTomadosPeriodo;                
            $arrFila['periodo'] = $periodo;
            $arrFila['fechaInicio'] = $fechaInicio;
            $arrFila['fechaFinInt'] = $fechaFinInt;
            $arrFila['diaCau'] = $diasVac;
            $arrFila['diaTom'] = $diasTomadosPeriodo;
            $arrFila['diaPen'] = $diasPendientesPeriodo;
            array_push($arrDatos, $arrFila);
            //Restar dias que estuvo en vacacion en ese periodo y colocar los dias pendientes en el periodo

            if(strtotime($fechaFinGen) == strtotime($fechaFinInt)){
                $entrar=false;
            }
            else{
                $fechaInicio = $fechaFinInt;
                $periodo++;
                $rowspan++;

            }
        }



        return view('/nomina.solicitudes.verDetalleVacacion', [
            "arrDatos" => $arrDatos,
            "fechaFinGen" => $fechaFinGen,
            "empleado" => $empleado,
            "diasTrabajados" => $diasTrabajados,
            "diasLic" => (isset($novedadesLIC->suma) ? $novedadesLIC->suma : 0),
            "diasNeto" => $diasNeto,
            "diasVacGen" => round($diasVacGen,2),
            "novedadesVacacionGen" => $novedadesVacacionGen
        ]);        
    }


    public function normalize ($string) {
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
        );
    
        return strtr($string, $table);
    }
    public function upperCaseAllArray($array){
        foreach($array as $key => $value){
            $array[$key] = strtoupper($value);
            $array[$key] = $this->normalize($array[$key]);

            
            
        }
        return $array;
    }
    public function days_360($fecha1,$fecha2,$europeo=true) {
        //try switch dates: min to max
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
    public function roundSup($numero, $presicion){
        $redondeo = $numero / pow(10,$presicion*-1);
        $redondeo = ceil($redondeo);
        $redondeo = $redondeo * pow(10,$presicion*-1);
        return $redondeo;
    }
}