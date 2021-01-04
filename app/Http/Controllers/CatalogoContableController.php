<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use League\Csv\Reader;
use Illuminate\Support\Facades\Storage;
use SplTempFileObject;          


class CatalogoContableController extends Controller
{
    public function index(Request $req){

        $catalogo = DB::table("catalgocontable", "cc")
        ->select("cc.*","tc.nombre as tipoTercero_nm","e.razonSocial as empresa_nm","c.nombre as centroCosto_nm")
        ->join("tercero as t", "t.idTercero", "=","cc.fkTercero", "left")
        ->join("empresa as e", "e.idempresa", "=","cc.fkEmpresa", "left")
        ->join("centrocosto as c", "c.idcentroCosto", "=","cc.fkCentroCosto", "left")
        ->join("tipotercerocuenta as tc", "tc.idTipoTerceroCuenta", "=","cc.fkTipoTercero", "left");
        if(isset($req->idempresa)){
            $catalogo = $catalogo->where("e.idempresa","=",$req->idempresa);
        }
        if(isset($req->idcentroCosto)){
            $catalogo = $catalogo->where("c.idcentroCosto","=",$req->idcentroCosto);
        }
        if(isset($req->descripcion)){
            $catalogo = $catalogo->where("cc.descripcion","LIKE","%".$req->descripcion."%");
        }
        $catalogo = $catalogo->paginate(15);
        
        
        $empresas = DB::table("empresa")->orderBy("razonSocial")->get();
        $centros_costos = array();
        if(isset($req->idempresa)){
            $centros_costos = DB::table("centrocosto")->where("fkEmpresa","=",$req->idempresa)->orderBy("nombre")->get();
        }
        $arrConsulta = ["idempresa" => $req->idempresa, "idcentroCosto" => $req->idcentroCosto, "descripcion" => $req->descripcion];
        return view('/catalogoContable.index',
            [
                "catalogo" => $catalogo,
                "req" => $req,
                "empresas" => $empresas,
                "centros_costos" => $centros_costos,
                "arrConsulta" => $arrConsulta
            ]
        );
    }
    public function getFormAdd(){
        $terceros = DB::table("tercero")->get();
        $tipoTerceroCuenta = DB::table("tipotercerocuenta")->get();
        $empresas = DB::table("empresa")->get();
        $gruposConcepto  = DB::table("grupoconcepto")->get();


        return view('/catalogoContable.formAdd',
            [
                "terceros" => $terceros, 
                "empresas" => $empresas,
                "tipoTerceroCuenta" => $tipoTerceroCuenta,
                "gruposConcepto" => $gruposConcepto
            ]
        );
    }
    public function getFormEdit($idCatalgoContable){

        $catalogo = DB::table("catalgocontable","cc")->where("idCatalgoContable","=",$idCatalgoContable)->first();
        $terceros = DB::table("tercero")->orderBy("razonSocial")->get();
        $tipoTerceroCuenta = DB::table("tipotercerocuenta")->orderBy("nombre")->get();
        $empresas = DB::table("empresa")->orderBy("razonSocial")->get();
        $gruposConcepto  = DB::table("grupoconcepto")->orderBy("nombre")->get();
        $centrosCosto = DB::table("centrocosto")
        ->where("fkEmpresa","=",$catalogo->fkEmpresa)
        ->get();
        $datoscuenta = DB::table("datoscuenta")->where("fkCuenta","=",$idCatalgoContable)->get();
        
        return view('/catalogoContable.formEdit',
            [
                "catalogo" => $catalogo,
                "terceros" => $terceros, 
                "empresas" => $empresas,
                "tipoTerceroCuenta" => $tipoTerceroCuenta,
                "gruposConcepto" => $gruposConcepto,
                "centrosCosto" => $centrosCosto,
                "datoscuenta" => $datoscuenta
            ]
        );
    }
    public function getCentrosCosto($fkEmpresa){
        $centrosCosto = DB::table("centrocosto")
        ->where("fkEmpresa","=",$fkEmpresa)
        ->get();

        $html="";
        foreach($centrosCosto as $centroCosto){
            $html.="<option value='".$centroCosto->idcentroCosto."'>".$centroCosto->id_uni_centro." - ".$centroCosto->nombre."</option>";
        }
        return response()->json([
            "success" => true,
            "html" => $html
        ]);
    }
    public function getGrupos($num){
        $gruposConcepto  = DB::table("grupoconcepto")->get();
        return view('/catalogoContable.gruposCuenta', [
            "gruposConcepto" => $gruposConcepto,
             "num" => $num
        ]);
        
    }
    


