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
        ->join("tipotercerocuenta as tc", "tc.idTipoTerceroCuenta", "=","cc.fkTipoTercero", "left")
        ->paginate(15);
        
        


        return view('/catalogoContable.index',
            ["catalogo" => $catalogo]
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
            "tipoComportamiento" => $req->tipoComportamiento,
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
    public function reporteNominaIndex(){

        $empresas = DB::table("empresa", "e")->get();
        
        


        return view('/catalogoContable.reporteNominaIndex',
            ["empresas" => $empresas]
        );
    }

    
    public function generarReporteNomina(Request $req){

        $fechaInicioMes = date("Y-m-01", strtotime($req->fechaReporte));
        $fechaFinMes = date("Y-m-t", strtotime($fechaInicioMes));

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
                    if(!isset($datoCuentaTipo1->fkCentroCosto)){
                        $arrCentroCosto["porcentaje"] = 100;
                    }
                   
                    $itemsBoucher = DB::table("item_boucher_pago", "ibp")
                    ->selectRaw("ibp.pago, ibp.descuento, con.idconcepto, ibp.valor, con.nombre as con_nombre")
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
                        $tipoReg = "";
                        if($datoCuentaTipo1->tipoComportamiento == 1){
                            
                            if($itemBoucher->valor > 0){
                                $tipoReg = "DEBITO";
                                $valor = $itemBoucher->valor;
                            }
                            else{
                                $tipoReg = "CREDITO";
                                $valor = $itemBoucher->valor * -1;
                            }
                            
                        }
                        else{
                            if($itemBoucher->valor > 0){
                                $tipoReg = "CREDITO";
                                $valor = $itemBoucher->valor;
                            }
                            else{
                                $tipoReg = "DEBITO";
                                $valor = $itemBoucher->valor * -1;
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
                    if(!isset($datoCuentaTipo2->fkCentroCosto)){
                        $arrCentroCosto["porcentaje"] = 100;
                    }
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

                    $datosProvResta = DB::table('provision','p')
                        ->selectRaw("sum(p.valor) as suma")
                        ->where("p.anio","=",$provision->anio)
                        ->where("p.mes","<",$provision->mes)
                        ->where("p.fkEmpleado","=",$provision->fkEmpleado)
                        ->where("p.fkConcepto","=",$provision->fkConcepto)
                        ->first();
                    if(isset($datosProvResta)){
                        $valor = $provision->valor - $datosProvResta->suma;    
                    }
                    else{
                        $valor = $provision->valor;    
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
                                "tipoReg" => $this->comportamientoPorNaturaleza($datoCuentaTipo2->cuenta),
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
                    if(!isset($datoCuentaTipo3->fkCentroCosto)){
                        $arrCentroCosto["porcentaje"] = 100;
                    }


                    $parafiscales = DB::table("parafiscales", "para")
                    ->selectRaw("para.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","para.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->whereRaw("(MONTH(ln.fechaInicio) = MONTH('".$req->fechaReporte."') and YEAR(ln.fechaInicio) = YEAR('".$req->fechaReporte."'))")
                    ->get();

                    $valor = 0;
                    if(!isset( $arrayInt[3][$datoCuentaTipo3->cuenta])){
                        $arrayInt[3][$datoCuentaTipo3->cuenta] = array();
                    }  
                    if($datoCuentaTipo3->subTipoConsulta == "1"){
                        foreach($parafiscales as $parafiscal){
                            $valor = $parafiscal->afp;
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
                                        "tipoReg" => $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta),
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
                                        "tipoReg" => $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta),
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
                                        "tipoReg" => $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta),
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
                                        "tipoReg" => $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta),
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
                                        "tipoReg" => $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta),
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
                                        "tipoReg" => $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta),
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

        return redirect('catalogoContable/verCarga/'.$idCarga);

    }

    public function verCargaVac($idCarga){
        $cargasDatosPasados = DB::table("carga_catalogo_contable","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->where("cdp.idCarga","=",$idCarga)
        ->first();
        
        $datosPasados = DB::table("catalogo_contabla_plano","dp")
        ->select("dp.*", "est.nombre as estado","dp2.*")
        ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
        ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
        ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->get();
        
        

        return view('/datosPasadosVac.verCarga', [
            "cargaDatoPasado" => $cargasDatosPasados,
            "datosPasados" => $datosPasados
        ]);

    }
    public function subirVac($idCarga){
        $cargaDatos = DB::table("carga_catalogo_contable","cdp")
        ->where("cdp.idCarga","=",$idCarga)
        ->where("cdp.fkEstado","=","3")
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
                        $row[$key] = utf8_encode($row[$key]);
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
                if($vacios >= 5){
                    continue;
                }
                

                $existeEmpleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion","=", $row[1])
                ->where("dp.fkTipoIdentificacion","=", $row[0])
                ->first();
           
                    
                $row[5] = floatval($row[5]);
                if(isset($existeEmpleado)){
                    DB::table("catalogo_contabla_plano")->insert([
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fecha" => $row[2],
                        "fechaInicial" => $row[3],
                        "fechaFinal" => $row[4],
                        "dias" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]);
                }
                else{
                    DB::table("catalogo_contabla_plano")->insert([
                        "fecha" => $row[2],
                        "fechaInicial" => $row[3],
                        "fechaFinal" => $row[4],
                        "dias" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "14"
                    ]);
                }
                $datosSubidos++;
                
                if($datosSubidos == 3){
                    if($cargaDatos->numRegistros == 3){
                        DB::table("carga_catalogo_contable")
                        ->where("idCarga","=",$idCarga)
                        ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);
                    }
                    else{
                        DB::table("carga_catalogo_contable")
                        ->where("idCarga","=",$idCarga)
                        ->update(["numActual" => ($i+1)]);
                    }
                


                    

                    $datosPasados = DB::table("catalogo_contabla_plano","dp")
                    ->select("dp.*","est.nombre as estado","dp2.*")
                    ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
                    ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
                    ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
                    ->where("dp.fkCargaDatosPasados","=",$idCarga)
                    ->get();
                    $mensaje = "";

                    foreach($datosPasados as $index => $datoPasado){
                        $mensaje.='<tr>
                            <th></th>
                            <td>'.($index + 1).'</td>
                            <td>'.$datoPasado->numeroIdentificacion.'</td>
                            <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                            <td>'.$datoPasado->fecha.'</td>
                            <td>'.$datoPasado->fechaInicial.'</td>
                            <td>'.$datoPasado->fechaFinal.'</td>
                            <td>'.$datoPasado->dias.'</td>
                            <td>'.$datoPasado->estado.'</td>
                        </tr>';
                    }
                    if($cargaDatos->numRegistros == 3){
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
                if($datosSubidos>3){
                    $existeEmpleado = DB::table("empleado","e")
                    ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                    ->where("dp.numeroIdentificacion","=", $row[1])
                    ->where("dp.fkTipoIdentificacion","=", $row[0])
                    ->first();
            
                        
                    if(isset($existeEmpleado)){
                        DB::table("catalogo_contabla_plano")->insert([
                            "fkEmpleado" => $existeEmpleado->idempleado,
                            "fecha" => $row[2],
                            "fechaInicial" => $row[3],
                            "fechaFinal" => $row[4],
                            "dias" => $row[5],
                            "fkCargaDatosPasados" => $idCarga,
                            "fkEstado" => "11"
                        ]);
                    }
                    else{
                        DB::table("catalogo_contabla_plano")->insert([
                            "fecha" => $row[2],
                            "fechaInicial" => $row[3],
                            "fechaFinal" => $row[4],
                            "dias" => $row[5],
                            "fkCargaDatosPasados" => $idCarga,
                            "fkEstado" => "14"
                        ]);
                    }
                }
                DB::table("carga_catalogo_contable")
                ->where("idCarga","=",$idCarga)
                ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);

            }  
            $datosPasados = DB::table("catalogo_contabla_plano","dp")
            ->select("dp.*","est.nombre as estado","dp2.*")
            ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
            ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.idempleado", "left")
            ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
            ->where("dp.fkCargaDatosPasados","=",$idCarga)
            ->get();
            $mensaje = "";

            foreach($datosPasados as $index => $datoPasado){
                $mensaje.='<tr>
                    <th>'.((isset($datoPasado->primerApellido)) ? '<input type="checkbox" name="idDatosPasados[]" value="'.$datoPasado->idDatosPasados.'" />' : '' ).'</th>
                    <td>'.($index + 1).'</td>
                    <td>'.$datoPasado->numeroIdentificacion.'</td>
                    <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                    <td>'.$datoPasado->fecha.'</td>
                    <td>'.$datoPasado->fechaInicial.'</td>
                    <td>'.$datoPasado->fechaFinal.'</td>
                    <td>'.$datoPasado->dias.'</td>
                    <td>'.$datoPasado->estado.'</td>
                </tr>';
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

    public function cancelarCargaVac($idCarga){
        DB::table("carga_catalogo_contable")
        ->where("idCarga","=",$idCarga)
        ->delete();
        return redirect('/datosPasadosVac');
    }
    public function eliminarRegistrosVac(Request $req){

        
        if(isset($req->idDatosPasados)){
            DB::table("catalogo_contabla_plano")->whereIn("idDatosPasados",$req->idDatosPasados)->delete();
        }
        
        return redirect('/datosPasadosVac/verCarga/'.$req->idCarga);
    }
    public function aprobarCargaVac($idCarga){
        $datosPasados = DB::table("catalogo_contabla_plano","dp")
        ->join("empleado as e", "e.idempleado", "=", "dp.fkEmpleado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->where("dp.fkEstado","=","3")
        ->orderBy("dp.fecha")
        ->orderBy("dp.fkEmpleado")
        ->orderBy("e.fkNomina")
        ->get();

        foreach($datosPasados as $datoPasado){            

            $arrInsertVac = [
                "fechaInicio" => $datoPasado->fechaInicial,
                "fechaFin" => $datoPasado->fechaFinal,
                "diasCompensar" => $datoPasado->dias,
                "pagoAnticipado" => "1"
            ];
            $idVacaciones = DB::table("vacaciones")->insertGetId($arrInsertVac, "idVacaciones");
      


            $arrInsertNovedad =[
                "fkTipoNovedad" => 6,
                "fkNomina" => $datoPasado->fkNomina,
                "fkEmpleado" => $datoPasado->fkEmpleado,
                "fkEstado" => "8",
                "fechaRegistro" => $datoPasado->fecha,
                "fkConcepto" => "29",
                "fkVacaciones" => $idVacaciones,
                "fkCargaDatosPasadosVac" => $idCarga
            ];
            DB::table("novedad")->insert($arrInsertNovedad);
            DB::table("catalogo_contabla_plano")
                ->where("idDatosPasados","=",$datoPasado->idDatosPasados)
                ->update(["fkEstado" => "11"]);

        }
        DB::table("carga_catalogo_contable")
        ->where("idCarga","=",$idCarga)
        ->update(["fkEstado" => "11"]);

        return redirect('/datosPasadosVac');
    }

}
