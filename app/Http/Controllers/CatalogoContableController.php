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
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogoContableController extends Controller
{
    public function index(Request $req){

        $dataUsu = UsuarioController::dataAdminLogueado();
        $arrIntermedio = array();
        $datoscuenta = DB::table("datoscuenta","dc")
        ->select("dc.*","cc.fkEmpresa","dc.fkCentroCosto","cc.cuenta","c.nombre as nombreConcepto", "gc.nombre as nombreGrupo", "e.razonSocial as nombreEmpresa", "e.razonSocial as nombreEmpresa", "cen.nombre as nombreCC")
        ->join("grupoconcepto as gc", "gc.idgrupoConcepto", "=","dc.fkGrupoConcepto", "left")
        ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
        ->join("empresa as e", "e.idempresa", "=","cc.fkEmpresa", "left")
        ->join("centrocosto as cen", "cen.idcentroCosto", "=","dc.fkCentroCosto", "left")
        ->join("concepto as c", "c.idconcepto", "=","dc.fkConcepto", "left");
        
        if(isset($req->idempresa)){
            $datoscuenta = $datoscuenta->where("e.idempresa","=",$req->idempresa);
        }
        if(isset($dataUsu) && $dataUsu->fkRol == 2){            
            $datoscuenta = $datoscuenta->whereIn("e.idempresa", $dataUsu->empresaUsuario);
        }

        if(isset($req->idcentroCosto)){
            $datoscuenta = $datoscuenta->where("cen.idcentroCosto","=",$req->idcentroCosto);
        }else{
            $datoscuenta = $datoscuenta->whereNull("cen.idcentroCosto");   
        }
        $datoscuenta = $datoscuenta->get();


        foreach($datoscuenta as $datocuenta){
            $id = $datocuenta->tablaConsulta."_".$datocuenta->fkGrupoConcepto."_".$datocuenta->subTipoConsulta."_".$datocuenta->fkConcepto."_".$datocuenta->fkEmpresa."_".$datocuenta->fkCentroCosto;
            $fkTipoProvision = "";
            $fkTipoAporteEmpleador = "";
            

            if($datocuenta->tablaConsulta=="2"){
                $fkTipoProvision = $datocuenta->subTipoConsulta;
            }
            else if($datocuenta->tablaConsulta=="3"){
                $fkTipoAporteEmpleador = $datocuenta->subTipoConsulta;
            }

            $cuentaDebito = "";
            $cuentaCredito = "";
            
            if($datocuenta->tipoCuenta == "DEBITO"){
                $cuentaDebito = $datocuenta->cuenta;
            }
            else if($datocuenta->tipoCuenta == "CREDITO"){
                $cuentaCredito = $datocuenta->cuenta;
            }


           
            if(!isset($arrIntermedio[$id])){
                $arrIntermedio[$id] = [
                    "id" => $id,
                    "tablaConsulta" => $datocuenta->tablaConsulta,
                    "nombreGrupo" => $datocuenta->nombreGrupo,
                    "fkTipoProvision" => $fkTipoProvision,
                    "fkTipoAporteEmpleador" => $fkTipoAporteEmpleador,
                    "nombreConcepto" => $datocuenta->nombreConcepto,
                    "cuentaDebito" => $cuentaDebito,
                    "cuentaCredito" => $cuentaCredito,
                    "nombreEmpresa" => $datocuenta->nombreEmpresa,
                    "nombreCC" => $datocuenta->nombreCC,
                    "transaccion" => $datocuenta->transaccion
                ];
            }
            else{
                if($datocuenta->tipoCuenta == "DEBITO"){
                    $arrIntermedio[$id]["cuentaDebito"] = $cuentaDebito;
                }
                else if($datocuenta->tipoCuenta == "CREDITO"){
                    $arrIntermedio[$id]["cuentaCredito"] = $cuentaCredito;
                }
            }
        }
        $filtro = $arrIntermedio;
        if(isset($req->descripcion)){
            $filtro = array();
            foreach($arrIntermedio as $dato){
                
                $adicionalProv = "";
                if($dato["fkTipoProvision"] == "1"){
                    $adicionalProv = "PRIMA";
                }
                else if($dato["fkTipoProvision"] == "2"){
                    $adicionalProv = "CESANTIAS";
                }
                else if($dato["fkTipoProvision"] == "3"){
                    $adicionalProv = "INTERESES DE CESANTIAS";
                }
                else if($dato["fkTipoProvision"] == "4"){
                    $adicionalProv = "VACACIONES";
                }

                $adicionalAporte = "";
                if($dato["fkTipoAporteEmpleador"] == "1"){
                    $adicionalAporte = "PENSIÓN";
                }
                else if($dato["fkTipoAporteEmpleador"] == "2"){
                    $adicionalAporte = "SALUD";
                }
                else if($dato["fkTipoAporteEmpleador"] == "3"){
                    $adicionalAporte = "ARL";
                }
                else if($dato["fkTipoAporteEmpleador"] == "4"){
                    $adicionalAporte = "CCF";
                }
                else if($dato["fkTipoAporteEmpleador"] == "5"){
                    $adicionalAporte = "ICBF";
                }
                else if($dato["fkTipoAporteEmpleador"] == "6"){
                    $adicionalAporte = "SENA";
                }
                else if($dato["fkTipoAporteEmpleador"] == "7"){
                    $adicionalAporte = "APORTE FONDO DE SOLIDARIDAD";
                }


                if( strpos($dato["cuentaCredito"], $req->descripcion)!==false || 
                    strpos($dato["cuentaDebito"], $req->descripcion)!==false || 
                    strpos($dato["nombreGrupo"], $req->descripcion)!==false || 
                    strpos($dato["nombreConcepto"], $req->descripcion)!==false || 
                    strpos($adicionalProv, $req->descripcion)!==false || 
                    strpos($adicionalAporte, $req->descripcion)!==false ){                    
                    array_push($filtro, $dato);
                }
            }
        }
        


        $data = $this->paginate($filtro, 15);
        $data->withPath("/catalogo-contable");

        $empresas = DB::table("empresa", "e");
        if(isset($dataUsu) && $dataUsu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $dataUsu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();
        

        $centros_costos = array();
        if(isset($req->idempresa)){
            $centros_costos = DB::table("centrocosto")->where("fkEmpresa","=",$req->idempresa)->orderBy("nombre")->get();
        }
        $arrConsulta = ["idempresa" => $req->idempresa, "idcentroCosto" => $req->idcentroCosto, "descripcion" => $req->descripcion];
        return view('/catalogoContable.index',
            [
                "catalogo" => $data,
                "req" => $req,
                "empresas" => $empresas,
                "centros_costos" => $centros_costos,
                "arrConsulta" => $arrConsulta,
                "dataUsu" => $dataUsu
            ]
        );
    }
    public function getFormAdd(){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $terceros = DB::table("tercero")->get();
        $tipoTerceroCuenta = DB::table("tipotercerocuenta")->get();
        $empresas = DB::table("empresa", "e");
        if(isset($dataUsu) && $dataUsu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $dataUsu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();
        
        $gruposConcepto  = DB::table("grupoconcepto")->get();

        $cuentas = DB::table("catalgocontable")->orderBy("cuenta")->get();
        $conceptos = DB::table("concepto")->orderBy("nombre")->get();
        


        return view('/catalogoContable.formAdd',
            [
                "conceptos" => $conceptos,
                "cuentas" => $cuentas,
                "terceros" => $terceros, 
                "empresas" => $empresas,
                "tipoTerceroCuenta" => $tipoTerceroCuenta,
                "gruposConcepto" => $gruposConcepto,
                "dataUsu" => $dataUsu
            ]
        );
    }
    public function eliminarTransaccion($idCompuesto){
        $arrCompuesto = explode('_', $idCompuesto);

        $datoscuenta = DB::table("datoscuenta","dc")
        ->select("dc.*","cat.*", "dc.fkCentroCosto as fkCentroCosto2")
        ->join("catalgocontable as cat", "cat.idCatalgoContable", "=","dc.fkCuenta")
        ->where("dc.tablaConsulta","=",$arrCompuesto[0]);
        if(isset($arrCompuesto[1]) && !empty($arrCompuesto[1])){
            $datoscuenta = $datoscuenta->where("dc.fkGrupoConcepto","=",$arrCompuesto[1]);
        }
        if(isset($arrCompuesto[2]) && !empty($arrCompuesto[2])){
            $datoscuenta = $datoscuenta->where("dc.subTipoConsulta","=",$arrCompuesto[2]);
        }
        if(isset($arrCompuesto[3]) && !empty($arrCompuesto[3])){
            $datoscuenta = $datoscuenta->where("dc.fkConcepto","=",$arrCompuesto[3]);
        }
        if(isset($arrCompuesto[4]) && !empty($arrCompuesto[4])){
            $datoscuenta = $datoscuenta->where("cat.fkEmpresa","=",$arrCompuesto[4]);
        }
        if(isset($arrCompuesto[5]) && !empty($arrCompuesto[5])){
            $datoscuenta = $datoscuenta->where("dc.fkCentroCosto","=",$arrCompuesto[5]);
        }
        $datoscuenta = $datoscuenta->delete();
        return response()->json([
            "success" => true,
            "mensaje" => "La transacción fue eliminada"
        ]);

    }
    public function getFormEdit($idCompuesto){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $arrCompuesto = explode('_', $idCompuesto);


        
        $datoscuenta = DB::table("datoscuenta","dc")
        ->select("dc.*","cat.*", "dc.fkCentroCosto as fkCentroCosto2")
        ->join("catalgocontable as cat", "cat.idCatalgoContable", "=","dc.fkCuenta")
        ->where("dc.tablaConsulta","=",$arrCompuesto[0]);
        if(isset($arrCompuesto[1]) && !empty($arrCompuesto[1])){
            $datoscuenta = $datoscuenta->where("dc.fkGrupoConcepto","=",$arrCompuesto[1]);
        }
        if(isset($arrCompuesto[2]) && !empty($arrCompuesto[2])){
            $datoscuenta = $datoscuenta->where("dc.subTipoConsulta","=",$arrCompuesto[2]);
        }
        if(isset($arrCompuesto[3]) && !empty($arrCompuesto[3])){
            $datoscuenta = $datoscuenta->where("dc.fkConcepto","=",$arrCompuesto[3]);
        }
        if(isset($arrCompuesto[4]) && !empty($arrCompuesto[4])){
            $datoscuenta = $datoscuenta->where("cat.fkEmpresa","=",$arrCompuesto[4]);
        }
        if(isset($arrCompuesto[5]) && !empty($arrCompuesto[5])){
            $datoscuenta = $datoscuenta->where("dc.fkCentroCosto","=",$arrCompuesto[5]);
        }else{
            $datoscuenta = $datoscuenta->whereNull("dc.fkCentroCosto");
        }
        
        $datoscuenta = $datoscuenta->get();
        

        $datosCuentaCred = array();
        $datosCuentaDeb = array();
        foreach($datoscuenta as $datocuenta){
            if($datocuenta->tipoCuenta == "CREDITO"){
                $datosCuentaCred = $datocuenta;
            }
            if($datocuenta->tipoCuenta == "DEBITO"){
                $datosCuentaDeb = $datocuenta;
            }
        }




        












        $cuentas = DB::table("catalgocontable")
        ->where("fkEmpresa","=",$arrCompuesto[4]);
       
        
        $cuentas = $cuentas->get();

        $conceptos = DB::table("concepto")->orderBy("nombre")->get();

        $terceros = DB::table("tercero")->orderBy("razonSocial")->get();
        $tipoTerceroCuenta = DB::table("tipotercerocuenta")->orderBy("nombre")->get();
        $empresas = DB::table("empresa", "e");
        if(isset($dataUsu) && $dataUsu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $dataUsu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();
        $gruposConcepto  = DB::table("grupoconcepto")->orderBy("nombre")->get();
        $centrosCosto = DB::table("centrocosto")
        ->where("fkEmpresa","=",$arrCompuesto[4])
        ->get();
        

        return view('/catalogoContable.formEdit',
            [
                "cuentas" => $cuentas,
                "terceros" => $terceros, 
                "empresas" => $empresas,
                "tipoTerceroCuenta" => $tipoTerceroCuenta,
                "gruposConcepto" => $gruposConcepto,
                "centrosCosto" => $centrosCosto,
                "datosCuentaCred" => $datosCuentaCred,
                "datosCuentaDeb" => $datosCuentaDeb,
                "conceptos" => $conceptos,
                "empresaSelect" => $arrCompuesto[4],
                "centroCostoSelect" => $arrCompuesto[5],
                "dataUsu" => $dataUsu
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

    public function getCuentas($fkEmpresa = null, $fkCentroCosto = null){


        $cuentas = DB::table("catalgocontable")
        ->where("fkEmpresa","=",$fkEmpresa)
        ->where("fkCentroCosto","=",$fkCentroCosto)
        ->get();

        $html="";
        foreach($cuentas as $cuenta){
            $html.="<option value='".$cuenta->idCatalgoContable."'>".$cuenta->cuenta." - ".$cuenta->descripcion."</option>";
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
        
        $errors = array();
        
        if($req->cuentaCred == "nueva"){
            if(!isset($req->cuentaCred2)){
                array_push($errors, "Cuenta credito vacia");
            }
            if(!isset($req->descripcionCred)){
                array_push($errors, "Descripción cuenta credito vacio");
            }
            if(!isset($req->fkTipoTerceroCred)){
                array_push($errors, "Tipo tercero credito vacio");
            }
            if(!isset($req->fkTerceroCred) && $req->fkTipoTerceroCred == "8"){
                array_push($errors, "Tercero fijo credito vacio");
            }
        }
        

        if($req->cuentaDeb == "nueva"){
            if(!isset($req->cuentaDeb2)){
                array_push($errors, "Cuenta debito vacia");
            }
            if(!isset($req->descripcionDeb)){
                array_push($errors, "Descripción cuenta debito vacio");
            }
            if(!isset($req->fkTipoTerceroDeb)){
                array_push($errors, "Tipo tercero debito vacio");
            }
            if(!isset($req->fkTerceroDeb) && $req->fkTipoTerceroDeb == "8"){
                array_push($errors, "Tercero fijo debito vacio");
            }
        }

        if(!isset($req->fkEmpresa)){
            array_push($errors, "Empresa vacia");
        }
        if(!isset($req->tablaConsulta[0])){
            array_push($errors, "Tipo vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "1" && !isset($req->fkGrupoConcepto[0])){
            array_push($errors, "Grupo de concepto vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "2" && !isset($req->subTipoProvision[0])){
            array_push($errors, "provision vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "3" && !isset($req->subTipoAporteEmpleador[0])){
            array_push($errors, "Aporte empleador vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "4" && !isset($req->fkConcepto[0])){
            array_push($errors, "Concepto vacio");
        }

        if(sizeof($errors)>0){
            return response()->json(['error'=>$errors]);
        }


        $idCuentaCredito = $req->cuentaCred;
        if($req->cuentaCred == "nueva"){
            $arrCatalogo = [
                "descripcion" => $req->descripcionCred,
                "cuenta" => $req->cuentaCred2,
                "fkTipoTercero" => $req->fkTipoTerceroCred,
                "fkTercero" => $req->fkTerceroCred,
                "fkEmpresa" => $req->fkEmpresa,
                "fkCentroCosto" => $req->fkCentroCosto,
                "tipoComportamiento" => "1"
            ];
            $idCuentaCredito = DB::table("catalgocontable")->insertGetId($arrCatalogo,"idCatalgoContable");
        }


        $idCuentaDebito = $req->cuentaDeb;
        if($req->cuentaDeb == "nueva"){
            $arrCatalogo = [
                "descripcion" => $req->descripcionDeb,
                "cuenta" => $req->cuentaDeb2,
                "fkTipoTercero" => $req->fkTipoTerceroDeb,
                "fkTercero" => $req->fkTerceroDeb,
                "fkEmpresa" => $req->fkEmpresa,
                "fkCentroCosto" => $req->fkCentroCosto,
                "tipoComportamiento" => "1"
            ];
            $idCuentaDebito = DB::table("catalgocontable")->insertGetId($arrCatalogo,"idCatalgoContable");
        }
        
        foreach($req->tablaConsulta as $row => $tablaConsulta){

            if($tablaConsulta == 1){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "fkGrupoConcepto" => $req->fkGrupoConcepto[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion
                ]);
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "fkGrupoConcepto" => $req->fkGrupoConcepto[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion
                ]);
            }
            else if($tablaConsulta == 2){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "subTipoConsulta" => $req->subTipoProvision[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion
                ]);
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "subTipoConsulta" => $req->subTipoProvision[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion
                ]);
                
                
            }
            else if($tablaConsulta == 3){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "subTipoConsulta" => $req->subTipoAporteEmpleador[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion
                ]);
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "subTipoConsulta" => $req->subTipoAporteEmpleador[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion
                ]);
            }
            else if($tablaConsulta == 4){
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "fkConcepto" => $req->fkConcepto[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion
                ]);
                DB::table("datoscuenta")->insert([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "fkConcepto" => $req->fkConcepto[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion
                ]);
            }

        }
        return response()->json(["success" => true]);
    }

    public function modificar(Request $req){
        $errors = array();
        
       
        if(!isset($req->cuentaCred2)){
            array_push($errors, "Cuenta credito vacia");
        }
        if(!isset($req->descripcionCred)){
            array_push($errors, "Descripción cuenta credito vacio");
        }
        if(!isset($req->fkTipoTerceroCred)){
            array_push($errors, "Tipo tercero credito vacio");
        }
        if(!isset($req->fkTerceroCred) && $req->fkTipoTerceroCred == "8"){
            array_push($errors, "Tercero fijo credito vacio");
        }
        
        

    
        if(!isset($req->cuentaDeb2)){
            array_push($errors, "Cuenta debito vacia");
        }
        if(!isset($req->descripcionDeb)){
            array_push($errors, "Descripción cuenta debito vacio");
        }
        if(!isset($req->fkTipoTerceroDeb)){
            array_push($errors, "Tipo tercero debito vacio");
        }
        if(!isset($req->fkTerceroDeb) && $req->fkTipoTerceroDeb == "8"){
            array_push($errors, "Tercero fijo debito vacio");
        }
        

        if(!isset($req->fkEmpresa)){
            array_push($errors, "Empresa vacia");
        }
        if(!isset($req->tablaConsulta[0])){
            array_push($errors, "Tipo vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "1" && !isset($req->fkGrupoConcepto[0])){
            array_push($errors, "Grupo de concepto vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "2" && !isset($req->subTipoProvision[0])){
            array_push($errors, "provision vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "3" && !isset($req->subTipoAporteEmpleador[0])){
            array_push($errors, "Aporte empleador vacio");
        }
        if(isset($req->tablaConsulta[0]) && $req->tablaConsulta[0] == "4" && !isset($req->fkConcepto[0])){
            array_push($errors, "Concepto vacio");
        }

        if(sizeof($errors)>0){
            return response()->json(['error'=>$errors]);
        }


        $idCuentaCredito = $req->cuentaCred;
        if($req->cuentaCred == "nueva"){
            $arrCatalogo = [
                "descripcion" => $req->descripcionCred,
                "cuenta" => $req->cuentaCred2,
                "fkTipoTercero" => $req->fkTipoTerceroCred,
                "fkTercero" => $req->fkTerceroCred,
                "fkEmpresa" => $req->fkEmpresa,
                "tipoComportamiento" => "1"
            ];
            $idCuentaCredito = DB::table("catalgocontable")->insertGetId($arrCatalogo,"idCatalgoContable");
        }
        else if($idCuentaCredito == $req->cuentaCredAnt){
            $arrCatalogo = [
                "descripcion" => $req->descripcionCred,
                "cuenta" => $req->cuentaCred2,
                "fkTipoTercero" => $req->fkTipoTerceroCred,
                "fkTercero" => $req->fkTerceroCred,
                "fkEmpresa" => $req->fkEmpresa,
                "tipoComportamiento" => "1"
            ];
            DB::table("catalgocontable")->where("idCatalgoContable", "=",$idCuentaCredito)->update($arrCatalogo);
        }

        $idCuentaDebito = $req->cuentaDeb;
        if($req->cuentaDeb == "nueva"){
            $arrCatalogo = [
                "descripcion" => $req->descripcionDeb,
                "cuenta" => $req->cuentaDeb2,
                "fkTipoTercero" => $req->fkTipoTerceroDeb,
                "fkTercero" => $req->fkTerceroDeb,
                "fkEmpresa" => $req->fkEmpresa,
                "tipoComportamiento" => "1"
            ];
            $idCuentaDebito = DB::table("catalgocontable")->insertGetId($arrCatalogo,"idCatalgoContable");
        }
        else if($idCuentaDebito == $req->cuentaDebAnt){
            $arrCatalogo = [
                "descripcion" => $req->descripcionDeb,
                "cuenta" => $req->cuentaDeb2,
                "fkTipoTercero" => $req->fkTipoTerceroDeb,
                "fkTercero" => $req->fkTerceroDeb,
                "fkEmpresa" => $req->fkEmpresa,
                "tipoComportamiento" => "1"
            ];
            DB::table("catalgocontable")->where("idCatalgoContable", "=",$idCuentaDebito)->update($arrCatalogo);
        }
        
        foreach($req->tablaConsulta as $row => $tablaConsulta){

            if($tablaConsulta == 1){

                
               

                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosCredito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "fkGrupoConcepto" => $req->fkGrupoConcepto[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion,
                    "fkCentroCosto" => $req->fkCentroCosto,
                ]);
                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosDebito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "fkGrupoConcepto" => $req->fkGrupoConcepto[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion,
                    "fkCentroCosto" => $req->fkCentroCosto,
                ]);
            }
            else if($tablaConsulta == 2){
                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosCredito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "subTipoConsulta" => $req->subTipoProvision[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion
                ]);
                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosDebito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "subTipoConsulta" => $req->subTipoProvision[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion
                ]);
                
                
            }
            else if($tablaConsulta == 3){
                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosCredito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "subTipoConsulta" => $req->subTipoAporteEmpleador[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion
                ]);
                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosDebito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "subTipoConsulta" => $req->subTipoAporteEmpleador[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion
                ]);
            }
            else if($tablaConsulta == 4){
                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosCredito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaCredito,
                    "fkConcepto" => $req->fkConcepto[$row],
                    "tipoCuenta" => "CREDITO",
                    "transaccion" => $req->transaccion
                ]);
                DB::table("datoscuenta")->where("idDatosCuenta","=", $req->idDatosDebito)->update([
                    "tablaConsulta" => $tablaConsulta,
                    "fkCuenta" => $idCuentaDebito,
                    "fkConcepto" => $req->fkConcepto[$row],
                    "tipoCuenta" => "DEBITO",
                    "transaccion" => $req->transaccion
                ]);
            }

        }
        return response()->json(["success" => true]);



    }
    
    public function reporteNominaIndex(){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $empresas = DB::table("empresa", "e");
        if(isset($dataUsu) && $dataUsu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $dataUsu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();
        
        
        return view('/catalogoContable.reporteNominaIndex',
            [
                "empresas" => $empresas, 
                "dataUsu" => $dataUsu
            ]
        );
    }

    
    public function generarReporteNomina(Request $req){

        $fechaInicioMes = date("Y-m-01", strtotime($req->fechaReporte));
        $fechaFinMes = date("Y-m-t", strtotime($fechaInicioMes));

        $arrConceptosAjustePeso = [
            //["concepto" => "18", "subTipoAporteEmpleador" => "2"],
            //["concepto" => "19", "subTipoAporteEmpleador" => "1"],
            //["concepto" => "33", "subTipoAporteEmpleador" => "1"]
        ];
        

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
                    
                    if(intval($centroCostoEmpleado->porcentaje) > 0){
                        array_push($arrCentrosCosto, [
                            "id_unico" => $centroCostoEmpleado->id_uni_centro,
                            "nombre" => $centroCostoEmpleado->nombre,
                            "centroCosto" => $centroCostoEmpleado->fkCentroCosto,
                            "porcentaje" => $centroCostoEmpleado->porcentaje
                        ]);
                    }
                   
                }
            }
            else{
                $centrosCosto = DB::table("empleado_centrocosto", "ecc")
                ->join("centrocosto as cec", "cec.idcentroCosto", "=","ecc.fkCentroCosto")
                ->where("ecc.fkEmpleado", "=",$empleado->idempleado)

                ->get();
                foreach($centrosCosto as $centroCosto){
                    array_push($arrCentrosCosto, [
                        "id_unico" => $centroCosto->id_uni_centro,
                        "nombre" => $centroCosto->nombre,
                        "centroCosto" => $centroCosto->fkCentroCosto,
                        "porcentaje" => $centroCosto->porcentajeTiempoTrabajado
                    ]);
                }
            }
            
         
      
            foreach($arrCentrosCosto as $arrCentroCosto){
                
                $existenCuentasConEseCentroCosto = false;
                $datosCuentaConsulta = DB::table("datoscuenta", "dc")
                ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                ->where("dc.fkCentroCosto", "=", $arrCentroCosto["centroCosto"])
                ->orderBy("dc.fkCentroCosto")
                ->first();
                if(isset($datosCuentaConsulta)){
                    $existenCuentasConEseCentroCosto = true;
                    
                }

                //Consular por tipo la cuenta
                if($existenCuentasConEseCentroCosto){
                    $datosCuentaTipo1 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","1")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->where("dc.fkCentroCosto", "=", $arrCentroCosto["centroCosto"])
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }
                else{
                    $datosCuentaTipo1 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","1")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->whereNull("dc.fkCentroCosto")
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }
                
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
                        if(!isset( $arrayInt[1][$datoCuentaTipo1->cuenta])){
                            $arrayInt[1][$datoCuentaTipo1->cuenta] = array();
                        }


                        $diferencia = 0;
                        foreach($arrConceptosAjustePeso as $arrConceptoAjustePeso){
                            if($arrConceptoAjustePeso["concepto"] == $itemBoucher->idconcepto){
                                $diferencia =  $valor - $this->roundSup($valor, -2);
                                if($diferencia != 0){
                                    if($diferencia < 0 && $itemBoucher->con_naturaleza=="1"){
                                        if($tipoRegDif == "CREDITO"){
                                            $tipoRegDif = "DEBITO";
                                        }
                                        else{
                                            $tipoRegDif = "CREDITO";
                                        }
                                        $diferencia = $diferencia*-1;
                                    }
                                    else if($diferencia > 0 && $itemBoucher->con_naturaleza=="3"){
                                        if($tipoRegDif == "CREDITO"){
                                            $tipoRegDif = "DEBITO";
                                        }
                                        else{
                                            $tipoRegDif = "CREDITO";
                                        }    
                                    }
                                    else if($diferencia < 0 && $itemBoucher->con_naturaleza=="3"){
                                        $diferencia = $diferencia*-1;
                                    }    
                        
                                   
                                    //Consultar cuenta empleador para el concepto 
        
                                    $datosCuentaEmpleador = DB::table("datoscuenta", "dc")
                                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                                    ->where("dc.tablaConsulta","=","3")
                                    ->where("dc.subTipoConsulta","=",$arrConceptoAjustePeso["subTipoAporteEmpleador"])
                                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                                    ->where("dc.tipoCuenta","=",$tipoRegDif)
                                    ->whereRaw("(cc.fkCentroCosto is NULL or cc.fkCentroCosto = ".$arrCentroCosto["centroCosto"].")")
                                    ->orderBy("cc.fkCentroCosto")
                                    ->first();
        
                                    if(!isset( $arrayInt[1][$datosCuentaEmpleador->cuenta])){
                                        $arrayInt[1][$datosCuentaEmpleador->cuenta] = array();
                                    }
                                    $valor_original = $diferencia;
                                    $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                    if(isset($datosCuentaEmpleador)){
                                        array_push($arrayInt[1][$datosCuentaEmpleador->cuenta], 
                                            array(
                                                "arrCentrosCosto" => $arrCentroCosto,
                                                "empleado" => $empleado,
                                                "tablaConsulta" => "1",
                                                "cuenta" => $datosCuentaEmpleador->cuenta,
                                                "descripcion" => $datosCuentaEmpleador->descripcion,
                                                "transaccion" => $datosCuentaEmpleador->transaccion,
                                                "porcentaje" => $arrCentroCosto["porcentaje"],
                                                "valor" => round($diferencia),
                                                "valor_original" => $valor_original,
                                                "tipoReg" => $tipoRegDif,
                                                "idConcepto" => "",
                                                "nombreConcepto" => "",
                                                "tercero" => $this->cargarTerceroAdecuado($datosCuentaEmpleador->fkTipoTercero, $empleado, $datosCuentaEmpleador->fkTercero)
                                            )
                                        );
                                    }

                                }
                            }
                        }
                        


                        


              
                       
                        $valor_original = $valor;
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
                                "valor_original" => $valor_original,
                                "tipoReg" => $tipoReg,
                                "idConcepto" => $itemBoucher->idconcepto,
                                "nombreConcepto" => $itemBoucher->con_nombre,
                                "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo1->fkTipoTercero, $empleado, $datoCuentaTipo1->fkTercero)
                            )
                        );

                        
                            
                        
                    }                   
                }
                
                if($existenCuentasConEseCentroCosto){
                    $datosCuentaTipo2 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","2")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->where("dc.fkCentroCosto", "=", $arrCentroCosto["centroCosto"])
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }else{
                    $datosCuentaTipo2 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","2")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->whereNull("dc.fkCentroCosto")
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }
                
                //PROVISIONES
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
                    /*->whereRaw("p.fkPeriodoActivo in(
                        SELECT per.idPeriodo from periodo as per where per.fkEmpleado = '".$empleado->idempleado."' and per.fkEstado = '1'
                    )")*/
                    ->where("p.fkConcepto","=",$fkConcepto)
                    ->whereRaw("(p.mes = MONTH('".$req->fechaReporte."') and p.anio= YEAR('".$req->fechaReporte."'))")
                    ->first();
                    

                    $valor = 0;   
                    if(isset($provision->valor)){
                        $valor = $provision->valor;   
                    }
                    
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
                        $valor_original = $valor;
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
                                "valor_original" => $valor_original,
                                "tipoReg" => $tipoReg,
                                "idConcepto" => $fkConcepto,
                                "nombreConcepto" => "",
                                "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo2->fkTipoTercero, $empleado, $datoCuentaTipo2->fkTercero)
                            )
                        );
          
                    }
                    

                }
                

                if($existenCuentasConEseCentroCosto){
                    $datosCuentaTipo3 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","3")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->where("dc.fkCentroCosto", "=", $arrCentroCosto["centroCosto"])
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }
                else{
                    $datosCuentaTipo3 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","3")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->whereNull("dc.fkCentroCosto")
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }

                //APORTES
                foreach($datosCuentaTipo3 as $datoCuentaTipo3){
                    $parafiscales = DB::table("parafiscales", "para")
                    ->selectRaw("para.*")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","para.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->whereRaw("(MONTH(ln.fechaInicio) = MONTH('".$req->fechaReporte."') and YEAR(ln.fechaInicio) = YEAR('".$req->fechaReporte."'))")
                    ->whereIn("ln.fkTipoLiquidacion",["1","2","4","5","6","9"]) 
                    ->orderBy("idParafiscales","desc")
                    ->limit("1")
                    ->get();

                    if(sizeof($parafiscales)==0){
                        $parafiscales = DB::table("parafiscales", "para")
                        ->selectRaw("para.*")
                        ->join("boucherpago as bp","bp.idBoucherPago","=","para.fkBoucherPago")
                        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                        ->where("bp.fkEmpleado","=",$empleado->idempleado)
                        ->whereRaw("(MONTH(ln.fechaInicio) = MONTH('".$req->fechaReporte."') and YEAR(ln.fechaInicio) = YEAR('".$req->fechaReporte."'))")
                        ->whereIn("ln.fkTipoLiquidacion",["3"]) 
                        ->orderBy("idParafiscales","desc")
                        ->limit("1")
                        ->get();
                    }



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
                            //$valor = $this->roundSup($valor, -2);
                            /*$diferencia =  $valor - $this->roundSup($valor, -2);
                            $tipoRegDif = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);

                            if($diferencia != 0){
                                if($diferencia < 0){
                                    $diferencia = $diferencia*-1;
                                } 
                                $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($diferencia),
                                        "tipoReg" => $tipoRegDif,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }*/


                    
                            
                            $val = true; 
                                                       
                            if($val){
                                $valor_original = $valor;
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
                                        "valor_original" => $valor_original,
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
                            //$valor = $this->roundSup($valor, -2);
                            /*$diferencia =  $valor - $this->roundSup($valor, -2);
                            $tipoRegDif = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);

                            if($diferencia != 0){
                                if($diferencia < 0){
                                    $diferencia = $diferencia*-1;
                                } 
                                $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($diferencia),
                                        "tipoReg" => $tipoRegDif,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }*/
                            $val = true;                            
                            if($val){
                                $valor_original = $valor;
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
                                        "valor_original" => $valor_original,
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

                            $diferencia =  $valor - $this->roundSup($valor, -2);
                            $tipoRegDif = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
                            //$valor = $this->roundSup($valor, -2);
                            /*if($diferencia != 0){
                                if($diferencia < 0){
                                    $diferencia = $diferencia*-1;
                                } 
                                $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($diferencia),
                                        "tipoReg" => $tipoRegDif,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }*/

                            $val = true;                            
                            if($val){
                                $valor_original = $valor;
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
                                        "valor_original" => $valor_original,
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
                            $diferencia =  $valor - $this->roundSup($valor, -2);
                            $tipoRegDif = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);
                            //$valor = $this->roundSup($valor, -2);
                            /*
                            if($diferencia != 0){
                                if($diferencia < 0){
                                    $diferencia = $diferencia*-1;
                                } 
                                $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($diferencia),
                                        "tipoReg" => $tipoRegDif,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }*/
                            $val = true;                            
                            if($val){
                                $valor_original = $valor;
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
                                        "valor_original" => $valor_original,
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
                            //$valor = $this->roundSup($valor, -2);
                            /*$diferencia =  $valor - $this->roundSup($valor, -2);
                            $tipoRegDif = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);

                            if($diferencia != 0){
                                if($diferencia < 0){
                                    $diferencia = $diferencia*-1;
                                } 
                                $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($diferencia),
                                        "tipoReg" => $tipoRegDif,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }*/

                            $val = true;                            
                            if($val){
                                $valor_original = $valor;
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
                                        "valor_original" => $valor_original,
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
                            //$valor = $this->roundSup($valor, -2);
                            /*
                            $diferencia =  $valor - $this->roundSup($valor, -2);
                            $tipoRegDif = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);

                            if($diferencia != 0){
                                if($diferencia < 0){
                                    $diferencia = $diferencia*-1;
                                } 
                                $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($diferencia),
                                        "tipoReg" => $tipoRegDif,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }*/
                            $val = true;                            
                            if($val){
                                $valor_original = $valor;
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
                                        "valor_original" => $valor_original,
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
                    else if($datoCuentaTipo3->subTipoConsulta == "7"){
                        foreach($parafiscales as $parafiscal){
                            
                            $valor = $parafiscal->fondoSolidaridad;
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
                            //$valor = $this->roundSup($valor, -2);
                            /*
                            $diferencia =  $valor - $this->roundSup($valor, -2);
                            $tipoRegDif = $this->comportamientoPorNaturaleza($datoCuentaTipo3->cuenta);

                            if($diferencia != 0){
                                if($diferencia < 0){
                                    $diferencia = $diferencia*-1;
                                } 
                                $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                array_push($arrayInt[3][$datoCuentaTipo3->cuenta], 
                                    array(
                                        "arrCentrosCosto" => $arrCentroCosto,
                                        "empleado" => $empleado,
                                        "tablaConsulta" => "3",
                                        "cuenta" => $datoCuentaTipo3->cuenta,
                                        "descripcion" => $datoCuentaTipo3->descripcion,
                                        "transaccion" => $datoCuentaTipo3->transaccion,
                                        "porcentaje" => $arrCentroCosto["porcentaje"],
                                        "valor" => round($diferencia),
                                        "tipoReg" => $tipoRegDif,
                                        "idConcepto" => "",
                                        "nombreConcepto" => "",
                                        "subTipoReg" => $datoCuentaTipo3->subTipoConsulta,
                                        "tercero" => $this->cargarTerceroAdecuado($datoCuentaTipo3->fkTipoTercero, $empleado, $datoCuentaTipo3->fkTercero)
                                    )
                                );
                            }*/
                            $val = true;                            
                            if($val){
                                $valor_original = $valor;
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
                                        "valor_original" => $valor_original,
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
               

                if($existenCuentasConEseCentroCosto){
                    //Consular por tipo la cuenta
                    $datosCuentaTipo4 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","4")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->where("dc.fkCentroCosto", "=", $arrCentroCosto["centroCosto"])
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }else{                    
                    $datosCuentaTipo4 = DB::table("datoscuenta", "dc")
                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                    ->where("dc.tablaConsulta","=","4")
                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                    ->whereNull("dc.fkCentroCosto")
                    ->orderBy("dc.fkCentroCosto")
                    ->get();
                }

               
                
                //CONCEPTOS_FIJOS
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
            
                    $itemsBoucherFueraNomina = DB::table("item_boucher_pago_fuera_nomina", "ibp")
                    ->selectRaw("ibp.pago, ibp.descuento, con.idconcepto, ibp.valor, con.nombre as con_nombre, con.fkNaturaleza as con_naturaleza")
                    ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                    ->join("concepto as con","con.idConcepto","=","ibp.fkConcepto") 
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->where("bp.fkEmpleado","=",$empleado->idempleado)
                    ->whereRaw("(MONTH(ln.fechaInicio) = MONTH('".$req->fechaReporte."') and YEAR(ln.fechaInicio) = YEAR('".$req->fechaReporte."'))")
                    ->where("ibp.fkConcepto","=",$datoCuentaTipo4->fkConcepto) 
                    ->where("ln.fkTipoLiquidacion","=","11")
                    ->get();

                    if(sizeof($itemsBoucherFueraNomina)>0){
                        foreach($itemsBoucherFueraNomina as $itemBoucherFueraNomina){
                            $itemsBoucher->push($itemBoucherFueraNomina);
                        }
                        
                    }
                    
                    


                    foreach($itemsBoucher as $itemBoucher){
                        
                        $valor = 0;
                        $tipoReg = $datoCuentaTipo4->tipoCuenta;
                        $tipoRegDif = $datoCuentaTipo4->tipoCuenta;
                        
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
                        

                        /*$diferencia = 0;
                        foreach($arrConceptosAjustePeso as $arrConceptoAjustePeso){
                            if($arrConceptoAjustePeso["concepto"] == $itemBoucher->idconcepto){
                                $diferencia =  $valor - $this->roundSup($valor, -2);
                                if($diferencia != 0){
                                    if($diferencia < 0 && $itemBoucher->con_naturaleza=="1"){
                                        if($tipoRegDif == "CREDITO"){
                                            $tipoRegDif = "DEBITO";
                                        }
                                        else{
                                            $tipoRegDif = "CREDITO";
                                        }
                                        $diferencia = $diferencia*-1;
                                    }
                                    else if($diferencia > 0 && $itemBoucher->con_naturaleza=="3"){
                                        if($tipoRegDif == "CREDITO"){
                                            $tipoRegDif = "DEBITO";
                                        }
                                        else{
                                            $tipoRegDif = "CREDITO";
                                        }    
                                    }
                                    else if($diferencia < 0 && $itemBoucher->con_naturaleza=="3"){
                                        $diferencia = $diferencia*-1;
                                    }    
                        
                                   
                                    //Consultar cuenta empleador para el concepto 
        
                                    $datosCuentaEmpleador = DB::table("datoscuenta", "dc")
                                    ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
                                    ->where("dc.tablaConsulta","=","3")
                                    ->where("dc.subTipoConsulta","=",$arrConceptoAjustePeso["subTipoAporteEmpleador"])
                                    ->where("cc.fkEmpresa","=",$empleado->fkEmpresa)
                                    ->where("dc.tipoCuenta","=",$tipoRegDif)
                                    ->whereRaw("(cc.fkCentroCosto is NULL or cc.fkCentroCosto = ".$arrCentroCosto["centroCosto"].")")
                                    ->orderBy("cc.fkCentroCosto")
                                    ->first();
        
                                    if(!isset( $arrayInt[3][$datosCuentaEmpleador->cuenta])){
                                        $arrayInt[3][$datosCuentaEmpleador->cuenta] = array();
                                    }
                                    
                                    $diferencia = $diferencia * ($arrCentroCosto["porcentaje"]/100);
                                                                     
                                    if(isset($datosCuentaEmpleador)){
                                        if(sizeof($arrayInt[3][$datosCuentaEmpleador->cuenta]) > 0){
                                            //dd($arrayInt[3][$datoCuentaTipo4->cuenta][0]["valor"], $diferencia, $datoCuentaTipo4, $itemBoucher->idconcepto);

                                            $arrayInt[3][$datosCuentaEmpleador->cuenta][0]["valor"] = round($diferencia) + $arrayInt[3][$datosCuentaEmpleador->cuenta][0]["valor"];
                                        }else{
                                            array_push($arrayInt[3][$datosCuentaEmpleador->cuenta], 
                                                array(
                                                    "arrCentrosCosto" => $arrCentroCosto,
                                                    "empleado" => $empleado,
                                                    "tablaConsulta" => "1",
                                                    "cuenta" => $datosCuentaEmpleador->cuenta,
                                                    "descripcion" => $datosCuentaEmpleador->descripcion,
                                                    "transaccion" => $datosCuentaEmpleador->transaccion,
                                                    "porcentaje" => $arrCentroCosto["porcentaje"],
                                                    "valor" => round($diferencia),
                                                    "tipoReg" => $tipoRegDif,
                                                    "idConcepto" => "",
                                                    "nombreConcepto" => "",
                                                    "tercero" => $this->cargarTerceroAdecuado($datosCuentaEmpleador->fkTipoTercero, $empleado, $datosCuentaEmpleador->fkTercero)
                                                )
                                            );
                                        }
                                        
                                    }
                                    
                                }
                            }
                        }   */
                       

                        


                       /* $val = true;
                        if(!isset( $arrayInt[4][$datoCuentaTipo4->cuenta]) && !isset($arrayInt[3][$datoCuentaTipo4->cuenta])){
                            $arrayInt[4][$datoCuentaTipo4->cuenta] = array();
                        }
                        else if(!isset( $arrayInt[4][$datoCuentaTipo4->cuenta])){
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

                        }*/
                        $val = true;
                        if(!isset( $arrayInt[4][$datoCuentaTipo4->cuenta])){
                            $arrayInt[4][$datoCuentaTipo4->cuenta] = array();
                        }
                        else{
                            $porcentajeInterno = 0;
                            foreach($arrayInt[4][$datoCuentaTipo4->cuenta] as $arrCuentaInt4){
                                if($arrCuentaInt4["idConcepto"] == $itemBoucher->idconcepto){
                                    $porcentajeInterno = $porcentajeInterno + $arrCuentaInt4["porcentaje"];
                                }
                                
                            }
                            if($porcentajeInterno > 100){
                                $val = true;
                            }
    
                        }
                        
                        if($val){
                            $valor_original = $valor;
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
                                    "valor_original" => $valor_original,
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
                "Valor Crédito",
                "Tipo Cuenta",
                "Valor",
                "Tercero"
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
        //dd($arrSalida);

        foreach($arrSalida as $key => $arrSalid){
            foreach($arrSalid as $key2 => $arrSalid2){
                foreach($arrSalid2 as $key3 =>  $arrSalid3){                    
                    $cuentaDecimales5 = 0;
                    foreach($arrSalid3 as $key4 => $arrSalid4){

                        $valor = $arrSalid4["valor_original"] * ($arrSalid4["porcentaje"]/100);     
                        $valorEntero = floor($valor);
                        $diferencia = $valor - $valorEntero;
                        if($diferencia == 0.5){
                            $cuentaDecimales5++;
                        }
                        $arrSalida[$key][$key2][$key3][$key4]["diff"] = $diferencia;

                        if($cuentaDecimales5 == 2){
                            $arrSalida[$key][$key2][$key3][$key4]["valor"] = $arrSalid4["valor"] - 1;
                            $cuentaDecimales5 = 0;
                           
                        }
                    }
                }
            }
        }
        //dd($arrSalida);


        foreach($arrSalida as $arrSalid){
            foreach($arrSalid as $arrSalid2){
                foreach($arrSalid2 as $arrSalid3){                    
                    foreach($arrSalid3 as $arrSalid4){
                        $valorDebito = "0";
                        $valorCredito = "0";
                        $tipoCuenta = "";

                        if($arrSalid4["tipoReg"]=="DEBITO"){
                            $valorDebito = $arrSalid4["valor"];
                            $tipoCuenta = "D";
                        }
                        else{
                            $valorCredito = $arrSalid4["valor"];
                            $tipoCuenta = "C";
                        }
                        
                        if($arrSalid4["valor"] != 0){

                            $arrDefInt = [
                                $fechaInforme,//0
                                $arrSalid4["cuenta"],//1
                                $arrSalid4["descripcion"],
                                $arrSalid4["nombreConcepto"],
                                $arrSalid4["arrCentrosCosto"]["id_unico"],//4
                                $arrSalid4["arrCentrosCosto"]["nombre"],//5
                                $arrSalid4["porcentaje"],//6
                                $arrSalid4["tercero"]["idTercero"],//7
                                $arrSalid4["tercero"]["digitoVer"],//8
                                $arrSalid4["tercero"]["nomTercero"],//9
                                $arrSalid4["transaccion"],//10
                                $arrSalid4["empleado"]->numeroIdentificacion,//11
                                $arrSalid4["empleado"]->primerApellido." ".$arrSalid4["empleado"]->segundoApellido." ".$arrSalid4["empleado"]->primerNombre." ".$arrSalid4["empleado"]->segundoNombre,//12
                                $valorDebito,//13
                                $valorCredito,//14
                                $tipoCuenta,//15
                                $arrSalid4["valor"],//16
                                $arrSalid4["tercero"]["idTercero"].($arrSalid4["tercero"]["digitoVer"] != "" ? "-".$arrSalid4["tercero"]["digitoVer"] : "")
                            ];
                            $val = true;
                            /*ifforeach($arrDef as $row => $val){
                               if(  $val[1]==$arrSalid4["cuenta"] &&
                                    $val[4] == $arrSalid4["arrCentrosCosto"]["id_unico"] &&
                                    $val[5] == $arrSalid4["arrCentrosCosto"]["nombre"] &&
                                    $val[6] == $arrSalid4["porcentaje"] &&
                                    $val[7] == $arrSalid4["tercero"]["idTercero"] &&
                                    $val[8] == $arrSalid4["tercero"]["digitoVer"] &&
                                    $val[9] == $arrSalid4["tercero"]["nomTercero"] &&
                                    $val[11] == $arrSalid4["empleado"]->numeroIdentificacion &&
                                    $val[12] == ($arrSalid4["empleado"]->primerApellido." ".$arrSalid4["empleado"]->segundoApellido." ".$arrSalid4["empleado"]->primerNombre." ".$arrSalid4["empleado"]->segundoNombre) &&
                                    $val[15] == $tipoCuenta
                                    ){
                                    $arrDef[$row][13] = intval($arrDef[$row][13]) + intval($valorDebito);
                                    $arrDef[$row][14] = intval($arrDef[$row][14]) + intval($valorCredito);
                                    $arrDef[$row][16] = intval($arrDef[$row][16]) + intval($arrSalid4["valor"]);
                                    $val = false;
                                }
                                
                            }*/
                            

                            if($val == true){
                                array_push($arrDef, $arrDefInt);
                            }
                            
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
        $dataUsu = UsuarioController::dataAdminLogueado();
        $cargas = DB::table("carga_catalogo_contable","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->orderBy("cdp.idCarga", "desc")
        ->get();

        return view('/catalogoContable.subirPlano', ["cargas" => $cargas, "dataUsu" => $dataUsu]);
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
        

        $dataUsu = UsuarioController::dataAdminLogueado();

        $datosCuentas = DB::table("catalogo_contable_plano","ccp")
        ->select("ccp.*", "ttc.nombre as nombreTipoTercero", "est.nombre as estado","t.razonSocial as nombreTercero", 
                "e.razonSocial as nombreEmpresa", "cc.nombre as nombreCentroCosto","gc.nombre as nombreGrupoConcepto",
                "c.nombre as nombreConcepto")
        ->join("tipotercerocuenta as ttc","ttc.idTipoTerceroCuenta", "=","ccp.fkTipoTercero", "left")
        ->join("tercero as t","t.idTercero", "=","ccp.fkTerceroFijo", "left")
        ->join("empresa as e","e.idempresa", "=","ccp.fkEmpresa", "left")
        ->join("centrocosto as cc","cc.idcentroCosto", "=","ccp.fkCentroCosto", "left")
        ->join("grupoconcepto as gc","gc.idgrupoConcepto", "=","ccp.fkGrupoConcepto", "left")
        ->join("concepto as c","c.idconcepto", "=","ccp.fkConcepto", "left")
        ->join("estado as est", "est.idEstado", "=", "ccp.fkEstado")
        ->where("ccp.fkCarga","=",$idCarga)
        ->get();
        
        

        return view('/catalogoContable.verCarga', [
            "cargas" => $cargas,
            "datosCuentas" => $datosCuentas,
            "dataUsu" => $dataUsu
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
                            
                            /*$dt = DateTime::createFromFormat("d/m/Y", $row[$key]);
                            if($dt === false){
                                $dt = new DateTime();
                            }
                            $ts = $dt->getTimestamp();
                            $row[$key] = date("Y-m-d", $ts);*/
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
                        if(intval($row[4])<=0 || intval($row[4])>7 ){
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
                        "fkTipoAporteEmpleador" => $row[4],
                        "fkTipoProvision" => $row[3],      
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
                            "numActual" =>  ($i),
                            "mensaje" => $mensaje,
                            "porcentaje" => ceil((($i) / $cargaDatos->numRegistros)*100)."%"
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
               
            }
            DB::table("carga_catalogo_contable")
            ->where("idCarga","=",$idCarga)
            ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);
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

                $existeCat = DB::table("catalgocontable")
                ->where("cuenta","=",$datoCuenta->cuenta)
                ->where("fkEmpresa","=",$datoCuenta->fkEmpresa)
                ->where("fkCentroCosto","=",$datoCuenta->fkCentroCosto)
                ->first();
                if(isset($existeCat)){
                    continue;
                }


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
                if(!isset($cuenta1)){
                    $cuenta1 = DB::table("catalgocontable")
                    ->where("fkEmpresa","=",$datoCuenta->fkEmpresa2)
                    ->whereNull("fkCentroCosto")
                    ->where("cuenta","=",$datoCuenta->cuenta1)
                    ->first();
                }



                $cuenta2 = DB::table("catalgocontable")
                ->where("fkEmpresa","=",$datoCuenta->fkEmpresa2)
                ->where("fkCentroCosto","=",$datoCuenta->fkCentroCosto2)
                ->where("cuenta","=",$datoCuenta->cuenta2)
                ->first();
                if(!isset($cuenta2)){
                    $cuenta2 = DB::table("catalgocontable")
                    ->where("fkEmpresa","=",$datoCuenta->fkEmpresa2)
                    ->whereNull("fkCentroCosto")
                    ->where("cuenta","=",$datoCuenta->cuenta2)
                    ->first();
                }

                if(!isset($cuenta1) || !isset($cuenta2)){
                    DB::table("catalogo_contable_plano")
                    ->where("idCartalogoContablePlano","=",$datoCuenta->idCartalogoContablePlano)
                    ->update(["fkEstado" => "28"]);
                }
                else{
                    

                    $existeDatoCuenta = DB::table("datoscuenta")
                    ->where("tablaConsulta" ,"=", $datoCuenta->tipoConsulta)
                    ->where("fkCuenta","=",$cuenta1->idCatalgoContable)
                    ->where("fkGrupoConcepto","=",$datoCuenta->fkGrupoConcepto)
                    ->where("fkConcepto","=", $datoCuenta->fkConcepto)
                    ->where("fkCentroCosto","=", $datoCuenta->fkCentroCosto2)
                    ->where("tipoCuenta","=","DEBITO")
                    ->where("subTipoConsulta","=", $tipo)
                    ->where("transaccion", "=", $datoCuenta->transaccion)->first();


                    if(isset($existeDatoCuenta)){
                        continue;
                    }

                    $arrInsertDatosCuenta = [
                        "tablaConsulta" => $datoCuenta->tipoConsulta,
                        "fkCuenta" => $cuenta1->idCatalgoContable,
                        "fkGrupoConcepto" => $datoCuenta->fkGrupoConcepto,
                        "fkConcepto" => $datoCuenta->fkConcepto,
                        "tipoCuenta" => "DEBITO",
                        "subTipoConsulta" => $tipo,
                        "transaccion" => $datoCuenta->transaccion,
                        "fkCentroCosto" => $datoCuenta->fkCentroCosto2
                    ];  
                    
                    DB::table("datoscuenta")->insert($arrInsertDatosCuenta);
    
                    $arrInsertDatosCuenta = [
                        "tablaConsulta" => $datoCuenta->tipoConsulta,
                        "fkCuenta" => $cuenta2->idCatalgoContable,
                        "fkGrupoConcepto" => $datoCuenta->fkGrupoConcepto,
                        "fkConcepto" => $datoCuenta->fkConcepto,
                        "tipoCuenta" => "CREDITO",
                        "subTipoConsulta" => $tipo,
                        "transaccion" => $datoCuenta->transaccion,
                        "fkCentroCosto" => $datoCuenta->fkCentroCosto2
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

    public function descargarPlano(){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $empresas = DB::table("empresa", "e");
        if(isset($dataUsu) && $dataUsu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $dataUsu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();

        return view('/catalogoContable.descargarPlano', ["empresas" => $empresas, "dataUsu" => $dataUsu]);
    }
    
    public function descargarArchivoxEmpresa(Request $req){
        $cuentasEmpresa = DB::table("catalgocontable")->where("fkEmpresa","=",$req->idempresa)->get();
        $arrDef = array();
        foreach($cuentasEmpresa as $cuentaEmpresa){
            array_push($arrDef, [
                "1",
                $cuentaEmpresa->cuenta,
                $cuentaEmpresa->descripcion,
                $cuentaEmpresa->fkTipoTercero,
                $cuentaEmpresa->fkTercero,
                $cuentaEmpresa->fkEmpresa,
                $cuentaEmpresa->fkCentroCosto
            ]);
        }
        $arrIntermedio = array();
        $datoscuenta = DB::table("datoscuenta","dc")
        ->join("catalgocontable as cc", "cc.idCatalgoContable", "=","dc.fkCuenta")
        ->where("cc.fkEmpresa","=",$req->idempresa)->get();
        foreach($datoscuenta as $datocuenta){
            $id = $datocuenta->tablaConsulta."_".$datocuenta->fkGrupoConcepto."_".$datocuenta->subTipoConsulta."_".$datocuenta->fkConcepto;
            $fkTipoProvision = "";
            $fkTipoAporteEmpleador = "";
            

            if($datocuenta->tablaConsulta=="2"){
                $fkTipoProvision = $datocuenta->subTipoConsulta;
            }
            else if($datocuenta->tablaConsulta=="3"){
                $fkTipoAporteEmpleador = $datocuenta->subTipoConsulta;
            }

            $cuentaDebito = "";
            $cuentaCredito = "";
            
            if($datocuenta->tipoCuenta == "DEBITO"){
                $cuentaDebito = $datocuenta->cuenta;
            }
            else if($datocuenta->tipoCuenta == "CREDITO"){
                $cuentaCredito = $datocuenta->cuenta;
            }


            $transaccion = "";
            if($datocuenta->transaccion=="Aportes"){
                $transaccion = "1";
            }
            else if($datocuenta->transaccion=="Provisiones"){
                $transaccion = "2";
            }
            else if($datocuenta->transaccion=="Nomina"){
                $transaccion = "3";
            }            
            if(!isset($arrIntermedio[$id])){
                $arrIntermedio[$id] = [
                    0 => "2",
                    1 => $datocuenta->tablaConsulta,
                    2 => $datocuenta->fkGrupoConcepto,
                    3 => $fkTipoProvision,
                    4 => $fkTipoAporteEmpleador,
                    5 => $datocuenta->fkConcepto,
                    6 => $cuentaDebito,
                    7 => $cuentaCredito,
                    8 => $datocuenta->fkEmpresa,
                    9 => $datocuenta->fkCentroCosto,
                    10 => $transaccion
                ];
            }
            else{
                if($datocuenta->tipoCuenta == "DEBITO"){
                    $arrIntermedio[$id][6] = $cuentaDebito;
                }
                else if($datocuenta->tipoCuenta == "CREDITO"){
                    $arrIntermedio[$id][7] = $cuentaCredito;
                }
            }
        }

        //Transformar intermedio en def
        foreach($arrIntermedio as $intermedio){
            array_push($arrDef, $intermedio);
        }
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=CatalogoContable'.$req->idempresa.'.csv');
        
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($arrDef);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->output('CatalogoContable'.$req->idempresa.'.csv');

        
    }


    public function roundSup($numero, $presicion){
        $redondeo = $numero / pow(10,$presicion*-1);
        
        $redondeo = ceil($redondeo);
        $redondeo = $redondeo * pow(10,$presicion*-1);
        return $redondeo;
    }
    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