    public function crear(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'descripcion' => 'required',
            'cuenta' => 'required',
            'fkTipoTercero' => 'required',
            'tablaConsulta' => 'required',
            'fkEmpresa' => 'required'
            

        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $arrCatalogo = [
            "descripcion" => $req->descripcion,
            "cuenta" => $req->cuenta,
            "fkTipoTercero" => $req->fkTipoTercero,
            "fkTercero" => $req->fkTercero,
            "fkEmpresa" => $req->fkEmpresa,
            "fkCentroCosto" => $req->fkCentroCosto,
            "tipoComportamiento" => "1",
            "transaccion" => $req->transaccion
        ];
        $idCatalgoContable = DB::table("catalgocontable")->insertGetId($arrCatalogo,"idCatalgoContable");
        foreach($req->tablaConsulta as $row => $tablaConsulta){
            if($tablaConsulta == 1){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCatalgoContable,
                    "fkGrupoConcepto" => $req->fkGrupoConcepto[$row]
                ]);
            }
            else if($tablaConsulta == 2){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCatalgoContable,
                    "subTipoConsulta" => $req->subTipoProvision[$row]
                ]);
                
            }
            else if($tablaConsulta == 3){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCatalgoContable,
                    "subTipoConsulta" => $req->subTipoAporteEmpleador[$row]
                ]);
            }
        }
        return response()->json(["success" => true]);
    }

    public function modificar(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            'descripcion' => 'required',
            'cuenta' => 'required',
            'fkTipoTercero' => 'required',
            'tablaConsulta' => 'required',
            'fkEmpresa' => 'required'
            

        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $arrCatalogo = [
            "descripcion" => $req->descripcion,
            "cuenta" => $req->cuenta,
            "fkTipoTercero" => $req->fkTipoTercero,
            "fkTercero" => $req->fkTercero,
            "fkEmpresa" => $req->fkEmpresa,
            "fkCentroCosto" => $req->fkCentroCosto,
            "tipoComportamiento" => "1",
            "transaccion" => $req->transaccion
        ];
        $idCatalgoContable = $req->idCatalgoContable;
        DB::table("catalgocontable")->where("idCatalgoContable","=",$idCatalgoContable)->update($arrCatalogo);
        DB::table("datoscuenta")->where("fkCuenta","=",$idCatalgoContable)->delete();

        foreach($req->tablaConsulta as $row => $tablaConsulta){
            if($tablaConsulta == 1){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCatalgoContable,
                    "fkGrupoConcepto" => $req->fkGrupoConcepto[$row]
                ]);
            }
            else if($tablaConsulta == 2){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCatalgoContable,
                    "subTipoConsulta" => $req->subTipoProvision[$row]
                ]);
                
            }
            else if($tablaConsulta == 3){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCatalgoContable,
                    "subTipoConsulta" => $req->subTipoAporteEmpleador[$row]
                ]);
            }
        }
        return response()->json(["success" => true]);
    }
    
    public function reporteNominaIndex(){

        $empresas = DB::table("empresa", "e")->get();
        
        return view('/catalogoContable.reporteNominaIndex',
            ["empresas" => $empresas]
        );
    }

    
    public function generarReporteNomina(Request $req){

        $fechaInicioMes = date("Y-m-01", strtotime($req->fechaReporte));
        $fechaFinMes = date("Y-m-t", strtotime($fechaInicioMes));

        $arrConceptosAjustePeso = [18,19,33];
        

        $empleados = DB::table("empleado", "e")
        ->select("e.*",
                 "dp.primerNombre",
                 "dp.primerApellido",
                 "dp.segundoNombre",
                 "dp.segundoApellido", 
                 "dp.numeroIdentificacion", 
                 "ti.nombre as tipoidentificacion"
                )
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","dp.fkTipoIdentificacion")
        ->join("nomina as n", "n.idNomina", "=","e.fkNomina")
        ->join("boucherpago as bp", "bp.fkEmpleado", "=","e.idempleado")
        ->join("liquidacionnomina as ln", "ln.idLiquidacionNomina", "=","bp.fkLiquidacion")
        ->where("n.fkEmpresa","=",$req->empresa)
        ->whereBetween("ln.fechaLiquida",[$fechaInicioMes, $fechaFinMes])
        ->distinct()
        ->get();
        $arrSalida = array();
        

        foreach($empleados as $empleado){
            $arrayInt = array();
            $centrosCostoEmpleado = DB::table("distri_centro_costo_centrocosto", "ddc")
            ->join("centrocosto as cec", "cec.idcentroCosto", "=","ddc.fkCentroCosto")
            ->join("distri_centro_costo as dc", "dc.id_distri_centro_costo", "=", "ddc.fkDistribucion")
            ->where("ddc.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("'".$fechaInicioMes."' BETWEEN dc.fechaInicio and dc.fechaFin")
            ->whereRaw("'".$fechaFinMes."' BETWEEN dc.fechaInicio and dc.fechaFin")
            ->get();
            $arrCentrosCosto = array();

            if(sizeof($centrosCostoEmpleado) > 0){
                foreach($centrosCostoEmpleado as $centroCostoEmpleado ){
                    array_push($arrCentrosCosto, [
                        "id_unico" => $centroCostoEmpleado->id_uni_centro,
                        "nombre" => $centroCostoEmpleado->nombre,
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
                        "id_unico" => "",
                        "nombre" => "",
                        "centroCosto" => $centroCosto->fkCentroCosto,
                        "porcentaje" => $centroCosto->porcentajeTiempoTrabajado
                    ]);
                }
            }
            

      
            foreach($arrCentrosCosto as $arrCentroCosto){
                
                //Consular por tipo la cuenta
                $datosCuentaTipo1 = DB::table("datoscuenta", "dc")
                ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                ->where("dc.tablaConsulta","=","1")
                ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                ->whereRaw("(cc.fkCentroCosto is NULL or cc.fkCentroCosto = ".$arrCentroCosto["centroCosto"].")")
                ->orderBy("cc.fkCentroCosto")
                ->get();
                
                foreach($datosCuentaTipo1 as $datoCuentaTipo1){
                    /*if(!isset($datoCuentaTipo1->fkCentroCosto)){
                        $arrCentroCosto["porcentaje"] = 100;
                    }*/
                   
                    $itemsBoucher = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.pago, ibp.descuento, con.idconcepto, ibp.valor, con.nombre as con_nombre, con.fkNaturaleza as con_naturaleza")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
                    ->join("concepto as con","con.idConcepto","=","ibp.fkConcepto") 
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->whereRaw("(MONTH(ln.fechaInicio) = MONTH('".$req->fechaReporte."') and YEAR(ln.fechaInicio) = YEAR('".$req->fechaReporte."'))")
                    ->where("gcc.fkGrupoConcepto","=",$datoCuentaTipo1->fkGrupoConcepto) 
                    ->get();
            
                    foreach($itemsBoucher as $itemBoucher){
                        
                        $valor = 0;
                        $tipoReg = $datoCuentaTipo1->tipoCuenta;

                        $tipoRegDif = $datoCuentaTipo1->tipoCuenta;

                        $diferencia = 0;
                        if(in_array($itemBoucher->idconcepto,$arrConceptosAjustePeso)){
                            $diferencia = $this->roundSup($itemBoucher->valor, -2) - $itemBoucher->valor;
                        }

                        //$itemBoucher->valor = ; 
                        
                        if($itemBoucher->valor < 0 && $itemBoucher->con_naturaleza=="1"){
                            if($tipoReg == "CREDITO"){
                                $tipoReg = "DEBITO";
                            }
                            else{
                                $tipoReg = "CREDITO";
                            }
                            $valor = $itemBoucher->valor*-1;
                        }
                        else if($itemBoucher->valor > 0 && $itemBoucher->con_naturaleza=="1"){
                            $valor = $itemBoucher->valor;
                        }
                        else if($itemBoucher->valor > 0 && $itemBoucher->con_naturaleza=="3"){
                            $valor = $itemBoucher->valor;
                            if($tipoReg == "CREDITO"){
                                $tipoReg = "DEBITO";
                            }
                            else{
                                $tipoReg = "CREDITO";
                            }

                        }
                        else if($itemBoucher->valor < 0 && $itemBoucher->con_naturaleza=="3"){
                            $valor = $itemBoucher->valor*-1;
                        }  
                        


                        if($diferencia != 0){
                            if($diferencia < 0 && $itemBoucher->con_naturaleza=="1"){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }
                                $diferencia = $diferencia*-1;
                            }
                            else if($diferencia > 0 && $itemBoucher->con_naturaleza=="3"){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }    
                            }
                            else if($diferencia < 0 && $itemBoucher->con_naturaleza=="3"){
                                $diferencia = $diferencia*-1;
                            }    
                        }
                        


                        $val = true;
                        if(!isset( $arrayInt[1][$datoCuentaTipo1->cuenta])){
                            $arrayInt[1][$datoCuentaTipo1->cuenta] = array();
                        }
                        else{
                            $porcentajeInterno = 0;
                            foreach($arrayInt[1][$datoCuentaTipo1->cuenta] as $arrCuentaInt2){
                                if($arrCuentaInt2["idConcepto"] == $itemBoucher->idconcepto){
                                    $porcentajeInterno = $porcentajeInterno + $arrCuentaInt2["porcentaje"];
                                }
                                
                            }
                            if($porcentajeInterno > 100){
                                $val = true;
                            }
                        }
                        
                        
                        if($val){
                            $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                         
                            array_push($arrayInt[1][$datoCuentaTipo1->cuenta], 
                                array(
                                    "arrCentrosCosto" => $arrCentroCosto,
                                    "empleado" => $empleado,
                                    "tablaConsulta" => "1",
                                    "cuenta" => $datoCuentaTipo1->cuenta,
                                    "descripcion" => $datoCuentaTipo1->descripcion,
                                    "transaccion" => $datoCuentaTipo1->transaccion,
                                    "porcentaje" => $arrCentroCosto["porcentaje"],
                                    "valor" => round($valor),
                                    "tipoReg" => $tipoReg,
                                    "idConcepto" => $itemBoucher->idconcepto,
                                    "nombreConcepto" => $itemBoucher->con_nombre,
                                    "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo1->fkTipoTercero, $empleado, $datoCuentaTipo1->fkTercero)
                                )
                            );

                            
                        }
                    }                   
                }
                
                $datosCuentaTipo2 = DB::table("datoscuenta", "dc")
                ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                ->where("dc.tablaConsulta","=","2")
                ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                ->whereRaw("(cc.fkCentroCosto is NULL or cc.fkCentroCosto = ".$arrCentroCosto["centroCosto"].")")
                ->orderBy("cc.fkCentroCosto")
                ->get();
            
                
                foreach($datosCuentaTipo2 as $datoCuentaTipo2){
          
                    $fkConcepto = 0;
                    if($datoCuentaTipo2->subTipoConsulta == "1"){
                        $fkConcepto = "73";
                    }
                    else if($datoCuentaTipo2->subTipoConsulta == "2"){
                        $fkConcepto = "71";
                    }
                    else if($datoCuentaTipo2->subTipoConsulta == "3"){
                        $fkConcepto = "72";
                    }
                    else if($datoCuentaTipo2->subTipoConsulta == "4"){
                        $fkConcepto = "74";
                    }
                    

                    $provision = DB::table("provision","p")
                    ->where("p.fkEmpleado","=",$empleado->idempleado)
                    ->where("p.fkConcepto","=",$fkConcepto)
                    ->whereRaw("(p.mes = MONTH('".$req->fechaReporte."') and p.anio= YEAR('".$req->fechaReporte."'))")
                    ->first();
                    



                    $valor = $provision->valor;   
                    //$valor = $this->roundSup($valor, -2); 
                    $tipoReg = $this->comportamientoPorNaturaleza($datoCuentaTipo2->cuenta);

                    if($valor < 0){
                        if($tipoReg == "CREDITO"){
                            $tipoReg = "DEBITO";
                        }
                        else{
                            $tipoReg = "CREDITO";
                        }
                        $valor = $valor*-1;
                    }
                    
                    
                    $val = true;
                    if(!isset( $arrayInt[2][$datoCuentaTipo2->cuenta])){
                        $arrayInt[2][$datoCuentaTipo2->cuenta] = array();
                    }
                    else{
                        $porcentajeInterno = 0;
                        foreach($arrayInt[2][$datoCuentaTipo2->cuenta] as $arrCuentaInt2){
                            if($arrCuentaInt2["idConcepto"] == $fkConcepto){
                                $porcentajeInterno = $porcentajeInterno + $arrCuentaInt2["porcentaje"];
                            }
                            
                        }
                        if($porcentajeInterno > 100){
                            $val = true;
                        }

                    }

                    
                    
                    if($val){
                        $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                        
                        array_push($arrayInt[2][$datoCuentaTipo2->cuenta], 
                            array(
                                "arrCentrosCosto" => $arrCentroCosto,
                                "empleado" => $empleado,
                                "tablaConsulta" => "2",
                                "cuenta" => $datoCuentaTipo2->cuenta,
                                "descripcion" => $datoCuentaTipo2->descripcion,
                                "transaccion" => $datoCuentaTipo2->transaccion,
                                "porcentaje" => $arrCentroCosto["porcentaje"],
                                "valor" => round($valor),
                                "tipoReg" => $tipoReg,
                                "idConcepto" => $fkConcepto,
                                "nombreConcepto" => "",
                                "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo2->fkTipoTercero, $empleado, $datoCuentaTipo2->fkTercero)
                            )
                        );
          
                    }
                    

                }
               
                $datosCuentaTipo3 = DB::table("datoscuenta", "dc")
                ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                ->where("dc.tablaConsulta","=","3")
                ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                ->whereRaw("(cc.fkCentroCosto is NULL or cc.fkCentroCosto = ".$arrCentroCosto["centroCosto"].")")
                ->orderBy("cc.fkCentroCosto")
                ->get();
                

                foreach($datosCuentaTipo3 as $datoCuentaTipo3){
                    $parafiscales = DB::table("parafiscales", "para")
                    ->selectRaw("para.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","para.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->whereRaw("(MONTH(ln.fechaInicio) = MONTH('".$req->fechaReporte."') and YEAR(ln.fechaInicio) = YEAR('".$req->fechaReporte."'))")
                    ->orderBy("idParafiscales","desc")
                    ->limit("1")
                    ->get();

                    $valor = 0;
                    if(!isset( $arrayInt[3][$datoCuentaTipo3->cuenta])){
                        $arrayInt[3][$datoCuentaTipo3->cuenta] = array();
                    }  
                    if($datoCuentaTipo3->subTipoConsulta == "1"){
                        foreach($parafiscales as $parafiscal){
                            $valor = $parafiscal->afp;
                            $tipoReg = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
        
                            if($valor < 0){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }
                                $valor = $valor*-1;
                            }
                    
                            
                            $val = true; 
                                                       
                            if($val){
                                $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($valor),
                                        "tipoReg" => $tipoReg,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }
                        }                        
                    }
                    else if($datoCuentaTipo3->subTipoConsulta == "2"){
                        foreach($parafiscales as $parafiscal){

                            $valor = $parafiscal->eps;
                            $tipoReg = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
        
                            if($valor < 0){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }
                                $valor = $valor*-1;
                            }

                            $val = true;                            
                            if($val){
                                $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($valor),
                                        "tipoReg" => $tipoReg,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }
                        }                        
                    }
                    else if($datoCuentaTipo3->subTipoConsulta == "3"){
                        foreach($parafiscales as $parafiscal){
                            
                            $valor = $parafiscal->arl;
                            $tipoReg = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
                            //$valor = $this->roundSup($valor, -2); 
                            if($valor < 0){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }
                                $valor = $valor*-1;
                            }


                            $val = true;                            
                            if($val){
                                $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($valor),
                                        "tipoReg" => $tipoReg,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }
                        }                        
                    }
                    else if($datoCuentaTipo3->subTipoConsulta == "4"){
                        foreach($parafiscales as $parafiscal){
                            

                            $valor = $parafiscal->ccf;
                            $tipoReg = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
                            //$valor = $this->roundSup($valor, -2); 
                            if($valor < 0){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }
                                $valor = $valor*-1;
                            }

                            $val = true;                            
                            if($val){
                                $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($valor),
                                        "tipoReg" => $tipoReg,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }
                        }                        
                    }
                    else if($datoCuentaTipo3->subTipoConsulta == "5"){
                        foreach($parafiscales as $parafiscal){

                            $valor = $parafiscal->icbf;
                            $tipoReg = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
                            //$valor = $this->roundSup($valor, -2); 
                            if($valor < 0){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }
                                $valor = $valor*-1;
                            }

                            $val = true;                            
                            if($val){
                                $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($valor),
                                        "tipoReg" => $tipoReg,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }
                        }                        
                    }
                    else if($datoCuentaTipo3->subTipoConsulta == "6"){
                        foreach($parafiscales as $parafiscal){
                            
                            $valor = $parafiscal->sena;
                            $tipoReg = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
                            //$valor = $this->roundSup($valor, -2); 
                            if($valor < 0){
                                if($tipoReg == "CREDITO"){
                                    $tipoReg = "DEBITO";
                                }
                                else{
                                    $tipoReg = "CREDITO";
                                }
                                $valor = $valor*-1;
                            }
                            $val = true;                            
                            if($val){
                                $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($valor),
                                        "tipoReg" => $tipoReg,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }
                        }                        
                    }


                }

                //Consular por tipo la cuenta
                $datosCuentaTipo4 = DB::table("datoscuenta", "dc")
                ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                ->where("dc.tablaConsulta","=","4")
                ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                ->whereRaw("(cc.fkCentroCosto is NULL or cc.fkCentroCosto = ".$arrCentroCosto["centroCosto"].")")
                ->orderBy("cc.fkCentroCosto")
                ->get();
                
                foreach($datosCuentaTipo4 as $datoCuentaTipo4){
                    /*if(!isset($datoCuentaTipo4->fkCentroCosto)){
                        $arrCentroCosto["porcentaje"] = 100;
                    }*/
                    $itemsBoucher = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.pago, ibp.descuento, con.idconcepto, ibp.valor, con.nombre as con_nombre, con.fkNaturaleza as con_naturaleza")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("concepto as con","con.idConcepto","=","ibp.fkConcepto") 
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->whereRaw("(MONTH(ln.fechaInicio) = MONTH('".$req->fechaReporte."') and YEAR(ln.fechaInicio) = YEAR('".$req->fechaReporte."'))")
                    ->where("ibp.fkConcepto","=",$datoCuentaTipo4->fkConcepto) 
                    ->get();
            
                    foreach($itemsBoucher as $itemBoucher){
                        
                        $valor = 0;
                        $tipoReg = $datoCuentaTipo4->tipoCuenta;
                        $diferencia = $this->roundSup($itemBoucher->valor, -2) - $itemBoucher->valor;
                        //$itemBoucher->valor = $this->roundSup($itemBoucher->valor, -2); 
                        


                        if($itemBoucher->valor < 0 && $itemBoucher->con_naturaleza=="1"){
                            if($tipoReg == "CREDITO"){
                                $tipoReg = "DEBITO";
                            }
                            else{
                                $tipoReg = "CREDITO";
                            }
                            $valor = $itemBoucher->valor*-1;
                        }
                        else if($itemBoucher->valor > 0 && $itemBoucher->con_naturaleza=="1"){
                            $valor = $itemBoucher->valor;
                        }
                        else if($itemBoucher->valor > 0 && $itemBoucher->con_naturaleza=="3"){
                            $valor = $itemBoucher->valor;
                            if($tipoReg == "CREDITO"){
                                $tipoReg = "DEBITO";
                            }
                            else{
                                $tipoReg = "CREDITO";
                            }

                        }
                        else if($itemBoucher->valor < 0 && $itemBoucher->con_naturaleza=="3"){
                            $valor = $itemBoucher->valor*-1;
                        }  
                        

                        $val = true;
                        if(!isset( $arrayInt[4][$datoCuentaTipo4->cuenta])){
                            $arrayInt[4][$datoCuentaTipo4->cuenta] = array();
                        }
                        else{
                            $porcentajeInterno = 0;
                            foreach($arrayInt[4][$datoCuentaTipo4->cuenta] as $arrCuentaInt2){
                                if($arrCuentaInt2["idConcepto"] == $itemBoucher->idconcepto){
                                    $porcentajeInterno = $porcentajeInterno + $arrCuentaInt2["porcentaje"];
                                }
                                
                            }
                            if($porcentajeInterno > 100){
                                $val = true;
                            }

                        }
                        
                        
                        if($val){
                            $valor = $valor * ($arrCentroCosto["porcentaje"]/100);
                         
                            array_push($arrayInt[4][$datoCuentaTipo4->cuenta], 
                                array(
                                    "arrCentrosCosto" => $arrCentroCosto,
                                    "empleado" => $empleado,
                                    "tablaConsulta" => "1",
                                    "cuenta" => $datoCuentaTipo4->cuenta,
                                    "descripcion" => $datoCuentaTipo4->descripcion,
                                    "transaccion" => $datoCuentaTipo4->transaccion,
                                    "porcentaje" => $arrCentroCosto["porcentaje"],
                                    "valor" => round($valor),
                                    "tipoReg" => $tipoReg,
                                    "idConcepto" => $itemBoucher->idconcepto,
                                    "nombreConcepto" => $itemBoucher->con_nombre,
                                    "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo4->fkTipoTercero, $empleado, $datoCuentaTipo4->fkTercero)
                                )
                            );
                        }
                    }     


                }
              
               
            }
            
            array_push($arrSalida, $arrayInt);

            //Add a global

        }
        
        $arrDef = array(
            [
                "Fecha",
                "Cuenta",
                "Descripción Cuenta",
                "Concepto",
                "Centro Beneficio Id",
                "Centro Beneficio Nom",
                "Porcentaje",
                "Tercero",
                "Dígito Verificación",
                "Descripción Tercero",
                "Transaccion",
                "Tercero Empleado",
                "Nombre Tercero Empleado",
                "Valor Débito",
                "Valor Crédito"
            ]
        );
        $mesInforme = date("m",strtotime($req->fechaReporte));
        $fechaInforme = date("Y-m-",strtotime($req->fechaReporte));
        if($mesInforme == "2"){
            $fechaInforme = date("Y-m-",strtotime($req->fechaReporte)).date("t",strtotime($req->fechaReporte));
        }
        else{
            $fechaInforme = date("Y-m-",strtotime($req->fechaReporte))."30";
        }
        foreach($arrSalida as $arrSalid){
            foreach($arrSalid as $arrSalid2){
                foreach($arrSalid2 as $arrSalid3){                    
                    foreach($arrSalid3 as $arrSalid4){
                        $valorDebito = "0";
                        $valorCredito = "0";
                        if($arrSalid4["tipoReg"]=="DEBITO"){
                            $valorDebito = $arrSalid4["valor"];
                        }
                        else{
                            $valorCredito = $arrSalid4["valor"];
                        }
                        
                        if($arrSalid4["valor"] != 0){
                            $arrDefInt = [
                                $fechaInforme,
                                $arrSalid4["cuenta"],
                                $arrSalid4["descripcion"],
                                $arrSalid4["nombreConcepto"],
                                $arrSalid4["arrCentrosCosto"]["id_unico"],
                                $arrSalid4["arrCentrosCosto"]["nombre"],
                                $arrSalid4["porcentaje"],
                                $arrSalid4["tercero"]["idTercero"],
                                $arrSalid4["tercero"]["digitoVer"],
                                $arrSalid4["tercero"]["nomTercero"],
                                $arrSalid4["transaccion"],
                                $arrSalid4["empleado"]->numeroIdentificacion,
                                $arrSalid4["empleado"]->primerApellido." ".$arrSalid4["empleado"]->segundoApellido." ".$arrSalid4["empleado"]->primerNombre." ".$arrSalid4["empleado"]->segundoNombre,
                                $valorDebito,
                                $valorCredito                            
                            ];
                            array_push($arrDef, $arrDefInt);
                        }
                        
                        
                    }
                   
                }
            }

        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=InformeContable'.$req->empresa.'_'.date("m",strtotime($req->fechaReporte)).'_'.date("Y",strtotime($req->fechaReporte)).'.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($arrDef);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->output('InformeContable'.$req->empresa.'_'.date("m",strtotime($req->fechaReporte)).'_'.date("Y",strtotime($req->fechaReporte)).'.csv');

        
    }
    
    public function cargarTerceroAdecuado($fkTipoTercero, $empleado, $terceroFijo){

        if($fkTipoTercero=="1"){
            return ["idTercero" => $empleado->numeroIdentificacion, "docTercero" => $empleado->tipoidentificacion, 
            "nomTercero" => ($empleado->primerApellido." ".$empleado->segundoApellido." ".$empleado->primerNombre." ".$empleado->segundoNombre),
            "digitoVer" => ""
        ];
            
        }
        else if($fkTipoTercero=="2"){
            $tercero = DB::table("tercero", "t")->
            select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero", "ti.nombre as tipoidentificacion", "t.digitoVer"])
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
            ->join("afiliacion as a","t.idTercero", "=","a.fkTercero")
            ->where("a.fkEmpleado","=",$empleado->idempleado)
            ->where("a.fkTipoAfilicacion","=","4") //4-Pensión Obligatoria 
            ->first();
            if(isset($tercero)){
                return ["idTercero" => $tercero->numeroIdentificacion,
                        "docTercero" => $tercero->tipoidentificacion, 
                        "nomTercero" => $tercero->razonSocial,
                        "digitoVer" => $tercero->digitoVer
                ];
            }            
            else{
                return false;
            }
        }
        else if($fkTipoTercero=="3"){
            $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero",
             "ti.nombre as tipoidentificacion", "t.digitoVer"])
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
            ->join("afiliacion as a","t.idTercero", "=","a.fkTercero")
            ->where("a.fkEmpleado","=",$empleado->idempleado)
            ->where("a.fkTipoAfilicacion","=","3") //3-Salud
            ->first();
            if(isset($tercero)){
                return ["idTercero" => $tercero->numeroIdentificacion,
                 "docTercero" => $tercero->tipoidentificacion, "nomTercero" => $tercero->razonSocial,
                 "digitoVer" => $tercero->digitoVer];
            }            
            else{
                return false;
            }
        }
        else if($fkTipoTercero=="4"){
            $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero", 
            "ti.nombre as tipoidentificacion", "t.digitoVer"])
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
            ->join("afiliacion as a","t.idTercero", "=","a.fkTercero")
            ->where("a.fkEmpleado","=",$empleado->idempleado)
            ->where("a.fkTipoAfilicacion","=","2") //2-Caja de compensacion
            ->first();
            if(isset($tercero)){
                return ["idTercero" => $tercero->numeroIdentificacion, "docTercero" => $tercero->tipoidentificacion,
                 "nomTercero" => $tercero->razonSocial,
                 "digitoVer" => $tercero->digitoVer];
            }            
            else{
                return false;
            }
        }
        else if($fkTipoTercero=="5"){
            $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero", 
            "ti.nombre as tipoidentificacion", "t.digitoVer"])
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
            ->join("empresa as em", "em.fkTercero_ARL", "=","t.idTercero")
            ->join("nomina as n", "n.fkEmpresa", "=","em.idempresa")
            ->join("empleado as e", "e.fkNomina", "=","n.idNomina")
            ->where("e.idempleado","=",$empleado->idempleado)
            ->first();

            if(isset($tercero)){
                return ["idTercero" => $tercero->numeroIdentificacion, "docTercero" => $tercero->tipoidentificacion,
                 "nomTercero" => $tercero->razonSocial,
                 "digitoVer" => $tercero->digitoVer];
            }            
            else{
                return false;
            }
        }
        else if($fkTipoTercero=="6"){
            $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero", 
            "ti.nombre as tipoidentificacion", "t.digitoVer"])
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
            ->join("afiliacion as a","t.idTercero", "=","a.fkTercero")
            ->where("a.fkEmpleado","=",$empleado->idempleado)
            ->where("a.fkTipoAfilicacion","=","1") //1-Fondo de Cesantias
            ->first();
            if(isset($tercero)){
                return ["idTercero" => $tercero->numeroIdentificacion, "docTercero" => $tercero->tipoidentificacion,
                 "nomTercero" => $tercero->razonSocial,
                 "digitoVer" => $tercero->digitoVer];
            }            
            else{
                return false;
            }
        }
        else if($fkTipoTercero=="7"){
            
            $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero", 
            "ti.nombre as tipoidentificacion", "t.digitoVer"])
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
            ->join("empleado as e", "e.fkEntidad", "=","t.idTercero")
            ->where("e.idempleado","=",$empleado->idempleado)
            ->first();

            if(isset($tercero)){
                return ["idTercero" => $tercero->numeroIdentificacion, "docTercero" => $tercero->tipoidentificacion,
                 "nomTercero" => $tercero->razonSocial,
                 "digitoVer" => $tercero->digitoVer];
            }            
            else{
                return false;
            }
        }
        else if($fkTipoTercero=="8"){
            
            $tercero = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero",
             "ti.nombre as tipoidentificacion", "t.digitoVer"])
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
            ->where("t.idTercero","=",$terceroFijo)
            ->first();
            
            if(isset($tercero)){
                return ["idTercero" => $tercero->numeroIdentificacion, "docTercero" => $tercero->tipoidentificacion, 
                "nomTercero" => $tercero->razonSocial,
                "digitoVer" => $tercero->digitoVer];
            }            
            else{
                return false;
            }
        }
        return false;
    }
    public function comportamientoPorNaturaleza($cuenta){

        $naturalezaDebito = "DEBITO";
        $naturalezaCredito = "CREDITO";
        $inicioCuenta = substr($cuenta, 0,1);
        if($inicioCuenta=="1" || $inicioCuenta=="5" || $inicioCuenta=="6" || $inicioCuenta=="7" || $inicioCuenta=="9"){
            $naturalezaCuenta = $naturalezaDebito;
        }
        else{
            $naturalezaCuenta = $naturalezaCredito;
        }
        return $naturalezaCuenta;
    }
    public function indexPlano(Request $req){
        $cargas = DB::table("carga_catalogo_contable","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->orderBy("cdp.idCarga", "desc")
        ->get();

        return view('/catalogoContable.subirPlano', ["cargas" => $cargas]);
    }
    public function subirArchivoPlano(Request $req){
    
        $csv = $req->file("archivoCSV");
        


        
        $reader = Reader::createFromFileObject($csv->openFile());
        $reader->setDelimiter(';');
        $csv = $csv->store("public/csvFiles");

        
        $idCarga  = DB::table("carga_catalogo_contable")->insertGetId([
            "rutaArchivo" => $csv,
            "fkEstado" => "3",
            "numActual" => 0,
            "numRegistros" => sizeof($reader)
        ], "idCarga");

        return redirect('catalogo-contable/verCarga/'.$idCarga);

    }

    public function verCarga($idCarga){
        $cargas = DB::table("carga_catalogo_contable","ccc")
        ->join("estado as e", "e.idEstado", "=", "ccc.fkEstado")
        ->where("ccc.idCarga","=",$idCarga)
        ->first();
        

         

        $datosCuentas = DB::table("catalogo_contable_plano","ccp")
        ->select("ccp.*", "ttc.nombre as nombreTipoTercero", "est.nombre as estado","t.razonSocial as nombreTercero", 
                "e.razonSocial as nombreEmpresa", "cc.nombre as nombreCentroCosto","gc.nombre as nombreGrupoConcepto")
        ->join("tipotercerocuenta as ttc","ttc.idTipoTerceroCuenta", "=","ccp.fkTipoTercero", "left")
        ->join("tercero as t","t.idTercero", "=","ccp.fkTerceroFijo", "left")
        ->join("empresa as e","e.idempresa", "=","ccp.fkEmpresa", "left")
        ->join("centrocosto as cc","cc.idcentroCosto", "=","ccp.fkCentroCosto", "left")
        ->join("grupoconcepto as gc","gc.idgrupoConcepto", "=","ccp.fkGrupoConcepto", "left")
        ->join("estado as est", "est.idEstado", "=", "ccp.fkEstado")
        ->where("ccp.fkCarga","=",$idCarga)
        ->get();
        
        

        return view('/catalogoContable.verCarga', [
            "cargas" => $cargas,
            "datosCuentas" => $datosCuentas
        ]);

    }
    public function subirDatosCuenta($idCarga){
        $cargaDatos = DB::table("carga_catalogo_contable","ccc")
        ->where("ccc.idCarga","=",$idCarga)
        ->where("ccc.fkEstado","=","3")
        ->first();
        if(isset($cargaDatos)){
            $contents = Storage::get($cargaDatos->rutaArchivo);
            
            $reader = Reader::createFromString($contents);
            $reader->setDelimiter(';');
            // Create a customer from each row in the CSV file
            $datosSubidos = 0; 
           
           
            for($i = $cargaDatos->numActual; $i < $cargaDatos->numRegistros; $i++){
                
                $row = $reader->fetchOne($i);
                $vacios = 0;
                foreach($row as $key =>$valor){
                    
                    if($valor==""){
                        $row[$key]=null;
                        $vacios++;
                    }
                    else{
                        $row[$key] = mb_convert_encoding($row[$key],"UTF-8");
                        if(strpos($row[$key], "/")){
                            
                            $dt = DateTime::createFromFormat("d/m/Y", $row[$key]);
                            if($dt === false){
                                $dt = new DateTime();
                            }
                            $ts = $dt->getTimestamp();
                            $row[$key] = date("Y-m-d", $ts);
                        }
                    }
                }
                $estado = "3";
                if($row[0]=="1"){
                    

                    $existeEmpresa = DB::table("empresa","e")
                    ->where("e.idempresa","=", $row[5])
                    ->first();
                    if(!isset($existeEmpresa)){
                        $estado = "17";
                    }
                    
                    if($row[6]!=NULL){
                        $existeCentroCosto = DB::table("centrocosto","cc")
                        ->where("cc.idcentroCosto","=", $row[6])
                        ->first();
                        if(!isset($existeCentroCosto)){
                            $estado = "18";
                        }
    
                        $centroCostoPerteneceEmpresa = DB::table("centrocosto","cc")
                        ->where("cc.idcentroCosto","=", $row[6])
                        ->where("cc.fkEmpresa","=", $row[5])
                        ->first();
                        if(!isset($centroCostoPerteneceEmpresa)){
                            $estado = "19";
                        }
                    }
                    


                    $existeTipoTercero = DB::table("tipotercerocuenta","ttc")
                    ->where("ttc.idTipoTerceroCuenta","=", $row[3])
                    ->first();

                    if(!isset($existeTipoTercero)){
                        $estado = "20";
                    }

                    if($row[3]=="8"){
                        $existeTercero = DB::table("tercero","t")
                        ->where("t.idTercero","=", $row[4])
                        ->first();
                        if(!isset($existeTercero)){
                            $estado = "21";
                        }
                    }
                    
                                       
                    
                    
                    $datosSubidos ++;
                    $arrSubida = [
                        "tipoRegistro" => $row[0],
                        "cuenta" => $row[1],
                        "descripcion" => $row[2],
                        "fkTipoTercero" => $row[3],
                        "fkEmpresa" => $row[5],
                        "fkCentroCosto" => $row[6],
                        "tipoComportamiento" => "1",
                        "fkCarga" => $idCarga,
                        "fkEstado" => $estado
                    ];
                   
                    if($row[3]=="8"){
                        $arrSubida["fkTerceroFijo"] = $row[4];
                    }
                    DB::table("catalogo_contable_plano")->insert($arrSubida);


                }
                else if($row[0]=="2"){
                    if($row[1]=="1"){
                        $existeGrupoConcepto = DB::table("grupoconcepto","gc")
                        ->where("gc.idgrupoConcepto","=", $row[2])
                        ->first();
                        if(!isset($existeGrupoConcepto)){
                            $estado = "23";
                        }
                        
                    }
                    else if($row[1]=="2"){
                        if(intval($row[3])<=0 || intval($row[3])>4 ){
                            //No existe provision
                            $estado = "24";
                        }

                    }
                    else if($row[1]=="3"){
                        if(intval($row[4])<=0 || intval($row[4])>6 ){
                            //No existe Aporte Empleador
                            $estado = "25";
                        }
                    }
                    else if($row[1]=="4"){
                        $existeConcepto = DB::table("concepto","c")
                        ->where("c.idconcepto","=", $row[5])
                        ->first();
                        if(!isset($existeConcepto)){
                            $estado = "23";
                        }
                        
                    }
                    else{
                        //No existe tipoConulta
                        $estado = "26";
                    }



                    if($row[10]=="1"){
                        $row[10] = "Aportes";
                    }
                    else if($row[10]=="2"){
                        $row[10] = "Provisiones";
                    }
                    else if($row[10]=="3"){
                        $row[10] = "Nomina";
                    }
                    else{
                        $estado = "22";
                        //No existe transaccion
                    }

                    $datosSubidos ++;
                    $arrSubida = [
                        "tipoRegistro" => $row[0],
                        "tipoConsulta" => $row[1],
                        "fkGrupoConcepto" => $row[2],
                        "fkTipoAporteEmpleador" => $row[3],
                        "fkTipoProvision" => $row[4],      
                        "fkConcepto" => $row[5],     
                        "cuenta1" => $row[6],
                        "cuenta2" => $row[7],
                        "fkEmpresa2" => $row[8],
                        "fkCentroCosto2" => $row[9],
                        "transaccion" => $row[10],
                        "fkCarga" => $idCarga,
                        "fkEstado" => $estado
                    ];
                   
                    DB::table("catalogo_contable_plano")->insert($arrSubida);
                }              
               
                $datosSubidos++;
                
                if($datosSubidos == 10){
                    if($cargaDatos->numRegistros == 10){
                        DB::table("carga_catalogo_contable")
                        ->where("idCarga","=",$idCarga)
                        ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);
                    }
                    else{
                        DB::table("carga_catalogo_contable")
                        ->where("idCarga","=",$idCarga)
                        ->update(["numActual" => ($i+1)]);
                    }
                


                    

                    $datosCuentas = DB::table("catalogo_contable_plano","ccp")
                    ->select("ccp.*", "ttc.nombre as nombreTipoTercero", "est.nombre as estado","t.razonSocial as nombreTercero", 
                            "e.razonSocial as nombreEmpresa", "cc.nombre as nombreCentroCosto", "gc.nombre as nombreGrupoConcepto")
                    ->join("tipotercerocuenta as ttc","ttc.idTipoTerceroCuenta", "=","ccp.fkTipoTercero", "left")
                    ->join("tercero as t","t.idTercero", "=","ccp.fkTerceroFijo", "left")
                    ->join("empresa as e","e.idempresa", "=","ccp.fkEmpresa", "left")
                    ->join("centrocosto as cc","cc.idcentroCosto", "=","ccp.fkCentroCosto", "left")
                    ->join("grupoconcepto as gc","gc.idgrupoConcepto", "=","ccp.fkGrupoConcepto", "left")
                    ->join("estado as est", "est.idEstado", "=", "ccp.fkEstado")
                    ->where("ccp.fkCarga","=",$idCarga)
                    ->get();
                    $mensaje = "";

                    foreach($datosCuentas as $index => $datoCuenta){
                        if($datoCuenta->tipoRegistro=="1"){
                            $mensaje.='<tr>
                                <th></th>
                                <td>'.($index + 1).'</td>
                                <td>'.$datoCuenta->cuenta.'</td>
                                <td>'.$datoCuenta->descripcion.'</td>
                                <td>'.$datoCuenta->nombreEmpresa.'</td>
                                <td>'.$datoCuenta->nombreCentroCosto.'</td>
                                <td>'.$datoCuenta->estado.'</td>
                            </tr>';
                        }
                        else{
                            $mensaje.='<tr>
                                <th></th>
                                <td>'.($index + 1).'</td>
                                <td></td>
                                <td>'.$datoCuenta->nombreGrupoConcepto.'</td>
                                <td>'.$datoCuenta->fkTipoProvision.'</td>
                                <td>'.$datoCuenta->fkTipoAporteEmpleador.'</td>
                                <td>'.$datoCuenta->estado.'</td>
                            </tr>';
                        }
                        
                    }
                    if($cargaDatos->numRegistros == 10){
                        return response()->json([
                            "success" => true,
                            "seguirSubiendo" => false,
                            "numActual" => $cargaDatos->numRegistros,
                            "mensaje" => $mensaje,
                            "porcentaje" => "100%"
            
                        ]);
                    }
                    else{
                        return response()->json([
                            "success" => true,
                            "seguirSubiendo" => true,
                            "numActual" =>  ($i+1),
                            "mensaje" => $mensaje,
                            "porcentaje" => ceil((($i+1) / $cargaDatos->numRegistros)*100)."%"
                        ]);

                    }
                    
                }


                
            }
            
                        
            if($datosSubidos!=0){
    
                /*$estado = "3";
                if($row[0]=="1"){
                    

                    $existeEmpresa = DB::table("empresa","e")
                    ->where("e.idempresa","=", $row[5])
                    ->first();
                    if(!isset($existeEmpresa)){
                        $estado = "17";
                    }
                    

                    $existeCentroCosto = DB::table("centrocosto","cc")
                    ->where("cc.idcentroCosto","=", $row[6])
                    ->first();
                    if(!isset($existeCentroCosto)){
                        $estado = "18";
                    }

                    $centroCostoPerteneceEmpresa = DB::table("centrocosto","cc")
                    ->where("cc.idcentroCosto","=", $row[6])
                    ->where("cc.fkEmpresa","=", $row[5])
                    ->first();
                    if(!isset($centroCostoPerteneceEmpresa)){
                        $estado = "19";
                    }


                    $existeTipoTercero = DB::table("tipotercerocuenta","ttc")
                    ->where("ttc.idTipoTerceroCuenta","=", $row[3])
                    ->first();

                    if(!isset($existeTipoTercero)){
                        $estado = "20";
                    }

                    if($row[3]=="8"){
                        $existeTercero = DB::table("tercero","t")
                        ->where("t.idTercero","=", $row[4])
                        ->first();
                        if(!isset($existeTercero)){
                            $estado = "21";
                        }
                    }
                    
                    if($row[7]!="1" && $row[7]!="2"){
                        //No existe tipoComportamiento
                        $estado = "27";
                    }
                    
                    if($row[8]=="1"){
                        $row[8] = "Aportes";
                    }
                    else if($row[8]=="2"){
                        $row[8] = "Provisiones";
                    }
                    else if($row[8]=="3"){
                        $row[8] = "Nomina";
                    }
                    else{
                        $estado = "22";
                        //No existe transaccion
                    }
                    
                    $datosSubidos ++;
                    $arrSubida = [
                        "tipoRegistro" => $row[0],
                        "cuenta" => $row[1],
                        "descripcion" => $row[2],
                        "fkTipoTercero" => $row[3],
                        "fkEmpresa" => $row[5],
                        "fkCentroCosto" => $row[6],
                        "tipoComportamiento" => $row[7],
                        "transaccion" => $row[8],
                        "fkCarga" => $idCarga,
                        "fkEstado" => $estado
                    ];
                   
                    if($row[3]=="8"){
                        $arrSubida["fkTerceroFijo"] = $row[4];
                    }
                    DB::table("catalogo_contable_plano")->insert($arrSubida);


                }
                else if($row[0]=="2"){

                   
                    

                    if($row[1]=="1"){
                        $existeGrupoConcepto = DB::table("grupoconcepto","gc")
                        ->where("gc.idgrupoConcepto","=", $row[2])
                        ->first();
                        if(!isset($existeGrupoConcepto)){
                            $estado = "23";
                        }
                        
                    }
                    else if($row[1]=="2"){
                        if(intval($row[3])<=0 || intval($row[3])>4 ){
                            //No existe provision
                            $estado = "24";
                        }

                    }
                    else if($row[1]=="3"){
                        if(intval($row[4])<=0 || intval($row[4])>6 ){
                            //No existe Aporte Empleador
                            $estado = "25";
                        }
                    }
                    else{
                        //No existe tipoConulta
                        $estado = "26";
                    }

        

                    $datosSubidos ++;
                    $arrSubida = [
                        "tipoRegistro" => $row[0],
                        "tipoConsulta" => $row[1],
                        "fkGrupoConcepto" => $row[2],
                        "fkTipoProvision" => $row[3],
                        "fkTipoAporteEmpleador" => $row[4],
                        "fkCarga" => $idCarga,
                        "fkEstado" => $estado
                    ];
                   
                    DB::table("catalogo_contable_plano")->insert($arrSubida);
                }*/
                DB::table("carga_catalogo_contable")
                ->where("idCarga","=",$idCarga)
                ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);
            }
            $datosCuentas = DB::table("catalogo_contable_plano","ccp")
            ->select("ccp.*", "ttc.nombre as nombreTipoTercero", "est.nombre as estado","t.razonSocial as nombreTercero", 
                    "e.razonSocial as nombreEmpresa", "cc.nombre as nombreCentroCosto", "gc.nombre as nombreGrupoConcepto")
            ->join("tipotercerocuenta as ttc","ttc.idTipoTerceroCuenta", "=","ccp.fkTipoTercero", "left")
            ->join("tercero as t","t.idTercero", "=","ccp.fkTerceroFijo", "left")
            ->join("empresa as e","e.idempresa", "=","ccp.fkEmpresa", "left")
            ->join("centrocosto as cc","cc.idcentroCosto", "=","ccp.fkCentroCosto", "left")
            ->join("grupoconcepto as gc","gc.idgrupoConcepto", "=","ccp.fkGrupoConcepto", "left")
            ->join("estado as est", "est.idEstado", "=", "ccp.fkEstado")
            ->where("ccp.fkCarga","=",$idCarga)
            ->get();
            $mensaje = "";

            foreach($datosCuentas as $index => $datoCuenta){
                if($datoCuenta->tipoRegistro=="1"){
                    $mensaje.='<tr>
                        <th></th>
                        <td>'.($index + 1).'</td>
                        <td>'.$datoCuenta->cuenta.'</td>
                        <td>'.$datoCuenta->descripcion.'</td>
                        <td>'.$datoCuenta->nombreEmpresa.'</td>
                        <td>'.$datoCuenta->nombreCentroCosto.'</td>
                        <td>'.$datoCuenta->estado.'</td>
                    </tr>';
                }
                else{
                    $mensaje.='<tr>
                        <th></th>
                        <td>'.($index + 1).'</td>
                        <td></td>
                        <td>'.$datoCuenta->nombreGrupoConcepto.'</td>
                        <td>'.$datoCuenta->fkTipoProvision.'</td>
                        <td>'.$datoCuenta->fkTipoAporteEmpleador.'</td>
                        <td>'.$datoCuenta->estado.'</td>
                    </tr>';
                }
                
            }
            return response()->json([
                "success" => true,
                "seguirSubiendo" => false,
                "numActual" => $cargaDatos->numRegistros,
                "mensaje" => $mensaje,
                "porcentaje" => "100%"
            ]);
        }
    }

    public function cancelarCarga($idCarga){
        DB::table("carga_catalogo_contable")
        ->where("idCarga","=",$idCarga)
        ->delete();
        return redirect('/catalogo-contable/subirPlano');
    }
    public function eliminarRegistros(Request $req){

        
        if(isset($req->idCartalogoContablePlano)){
            DB::table("catalogo_contable_plano")->whereIn("idCartalogoContablePlano",$req->idCartalogoContablePlano)->delete();
        }
        
        return redirect('/catalogo-contable/verCarga/'.$req->idCarga);
    }
    public function aprobarCarga($idCarga){

        $datosCuentas = DB::table("catalogo_contable_plano","ccp")
        ->select("ccp.*")
        ->where("ccp.fkCarga","=",$idCarga)
        ->get();
        $idCatalgoContable = 0;
        foreach($datosCuentas as $datoCuenta){            
            if($datoCuenta->tipoRegistro == "1"){

                $arrInsertCuenta = [
                    "descripcion" => $datoCuenta->descripcion,
                    "cuenta" => $datoCuenta->cuenta,
                    "fkTipoTercero" => $datoCuenta->fkTipoTercero,
                    "fkTercero" => $datoCuenta->fkTerceroFijo,
                    "fkEmpresa" => $datoCuenta->fkEmpresa,
                    "fkCentroCosto" => $datoCuenta->fkCentroCosto,
                    "tipoComportamiento" => "1"
                ];  
                $idCatalgoContable = DB::table("catalgocontable")->insertGetId($arrInsertCuenta, "idCatalgoContable");

                DB::table("catalogo_contable_plano")
                    ->where("idCartalogoContablePlano","=",$datoCuenta->idCartalogoContablePlano)
                    ->update(["fkEstado" => "11"]);
            }
            else if($datoCuenta->tipoRegistro == "2"){

                $tipo = 0;
                if(isset($datoCuenta->fkTipoProvision) && $datoCuenta->fkTipoProvision!=0){
                    $tipo = $datoCuenta->fkTipoProvision;
                }
                else if(isset($datoCuenta->fkTipoAporteEmpleador) && $datoCuenta->fkTipoAporteEmpleador!=0){
                    $tipo = $datoCuenta->fkTipoAporteEmpleador;
                }
               
                $cuenta1 = DB::table("catalgocontable")
                ->where("fkEmpresa","=",$datoCuenta->fkEmpresa2)
                ->where("fkCentroCosto","=",$datoCuenta->fkCentroCosto2)
                ->where("cuenta","=",$datoCuenta->cuenta1)
                ->first();

                $cuenta2 = DB::table("catalgocontable")
                ->where("fkEmpresa","=",$datoCuenta->fkEmpresa2)
                ->where("fkCentroCosto","=",$datoCuenta->fkCentroCosto2)
                ->where("cuenta","=",$datoCuenta->cuenta2)
                ->first();

                if(!isset($cuenta1) || !isset($cuenta2)){
                    DB::table("catalogo_contable_plano")
                    ->where("idCartalogoContablePlano","=",$datoCuenta->idCartalogoContablePlano)
                    ->update(["fkEstado" => "28"]);
                }
                else{
                    $arrInsertDatosCuenta = [
                        "tablaConsulta" => $datoCuenta->tipoConsulta,
                        "fkCuenta" => $cuenta1->idCatalgoContable,
                        "fkGrupoConcepto" => $datoCuenta->fkGrupoConcepto,
                        "fkConcepto" => $datoCuenta->fkConcepto,
                        "tipoCuenta" => "DEBITO",
                        "subTipoConsulta" => $tipo,
                        "transaccion" => $datoCuenta->transaccion
                    ];  
                    
                    DB::table("datoscuenta")->insert($arrInsertDatosCuenta);
    
                    $arrInsertDatosCuenta = [
                        "tablaConsulta" => $datoCuenta->tipoConsulta,
                        "fkCuenta" => $cuenta2->idCatalgoContable,
                        "fkGrupoConcepto" => $datoCuenta->fkGrupoConcepto,
                        "fkConcepto" => $datoCuenta->fkConcepto,
                        "tipoCuenta" => "CREDITO",
                        "subTipoConsulta" => $tipo,
                        "transaccion" => $datoCuenta->transaccion
                    ]; 

                    DB::table("datoscuenta")->insert($arrInsertDatosCuenta);


                    DB::table("catalogo_contable_plano")
                    ->where("idCartalogoContablePlano","=",$datoCuenta->idCartalogoContablePlano)
                    ->update(["fkEstado" => "11"]);
                }
            }
        }
        DB::table("carga_catalogo_contable")
        ->where("idCarga","=",$idCarga)
        ->update(["fkEstado" => "11"]);

        return redirect('/catalogo-contable/subirPlano');
    }
    public function roundSup($numero, $presicion){
        $redondeo = $numero / pow(10,$presicion*-1);
        $redondeo = ceil($redondeo);
        $redondeo = $redondeo * pow(10,$presicion*-1);
        return $redondeo;
    }
}
