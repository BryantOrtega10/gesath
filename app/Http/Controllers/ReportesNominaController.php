<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use League\Csv\Reader;
use SplTempFileObject;          
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use App\User;
use Illuminate\Support\Facades\Auth;

class ReportesNominaController extends Controller
{
    private $rutaBaseImagenes = "/home/xtspamqf/public_html/gesathWeb/public/"; 

    

    public function reporteNominaHorizontalIndex(){
        $empresas = DB::table("empresa","e")->get();

        return view('/reportes.nominaHorizontal',["empresas" => $empresas]);
    }
    public function reporteNominaAcumuladoIndex(){
        $empresas = DB::table("empresa","e")->get();
        $conceptos = DB::table("concepto","c")->orderBy("c.nombre")->get();
        return view('/reportes.nominaAcumulado',["empresas" => $empresas, "conceptos" => $conceptos]);
    }
    
    public function documentoNominaHorizontal($idLiquidacionNomina){
        $nominas = DB::table("item_boucher_pago", "ibp")
        ->select("c.idconcepto","c.nombre","ln.fechaLiquida","e.idempleado", 
        "dp.primerNombre","dp.segundoNombre", 
        "dp.primerApellido","dp.segundoApellido","ti.nombre as tipoidentificacion", 
        "dp.numeroIdentificacion", "bp.diasTrabajados", "ibp.valor", "ln.fechaLiquida","ccfijo.valor as valorSalario", "cargo.nombreCargo")
        ->join("concepto as c","c.idconcepto", "=","ibp.fkConcepto")
        ->join("boucherpago as bp","bp.idBoucherPago","=", "ibp.fkBoucherPago")
        ->join("liquidacionnomina as ln", "ln.idLiquidacionNomina", "=","bp.fkLiquidacion")
        ->join("empleado as e","e.idempleado", "=", "bp.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
        ->join("conceptofijo as ccfijo","ccfijo.fkEmpleado", "=", "e.idempleado")
        ->join("cargo","cargo.idCargo","=","e.fkCargo")
        ->where("ln.idLiquidacionNomina","=",$idLiquidacionNomina)
        ->whereIn("ccfijo.fkConcepto",["1","2"])
        ->orderBy("e.idempleado")
        ->orderBy("c.idconcepto")
        ->get();
        
        $matrizReporte = array();
        
        foreach($nominas as $nomina){
            $centroCosto = DB::table("centrocosto","cc")
            ->join("empleado_centrocosto as ec","ec.fkCentroCosto","=","cc.idcentroCosto")
            ->where("ec.fkEmpleado","=",$nomina->idempleado)->first();

            $matrizReporte[$nomina->idempleado]["Fecha Liquidacion"] = $nomina->fechaLiquida;
            $matrizReporte[$nomina->idempleado]["Centro Costo"] = $centroCosto->nombre;
            $matrizReporte[$nomina->idempleado]["Tipo Documento"] = $nomina->tipoidentificacion;
            $matrizReporte[$nomina->idempleado]["Documento"] = $nomina->numeroIdentificacion;            
            $matrizReporte[$nomina->idempleado]["Nombre"] = $nomina->primerApellido." ".$nomina->segundoApellido." ".$nomina->primerNombre." ".$nomina->segundoNombre;
            $matrizReporte[$nomina->idempleado]["Sueldo"] = intval($nomina->valorSalario);
            $matrizReporte[$nomina->idempleado]["Cargo"] = $nomina->nombreCargo;
            $matrizReporte[$nomina->idempleado]["Dias"] = $nomina->diasTrabajados;
            
            $matrizReporte[$nomina->idempleado][$nomina->nombre] = $nomina->valor;

        }

        $arrDefLinea1 = array();
        $arrDef = array();
        foreach($matrizReporte as $matriz){ 
  
            foreach($matriz as $row => $datoInt){
        
                if(!is_int($datoInt) || $row=="Sueldo"){
                    if(!in_array($row, $arrDefLinea1)){
                        array_push($arrDefLinea1, $row);
                    }
                }              
                
            }
           
        }



        foreach($matrizReporte as $matriz){ 
            foreach($matriz as $row => $datoInt){
                
                if(is_int($datoInt) && $datoInt > 0 && $row != "Sueldo" && $row != "Dias"){
                    
                    if(!in_array($row, $arrDefLinea1)){
                        array_push($arrDefLinea1, $row);
                        
                    }
                }
                            
            }
        }
   
        array_push($arrDefLinea1, "TOTAL PAGOS");


        foreach($matrizReporte as $matriz){ 
            foreach($matriz as $row => $datoInt){
                
                if(is_int($datoInt) && $datoInt < 0){
                    if(!in_array($row, $arrDefLinea1)){
                        array_push($arrDefLinea1, $row);
                    }                    
                }
                
            }
            
        }
       

        array_push($arrDefLinea1, "TOTAL DESCUENTO");
        array_push($arrDefLinea1, "NETO PAGAR");



        $idDefPagos = array_search("TOTAL PAGOS", $arrDefLinea1);
        $idDefDesc = array_search("TOTAL DESCUENTO", $arrDefLinea1);
        $idDefTotal = array_search("NETO PAGAR", $arrDefLinea1);
        foreach($matrizReporte as $matriz){ 
           
            $arrFila = array();            
            foreach($matriz as $row => $datoInt){               
                $idDef = array_search($row, $arrDefLinea1);
        

                if(is_int($datoInt) && $row != "Dias" && $row != "Sueldo"){
                    $arrFila[$idDefTotal] = (isset($arrFila[$idDefTotal]) ? $arrFila[$idDefTotal]+ $datoInt : $datoInt);    
                    
                }

                    
                if(is_int($datoInt) && $datoInt<0){
                    $arrFila[$idDefDesc]= (isset($arrFila[$idDefDesc]) ? $arrFila[$idDefDesc] + ($datoInt*-1) : ($datoInt*-1)); 
                    $arrFila[$idDef] = $datoInt*-1;    
                }
                else if(is_int($datoInt) && $datoInt>0 && $row != "Dias" && $row != "Sueldo"){
                    $arrFila[$idDefPagos] = (isset($arrFila[$idDefPagos]) ? $arrFila[$idDefPagos] + $datoInt : $datoInt);
                    $arrFila[$idDef] = $datoInt;    
                }
                else{
                    $arrFila[$idDef] = $datoInt;    
                }          
                    
            }
            if(!empty($arrFila)){
                array_push($arrDef, $arrFila);  
            }
        }
 
      
      
        $reporteFinal = array();
        $reporteFinal[0] = $arrDefLinea1;
        
        foreach($arrDef as $datos){
            
            foreach($arrDefLinea1 as $row => $datoLinea1){
                if(!isset($datos[$row])){
                    $datos[$row] = 0;
                }
            }
            ksort($datos);
            array_push($reporteFinal, $datos);
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=NominaHorizontal_'.$idLiquidacionNomina.'.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($reporteFinal);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->output('NominaHorizontal_'.$idLiquidacionNomina.'.csv');

    }
    public function documentoNominaFechas(Request $req){
        $nominas = DB::table("item_boucher_pago", "ibp")
        ->select("c.idconcepto","c.nombre","ln.fechaLiquida","e.idempleado", "e.fechaIngreso", 
        "dp.primerNombre","dp.segundoNombre", 
        "dp.primerApellido","dp.segundoApellido","ti.nombre as tipoidentificacion", 
        "dp.numeroIdentificacion", "bp.diasTrabajados", "ibp.valor", "bp.idBoucherPago", "cargo.nombreCargo", "ibp.cantidad", "ibp.tipoUnidad")
        ->join("concepto as c","c.idconcepto", "=","ibp.fkConcepto")
        ->join("boucherpago as bp","bp.idBoucherPago","=", "ibp.fkBoucherPago")
        ->join("liquidacionnomina as ln", "ln.idLiquidacionNomina", "=","bp.fkLiquidacion")
        ->join("empleado as e","e.idempleado", "=", "bp.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
        ->join("nomina as n","n.idNomina", "=","ln.fkNomina")
        ->join("cargo","cargo.idCargo","=","e.fkCargo")
        ->whereBetween("ln.fechaLiquida",[$req->fechaInicio, $req->fechaFin])
        ->where("n.fkEmpresa", "=", $req->empresa);
        if(isset($req->concepto)){
            
            $nominas = $nominas->whereIn("c.idconcepto", $req->concepto);
        }
        if(isset($req->idEmpleado)){
            $nominas = $nominas->where("e.idempleado", "=", $req->idEmpleado);
        }
        $nominas = $nominas->orderBy("ln.fechaLiquida")
        ->orderBy("e.idempleado")
        ->orderBy("c.idconcepto")
        ->get();

        $matrizReporte = array();
       
        foreach($nominas as $nomina){

            $matrizReporte[$nomina->idempleado]["Tipo Documento"][$nomina->idconcepto] = $nomina->tipoidentificacion;
            $matrizReporte[$nomina->idempleado]["Documento"][$nomina->idconcepto] = $nomina->numeroIdentificacion;            
            $matrizReporte[$nomina->idempleado]["Empleado"][$nomina->idconcepto] = $nomina->primerApellido." ".$nomina->segundoApellido." ".$nomina->primerNombre." ".$nomina->segundoNombre;
            $matrizReporte[$nomina->idempleado]["Cargo"][$nomina->idconcepto] = $nomina->nombreCargo;
            $matrizReporte[$nomina->idempleado]["Fecha ingreso"][$nomina->idconcepto] = $nomina->fechaIngreso;            
            $matrizReporte[$nomina->idempleado]["Concepto"][$nomina->idconcepto] = $nomina->nombre;
            $matrizReporte[$nomina->idempleado][$nomina->fechaLiquida][$nomina->idconcepto] = $nomina->valor;
            $matrizReporte[$nomina->idempleado][$nomina->fechaLiquida." UNIDADES"][$nomina->idconcepto] = $nomina->cantidad;
            $matrizReporte[$nomina->idempleado][$nomina->fechaLiquida." TIPO UNIDAD"][$nomina->idconcepto] = $nomina->tipoUnidad;


        }
        
        $arrDefLinea1 = array();
        $arrDef = array();
        
        foreach($matrizReporte as $matriz){ 
            $arrFila = array();
            foreach($matriz as $row => $dato){
                foreach($dato as $rowInt => $datoInt){                   
                    if(!in_array($row, $arrDefLinea1)){
                        array_push($arrDefLinea1, $row);
                    }
                    $idDef = array_search($row, $arrDefLinea1);
                    $arrFila[$idDef][$rowInt] = $datoInt;         
                }
            }
            if(!empty($arrFila)){
                array_push($arrDef, $arrFila);  
            }            
        }
      
        
        
        $reporteFinal = array();
        $reporteFinal[0] = $arrDefLinea1;
        
        foreach($arrDef as $key => $datos){
            foreach($arrDefLinea1 as $row => $datoLinea1){
                if(!isset($datos[$row])){
                    $datos[$row] = 0;
                }
            }
            ksort($datos);
            $arrDef[$key] = $datos;
            
        }
        
        foreach($arrDef as $datos){
            $arrEntrega= array();
            foreach($datos as $row => $vData){
                foreach($datos[0] as $idBouc => $datosInt){
                    if(isset($vData[$idBouc])){
                        if(is_array($vData[$idBouc])){
                            $arrEntrega[$idBouc][$row] = $vData[$idBouc];
                        }
                        else{
                            $arrEntrega[$idBouc][$row]= $vData[$idBouc];
                        }
                    }
                    else{
                        $arrEntrega[$idBouc][$row] = " ";
                    }
                    
                    ksort($arrEntrega);
                }           
            }
            foreach($arrEntrega as $porfin){
                array_push($reporteFinal, $porfin);
            }
            
            
             
        }


        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=NominaHorizontal_'.$req->empresa.'_'.$req->fechaInicio.'_'.$req->fechaFin.'.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($reporteFinal);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->output('NominaHorizontal_'.$req->empresa.'_'.$req->fechaInicio.'_'.$req->fechaFin.'.csv');
    }
    public function documentoNominaHorizontalFechas(Request $req){
        $nominas = DB::table("item_boucher_pago", "ibp")
        ->selectRaw("c.idconcepto,c.nombre,ln.fechaLiquida,e.idempleado, 
        dp.primerNombre,dp.segundoNombre,
        dp.primerApellido,dp.segundoApellido,ti.nombre as tipoidentificacion, 
        dp.numeroIdentificacion, bp.diasTrabajados, ibp.valor, ibp.cantidad, 
        bp.idBoucherPago, ccfijo.valor as valorSalario,c.fkNaturaleza,
        emp.razonSocial as nom_empresa,nom.nombre as nom_nomina,
        (SELECT centrocosto.nombre from centrocosto where idcentroCosto in
        (Select empleado_centrocosto.fkCentroCosto from empleado_centrocosto where empleado_centrocosto.fkEmpleado = e.idempleado) 
        limit 0,1) as centroCosto")        
        ->join("concepto as c","c.idconcepto", "=","ibp.fkConcepto")
        ->join("boucherpago as bp","bp.idBoucherPago","=", "ibp.fkBoucherPago")
        ->join("liquidacionnomina as ln", "ln.idLiquidacionNomina", "=","bp.fkLiquidacion")
        ->join("empleado as e","e.idempleado", "=", "bp.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
        ->join("nomina as n","n.idNomina", "=","ln.fkNomina")
        ->join("conceptofijo as ccfijo","ccfijo.fkEmpleado", "=", "e.idempleado")
        ->join("empresa as emp","emp.idempresa", "=", "e.fkEmpresa")
        ->join("nomina as nom","nom.idNomina", "=", "e.fkNomina")
        ->whereBetween("ln.fechaLiquida",[$req->fechaInicio, $req->fechaFin])
        ->where("n.fkEmpresa", "=", $req->empresa)
        ->whereIn("ccfijo.fkConcepto",["1","2"])
        ->orderBy("ln.fechaLiquida")
        ->orderBy("e.idempleado")
        ->orderBy("c.idconcepto")
        ->get();


        


        $matrizReporte = array();
        
        foreach($nominas as $nomina){

            $matrizReporte[$nomina->idempleado]["Fecha Liquidacion"][$nomina->idBoucherPago] = $nomina->fechaLiquida;
            $matrizReporte[$nomina->idempleado]["Tipo Documento"][$nomina->idBoucherPago] = $nomina->tipoidentificacion;
            $matrizReporte[$nomina->idempleado]["Documento"][$nomina->idBoucherPago] = $nomina->numeroIdentificacion;     
            $matrizReporte[$nomina->idempleado]["Empresa"][$nomina->idBoucherPago] = $nomina->nom_empresa;     
            $matrizReporte[$nomina->idempleado]["Nomina"][$nomina->idBoucherPago] = $nomina->nom_nomina;     
            $matrizReporte[$nomina->idempleado]["Centro costo"][$nomina->idBoucherPago] = $nomina->centroCosto;     
            
            
            if($nomina->idconcepto == "1" || $nomina->idconcepto == "2"){
                $matrizReporte[$nomina->idempleado]["Dias"][$nomina->idBoucherPago] = intval($nomina->cantidad);
            }
            $matrizReporte[$nomina->idempleado]["Sueldo"][$nomina->idBoucherPago] = intval($nomina->valorSalario);
            

            $matrizReporte[$nomina->idempleado]["Nombre"][$nomina->idBoucherPago] = $nomina->primerApellido." ".$nomina->segundoApellido." ".$nomina->primerNombre." ".$nomina->segundoNombre;
            $matrizReporte[$nomina->idempleado][$nomina->nombre][$nomina->idBoucherPago]["valor"] = $nomina->valor;
            $matrizReporte[$nomina->idempleado][$nomina->nombre][$nomina->idBoucherPago]["naturaleza"] = $nomina->fkNaturaleza;
        }
        

        


        $arrDefLinea1 = array();
        $arrDef = array();
        foreach($matrizReporte as $matriz){ 
            foreach($matriz as $row => $dato){
                foreach($dato as $rowInt => $datoInt){
                    if(!is_array($datoInt)){
                        if(!in_array($row, $arrDefLinea1)){
                            array_push($arrDefLinea1, $row);
                        }
                    }              
                }
            }
           
        }
        foreach($matrizReporte as $matriz){ 
            foreach($matriz as $row => $dato){
                foreach($dato as $rowInt => $datoInt){
                    if(is_array($datoInt)){
                        if($datoInt['naturaleza']=="1"){
                            if(!in_array($row, $arrDefLinea1)){
                                array_push($arrDefLinea1, $row);
                            }                            
                        }
                    }
                }
            }
        }
        array_push($arrDefLinea1, "TOTAL PAGOS");

        foreach($matrizReporte as $matriz){ 
            foreach($matriz as $row => $dato){
                foreach($dato as $rowInt => $datoInt){
                    if(is_array($datoInt)){
                        if($datoInt['naturaleza']=="3"){
                            if(!in_array($row, $arrDefLinea1)){
                                array_push($arrDefLinea1, $row);
                            }                            
                        }
                    }
                }
            }
        }
        array_push($arrDefLinea1, "TOTAL DESCUENTO");
        array_push($arrDefLinea1, "NETO PAGAR");
        $idDefPagos = array_search("TOTAL PAGOS", $arrDefLinea1);
        $idDefDesc = array_search("TOTAL DESCUENTO", $arrDefLinea1);
        $idDefTotal = array_search("NETO PAGAR", $arrDefLinea1);
        
        foreach($matrizReporte as $matriz){ 
            $arrFila = array();
            
            foreach($matriz as $row => $dato){
               

                foreach($dato as $rowInt => $datoInt){
                    $idDef = array_search($row, $arrDefLinea1);
         

                    if(is_array($datoInt) && $row != "Dias" && $row != "Sueldo"){
                        $arrFila[$idDefTotal][$rowInt] = (isset($arrFila[$idDefTotal][$rowInt]) ? $arrFila[$idDefTotal][$rowInt] + $datoInt['valor'] : $datoInt['valor']);    
                    }  
                    if(is_array($datoInt) && $datoInt['naturaleza'] == "3"){
                        $arrFila[$idDefDesc][$rowInt] = (isset($arrFila[$idDefDesc][$rowInt]) ? $arrFila[$idDefDesc][$rowInt]  + ($datoInt['valor']*-1) : ($datoInt['valor']*-1)); 
                        $arrFila[$idDef][$rowInt] = $datoInt['valor']*-1;    
                    }
                    else if(is_array($datoInt) && $datoInt['naturaleza'] == "1" && $row != "Dias" && $row != "Sueldo"){
                        $arrFila[$idDefPagos][$rowInt] = (isset($arrFila[$idDefPagos][$rowInt]) ? $arrFila[$idDefPagos][$rowInt] + $datoInt['valor'] : $datoInt['valor']);
                        $arrFila[$idDef][$rowInt] = $datoInt['valor'];    
                    }
                    else{
                        $arrFila[$idDef][$rowInt] = $datoInt;    
                    }
                    
                    
                    
                }
                

            }
            if(!empty($arrFila)){
                array_push($arrDef, $arrFila);  
            }
        }



        

        $reporteFinal = array();
        $reporteFinal[0] = $arrDefLinea1;
        
        foreach($arrDef as $key => $datos){
            foreach($arrDefLinea1 as $row => $datoLinea1){
                if(!isset($datos[$row])){
                    $datos[$row] = 0;
                }
            }
            ksort($datos);
            $arrDef[$key] = $datos;
            
        }
        
        foreach($arrDef as $datos){
            $arrEntrega= array();
            foreach($datos as $row => $vData){
                foreach($datos[0] as $idBouc => $datosInt){
                    if(isset($vData[$idBouc])){
                        if(is_array($vData[$idBouc])){
                            $arrEntrega[$idBouc][$row] = $vData[$idBouc];
                        }
                        else{
                            $arrEntrega[$idBouc][$row]= $vData[$idBouc];
                        }
                    }
                    else{
                        $arrEntrega[$idBouc][$row] = 0;
                    }
                    
                    ksort($arrEntrega);
                }           
            }
            foreach($arrEntrega as $porfin){
                array_push($reporteFinal, $porfin);
            }
        }


        $reporteDatosJuntos = array($reporteFinal[0]);
        $empleadoActual = 0;
        for($i = 1; $i<sizeof($reporteFinal) ;$i++){

            if($empleadoActual != $reporteFinal[$i][2]){
                $empleadoActual = $reporteFinal[$i][2];
            }
       
            $existeEmp = -1;
            foreach($reporteDatosJuntos as $row => $reporteTemp){
                if($reporteTemp[2] == $empleadoActual){
                    //Existe el empleado en el reporte conjunto
                    $existeEmp = $row;
                }
            }
            
            
            if($existeEmp== -1){
                array_push($reporteDatosJuntos, $reporteFinal[$i]);
            }
            else{
                foreach($reporteFinal[$i] as $row => $columna){
                    if(is_numeric($columna) && $row!=2 && $row!=4 && $row!=7){
                        $reporteDatosJuntos[$existeEmp][$row] = $reporteDatosJuntos[$existeEmp][$row] + $columna;
                    }
                }
            }

        }


        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=NominaHorizontal_'.$req->empresa.'_'.$req->fechaInicio.'_'.$req->fechaFin.'.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($reporteDatosJuntos);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->output('NominaHorizontal_'.$req->empresa.'_'.$req->fechaInicio.'_'.$req->fechaFin.'.csv');
    }

    public function boucherPagoPdf($idBoucherPago){

        $empresayLiquidacion = DB::table("empresa", "e")
        ->select("e.*", "ln.*", "n.nombre as nom_nombre", "bp.*")
        ->join("nomina as n","n.fkEmpresa", "e.idempresa")
        ->join("liquidacionnomina as ln","ln.fkNomina", "n.idNomina")
        ->join("boucherpago as bp","bp.fkLiquidacion", "ln.idLiquidacionNomina")
        ->where("bp.idBoucherPago","=",$idBoucherPago)
        ->first();
       
        
        $empleado = DB::table("empleado","e")
        ->select("e.idempleado", "e.fechaIngreso","e.tipoRegimen", "e.fkNomina",
        "dp.primerNombre","dp.segundoNombre", 
        "dp.primerApellido","dp.segundoApellido","ti.nombre as tipoidentificacion", 
        "dp.numeroIdentificacion", "cargo.nombreCargo")
        ->join("boucherpago as bp","bp.fkEmpleado", "e.idempleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
        ->join("cargo","cargo.idCargo","=","e.fkCargo")
        ->where("bp.idBoucherPago","=",$idBoucherPago)
        ->first();
        $nomina = DB::table("nomina","n")
        ->where("n.idNomina","=",$empleado->fkNomina)->first();
        $pension = DB::table("tercero", "t")->
        select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero", "ti.nombre as tipoidentificacion", "t.digitoVer"])
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
        ->join("afiliacion as a","t.idTercero", "=","a.fkTercero")
        ->where("a.fkEmpleado","=",$empleado->idempleado)
        ->where("a.fkTipoAfilicacion","=","4") //4-Pensión Obligatoria 
        ->first();

        $salud = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero",
        "ti.nombre as tipoidentificacion", "t.digitoVer"])
       ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
       ->join("afiliacion as a","t.idTercero", "=","a.fkTercero")
       ->where("a.fkEmpleado","=",$empleado->idempleado)
       ->where("a.fkTipoAfilicacion","=","3") //3-Salud
       ->first();

       $cesantiasEmp = DB::table("tercero", "t")->select(["t.razonSocial", "t.numeroIdentificacion", "t.idTercero",
        "ti.nombre as tipoidentificacion", "t.digitoVer"])
       ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
       ->join("afiliacion as a","t.idTercero", "=","a.fkTercero")
       ->where("a.fkEmpleado","=",$empleado->idempleado)
       ->where("a.fkTipoAfilicacion","=","2") //2-CCF
       ->first();

       $entidadBancaria = DB::table("tercero", "t")->select(["t.razonSocial", "e.numeroCuenta"])
       ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
       ->join("empleado as e", "e.fkEntidad", "=","t.idTercero")
       ->where("e.idempleado","=",$empleado->idempleado)
       ->first();

       $idItemBoucherPago = DB::table("item_boucher_pago","ibp")
       ->join("concepto AS c","c.idconcepto","=", "ibp.fkConcepto")
       ->where("ibp.fkBoucherPago","=",$idBoucherPago)
       ->get();

       $conceptoSalario = DB::table("conceptofijo")->where("fkEmpleado","=",$empleado->idempleado)
        ->whereIn("fkConcepto",[1,2])->first();
        
        //VACACIONES
        $novedadesVacacionActual = DB::table("novedad","n")
        ->select("v.*", "c.nombre","c.idconcepto", "ibpn.valor")
        ->join("concepto as c","c.idconcepto", "=","n.fkConcepto")
        ->join("vacaciones as v","v.idVacaciones","=","n.fkVacaciones")
        ->join("item_boucher_pago_novedad as ibpn","ibpn.fkNovedad","=","n.idNovedad")
        ->join("item_boucher_pago as ibp","ibp.idItemBoucherPago","=","ibpn.fkItemBoucher")
        ->where("ibp.fkBoucherPago","=",$idBoucherPago)
        ->whereIn("n.fkEstado",["8","7"]) // Pagada -> no que este eliminada
        ->whereNotNull("n.fkVacaciones")
        ->get();
        //$diasVac = $totalPeriodoPagoAnioActual * 15 / 360;
        





        $base64 = "";
        if(is_file($this->rutaBaseImagenes.'storage/logosEmpresas/'.$empresayLiquidacion->logoEmpresa)){
            $imagedata = file_get_contents($this->rutaBaseImagenes.'storage/logosEmpresas/'.$empresayLiquidacion->logoEmpresa);
                     // alternatively specify an URL, if PHP settings allow
            $base64 = base64_encode($imagedata);
        }
        

        $arrMeses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

        $dompdf = new Dompdf();
        $dompdf->getOptions()->setChroot($this->rutaBaseImagenes);
        $dompdf->getOptions()->setIsPhpEnabled(true);
        $html='
        <html>
        <body>
            <style>
            *{
                -webkit-hyphens: auto;
                -ms-hyphens: auto;
                hyphens: auto;
                font-family: sans-serif;                
            }
            td{
                text-align: left;
                font-size: 12px;
            }
            th{
                text-align: left;
                font-size: 12px;
            }
            .liquida td, .liquida th{
                font-size:11px;
            }
            
            @page { 
                margin: 0in;
            }
            .page {
                top: .3in;
                right: .3in;
                bottom: .3in;
                left: .3in;
                position: absolute;
                z-index: -1000;
                min-width: 7in;
                min-height: 11.7in;
                
            }
            .page_break { 
                page-break-before: always; 
            }
        
            </style>
            ';
            if($empresayLiquidacion->fkTipoLiquidacion == "7"){
                $html.='<div class="page liquida">
                <div style="border: 2px solid #000; padding: 5px 10px; font-size: 15px; margin-bottom: 5px;">
                    <img style="float:left; max-width: 40px; max-height: 40px; margin-right: 5px;" src="'.(isset($empresayLiquidacion->logoEmpresa) ? "data:image/png;base64,'.$base64.'" : '').'" class="logoEmpresa" />
                    <b>'.$empresayLiquidacion->razonSocial.'</b>
                    <br>
                    <b>'.$empresayLiquidacion->documento.'-'.$empresayLiquidacion->digitoVerificacion.'</b>
                    <center>
                        <h2 style="margin:0; margin-bottom: 0px; font-size: 20px;">COMPROBANTE DE PAGO DE NÓMINA</h2><br>
                    </center>
                    <table style="width: 100%;">
                        <tr>
                            <th>Nómina</th>
                            <td>'.$empresayLiquidacion->nom_nombre.'</td>
                            <th>Período liquidación</th>
                            <td>
                                '.date("Y",strtotime($empresayLiquidacion->fechaInicio))."/".$arrMeses[date("m",strtotime($empresayLiquidacion->fechaInicio)) - 1].'/'.date("d",strtotime($empresayLiquidacion->fechaInicio)).' 
                                a
                                '.date("Y",strtotime($empresayLiquidacion->fechaFin))."/".$arrMeses[date("m",strtotime($empresayLiquidacion->fechaFin)) - 1].'/'.date("d",strtotime($empresayLiquidacion->fechaFin)).' 
                            </td>
                        </tr>
                        <tr>
                            <th>Empleado</th>
                            <td>'.$empleado->primerApellido." ".$empleado->segundoApellido." ".$empleado->primerNombre." ".$empleado->segundoNombre.'</td>
                            <th>Salario</th>
                            <td>$ '.number_format($conceptoSalario->valor,0, ",", ".").'</td>
                        </tr>
                        <tr>
                            <th>Identificación</th>
                            <td>'.$empleado->tipoidentificacion.' '.$empleado->numeroIdentificacion.'</td>
                            <th>Cargo</th>
                            <td>'.$empleado->nombreCargo.'</td>
                        </tr>
                        <tr>
                            <th>Entidad Bancaria</th>
                            <td>'.(isset($entidadBancaria->razonSocial) ? $entidadBancaria->razonSocial : "").'</td>
                            <th>Cuenta</th>
                            <td>'.(isset($entidadBancaria->numeroCuenta) ? $entidadBancaria->numeroCuenta : "").'</td>
                        </tr>
                        <tr>
                            <th>EPS</th>
                            <td>'.$salud->razonSocial.'</td>
                            <th>Fondo Pensiones</th>
                            <td>'.(isset($pension->razonSocial) ? $pension->razonSocial : "").'</td>
                        </tr>
                        
                    </table>
                    <br>
                </div>
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <th style="background: #d89290; text-align: center;" colspan="2">Devengado</th>
                                <th style="background: #d89290; text-align: center;">Deducciones</th>                        
                            </tr>
                            <tr>
                                <th style="background: #CCC; text-align: center;">Conceptos Liquidados</th>
                                <th style="background: #CCC; text-align: center;">Cantidad</th>
                                <th style="background: #CCC; text-align: center;">Unidad</th>
                                <th style="background: #CCC; text-align: center;">Pagos</th>
                                <th style="background: #CCC; text-align: center;">Beneficios</th>
                                <th style="background: #CCC; text-align: center;">Descuentos</th>
                            </tr>';
                            $totalDesc = 0;
                            $totalPag = 0;
                
                            foreach($idItemBoucherPago as $itemBoucherPago){
                                $html.='<tr style="border-bottom: 1px solid #B0B0B0;">
                                    <td style="border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->nombre.'</td>
                                    <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.((15 / 180) * $itemBoucherPago->cantidad).'</td>
                                    <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->tipoUnidad.'</td>';
                                    
                                    if($itemBoucherPago->valor > 0){
                                        $html.='<td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$'.number_format($itemBoucherPago->valor,0, ",", ".").'</td>
                                            <td style="border-bottom: 1px solid #B0B0B0;"></td>
                                            <td style="border-bottom: 1px solid #B0B0B0;"></td>';
                                        $totalPag = $totalPag + $itemBoucherPago->valor;
                                    }
                                    else{
                                        $html.='<td style="border-bottom: 1px solid #B0B0B0;"></td>
                                            <td style="border-bottom: 1px solid #B0B0B0;"></td>
                                            <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$'.number_format($itemBoucherPago->valor*-1,0, ",", ".").'</td>';
                                        $totalDesc = $totalDesc + $itemBoucherPago->valor;
                                    }

                                $html.='</tr>';
                            }
                            $html.='<tr>
                                        
                                        <th colspan="3" style="text-align: right;">Totales</th>
                                        <td style="text-align: right; border: 1px solid #B0B0B0;">$'.number_format($totalPag,0, ",", ".").'</td>
                                        <td style="text-align: right; border: 1px solid #B0B0B0;"></td>
                                        <td style="text-align: right;border: 1px solid #B0B0B0;" >$'.number_format($totalDesc*-1,0, ",", ".").'</td>
                                    </tr>
                            ';
                            $totalGen = $totalPag + $totalDesc;
                            if($totalGen<0){
                                $totalGen=0;
                            }
                            $html.='<tr>
                                        
                                        <th colspan="3" style="text-align: right;" >Neto a pagar en cuenta nómina</th>
                                        <td style="text-align: right;border: 1px solid #B0B0B0;" colspan="2">$'.number_format($totalGen,0, ",", ".").'</td>
                                        
                                    </tr>
                            ';
                            
                        $html.='</table>

                    </div>
                    <br>
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <th style="background: #CCC; text-align: center;" colspan="2">Bases para cálculo de seguridad social</th>
                            </tr>
                            <tr>
                                <td>Ingreso Base Cotización Salud</td><td style="text-align: right;">$'.number_format($empresayLiquidacion->ibc_eps,0, ",", ".").'</td>
                            </tr>
                            <tr>
                                <td>Ingreso Base Cotización Pension</td><td style="text-align: right;">$'.number_format($empresayLiquidacion->ibc_afp,0, ",", ".").'</td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <th style="background: #CCC; text-align: center;" colspan="2">Mensaje Empresarial</th>
                            </tr>
                            <tr>
                                <td style="text-align: justify;">Si tienes preguntas acerca de la atención de pacientes con infección respiratoria, comunícate con el Ministerio de Salud y Protección Social al número telefónico: Bogotá (1) 330 50 41 |
                                Línea gratuita nacional 01 8000 955 590 | Fuera del país +571 330 50 41. Informarnos correctamente es la primera línea de defensa ante el Coronavirus (COVID-19).</td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div style="position: absolute; bottom: 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <td>COLABORADOR</td>
                                <td></td>
                                <td>LA EMPRESA</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Cédula o NIT</td>
                                <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                                <td>NIT</td>
                                <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td>Fecha de elaboración:&nbsp;&nbsp;&nbsp; '.date("d/m/Y").'</td>
                            </tr>
                        </table>

                    </div>
                </div>';
            }
            else if($empresayLiquidacion->fkTipoLiquidacion == "2" || $empresayLiquidacion->fkTipoLiquidacion == "3"){
                $novedadesRetiro = DB::table("novedad","n")
                ->select("r.fecha", "r.fechaReal","mr.nombre as motivoRet")
                ->join("retiro AS r", "r.idRetiro","=","n.fkRetiro")
                ->join("motivo_retiro as mr","mr.idMotivoRetiro","=","r.fkMotivoRetiro")
                ->where("n.fkEmpleado", "=", $empleado->idempleado)
                ->whereIn("n.fkEstado",["7", "8"])
                ->whereNotNull("n.fkRetiro")
                ->whereBetween("n.fechaRegistro",[$empresayLiquidacion->fechaInicio, $empresayLiquidacion->fechaFin])->first();

                $contrato = DB::table("contrato","c")
                ->join("tipocontrato as tc","tc.idtipoContrato", "=","c.fkTipoContrato")
                ->where("c.fkEmpleado","=",$empleado->idempleado)
                ->where("c.fkEstado","=",["1","2"])->first();
                
                $cambioSalario = DB::table("cambiosalario","cs")
                ->where("cs.fkEmpleado","=",$empleado->idempleado)
                ->where("cs.fkEstado","=","5")
                ->first();
                $fechaUltimoCamSal = $empleado->fechaIngreso;
                if(isset($cambioSalario)){
                    $fechaUltimoCamSal = $cambioSalario->fechaCambio;
                }

                $diasLab = $this->days_360($empleado->fechaIngreso, $novedadesRetiro->fecha);
                $meses = intval($diasLab/30);
                $diasDemas = $diasLab - ($meses * 30);
                $tiempoTrabTxt = $meses." Meses ".$diasDemas." días";

                $html.='                    
                <div class="page liquida">
                    <div style="border: 2px solid #000; padding: 5px 10px; font-size: 15px; margin-bottom: 5px;">
                        <img style="float:left; max-width: 40px; max-height: 40px; margin-right: 5px;" src="'.(isset($empresayLiquidacion->logoEmpresa) ? "data:image/png;base64,'.$base64.'" : '').'" class="logoEmpresa" />
                        <b>'.$empresayLiquidacion->razonSocial.'</b>
                        <br>
                        <b>'.$empresayLiquidacion->documento.'-'.$empresayLiquidacion->digitoVerificacion.'</b>
                        <center>
                            <h2 style="margin:0; margin-bottom: 0px; font-size: 20px;">LIQUIDACIÓN DE CONTRATO</h2>
                        </center>
                    </div>
                    <table style="width: 96%; text-align: left;">
                        <tr>
                            <th>
                                Nómina
                            </th>
                            <td>
                                '.$empresayLiquidacion->nom_nombre.'
                            </td>
                            <th>
                                Período liquidación
                            </th>
                            <td>
                                '.date("Y",strtotime($empresayLiquidacion->fechaInicio))."/".$arrMeses[date("m",strtotime($empresayLiquidacion->fechaInicio)) - 1].'/'.date("d",strtotime($empresayLiquidacion->fechaInicio)).' 
                                a
                                '.date("Y",strtotime($empresayLiquidacion->fechaFin))."/".$arrMeses[date("m",strtotime($empresayLiquidacion->fechaFin)) - 1].'/'.date("d",strtotime($empresayLiquidacion->fechaFin)).' 
                            </td>
                        </tr>
                        <tr>
                            <th>Empleado</th>
                            <td>'.$empleado->primerApellido." ".$empleado->segundoApellido." ".$empleado->primerNombre." ".$empleado->segundoNombre.'</td>
                            <th>Fecha ingreso</th>
                            <td>'.date("Y",strtotime($empleado->fechaIngreso))."/".$arrMeses[date("m",strtotime($empleado->fechaIngreso)) - 1].'/'.date("d",strtotime($empleado->fechaIngreso)).'</td>
                        </tr>
                        <tr>
                            <th>Identificación</th>
                            <td>'.$empleado->tipoidentificacion.' '.$empleado->numeroIdentificacion.'</td>
                            <th>Fecha Retiro</th>
                            <td>'.date("Y",strtotime($novedadesRetiro->fecha))."/".$arrMeses[date("m",strtotime($novedadesRetiro->fecha)) - 1].'/'.date("d",strtotime($novedadesRetiro->fecha)).'</td>
                        </tr>
                        <tr>
                            <th>Tipo Contrato</th>
                            <td>'.$contrato->nombre.'</td>
                            <th>Fecha Retiro Real</th>
                            <td>'.date("Y",strtotime($novedadesRetiro->fechaReal))."/".$arrMeses[date("m",strtotime($novedadesRetiro->fechaReal)) - 1].'/'.date("d",strtotime($novedadesRetiro->fechaReal)).'</td>
                        </tr>
                        <tr>
                            <th>Nómina</th>
                            <td>'.$empresayLiquidacion->nom_nombre.'</td>
                            <th>Fecha Último Aumento Salario</th>
                            <td>
                                '.date("Y",strtotime($fechaUltimoCamSal))."/".$arrMeses[date("m",strtotime($fechaUltimoCamSal)) - 1].'/'.date("d",strtotime($fechaUltimoCamSal)).' 
                            </td>
                        </tr>
                        <tr>
                            <th>Régimen</th>
                            <td>'.$empleado->tipoRegimen.'</td>
                            <th>Última Nómina Pagada</th>
                            <td>
                                '.date("Y",strtotime($empresayLiquidacion->fechaLiquida))."/".$arrMeses[date("m",strtotime($empresayLiquidacion->fechaLiquida)) - 1].'/'.date("d",strtotime($empresayLiquidacion->fechaLiquida)).' 
                            </td>
                        </tr>
                        <tr>
                            <th>Tiempo Trabajado</th>
                            <td>'.$tiempoTrabTxt.'</td>
                            <th>Cargo</th>
                            <td>'.$empleado->nombreCargo.'</td>
                            </td>
                        </tr>
                        <tr>
                            <th>Salario</th>
                            <td>$ '.number_format($conceptoSalario->valor,0, ",", ".").'</td>
                            <th>EPS</th>
                            <td>'.$salud->razonSocial.'</td>
                        </tr>
                        <tr>
                            <th>Entidad Bancaria</th>
                            <td>'.(isset($entidadBancaria->razonSocial) ? $entidadBancaria->razonSocial : "").'</td>
                            <th>Cuenta</th>
                            <td>'.(isset($entidadBancaria->numeroCuenta) ? $entidadBancaria->numeroCuenta : "").'</td>
                        </tr>
                        <tr>
                            <th>Fondo Pensiones</th>
                            <td>'.(isset($pension->razonSocial) ? $pension->razonSocial : "").'</td>
                            <th>IBL Seguridad Social </th>
                            <td>$ '.number_format($empresayLiquidacion->ibc_eps,0, ",", ".").'</td>
                        </tr>
                        <tr>
                            <th>Fondo Cesantías </th>
                            <td>'.(isset($cesantiasEmp->razonSocial) ? $cesantiasEmp->razonSocial : "").'</td>
                            <th>Motivo Retiro</th>
                            <td>'.$novedadesRetiro->motivoRet.'</td>
                        </tr>
                    </table>
                    <br>';
                    $basePrima = 0;
                    $baseCes = 0;
                    $baseVac = 0;

                    $fechaInicioCes = "";
                    $fechaInicioPrima = "";
                    $fechaInicioVac = $empleado->fechaIngreso;

                    $fechaFinCes = "";
                    $fechaFinPrima = "";
                    $fechaFinVac = $novedadesRetiro->fecha;

                    $diasCes = "";
                    $diasPrima = "";
                    $diasVac = "";

                
                    foreach($idItemBoucherPago as $itemBoucherPago){
                        if($itemBoucherPago->fkConcepto == 30){
                            $baseVac = $itemBoucherPago->base;
                            $diasVac = $itemBoucherPago->cantidad;
                        }

                        if($itemBoucherPago->fkConcepto == 58){
                            $basePrima = $itemBoucherPago->base;
                            $fechaInicioPrima =  $itemBoucherPago->fechaInicio;
                            $fechaFinPrima =  $itemBoucherPago->fechaFin;                            
                            $diasPrima = (15 / 180) * $itemBoucherPago->cantidad;
                        }
                        
                        if($itemBoucherPago->fkConcepto == 66){
                            $baseCes = $itemBoucherPago->base;
                            $fechaInicioCes =  $itemBoucherPago->fechaInicio;
                            $fechaFinCes =  $itemBoucherPago->fechaFin;
                            $diasCes = (($itemBoucherPago->cantidad * $nomina->diasCesantias) / 360);
                        }
                        
                    }
                    $html.='<div style="border: 2px solid #000; padding: 0px 10px; margin-bottom: 5px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <th style="background: #CCC; text-align: center;" colspan="6">Promedio Liquidación Prestaciones</th>
                            </tr>
                            <tr>
                                <th>Promedio Cesantías</th>
                                <td>$'.number_format($baseCes,0, ",", ".").'</td>
                                <th>Promedio Vacaciones</th>
                                <td>$'.number_format($baseVac,0, ",", ".").'</td>
                                <th>Promedio Prima</th>
                                <td>$'.number_format($basePrima,0, ",", ".").'</td>
                            </tr>
                        </table>
                    </div>';
                    $html.='<div style="border: 2px solid #000; padding: 0px 10px; margin-bottom: 5px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <th style="background: #CCC; text-align: center;" colspan="5">Valores Consolidados</th>
                            </tr>
                            <tr>
                                <th  style="background: #CCC; text-align: center;">Tipo Consolidado </th>
                                <th  style="background: #CCC; text-align: center;">Fecha Inicio</th>
                                <th  style="background: #CCC; text-align: center;">Fecha Fin</th>
                                <th  style="background: #CCC; text-align: center;">Total Días</th>
                            </tr>
                            <tr>
                                <th>Cesantías Consolidadas A</th>
                                <td>'.$fechaInicioCes.'</td>
                                <td>'.$fechaFinCes.'</td>
                                <td>'.round($diasCes,2).'</td>
                            </tr>
                            <tr>
                                <th>Ultimo Pago Prima Legal</th>
                                <td>'.$fechaInicioPrima.'</td>
                                <td>'.$fechaFinPrima.'</td>
                                <td>'.round($diasPrima,2).'</td>
                            </tr>
                            <tr>
                                <th>Vacaciones Consolidadas A</th>
                                <td>'.$fechaInicioVac.'</td>
                                <td>'.$fechaFinVac.'</td>
                                <td>'.round($diasVac,2).'</td>
                            </tr>
                        </table>
                    </div>
                    <div style="border: 2px solid #000; padding: 0px 10px; margin-bottom: 5px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <td colspan="4"></td>
                                <th style="background: #d89290; text-align: center;" colspan="2">Pagos y Descuentos</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th style="background: #CCC; text-align: center;">Conceptos Liquidados</th>
                                <th style="background: #CCC; text-align: center;">Cantidad</th>
                                <th style="background: #CCC; text-align: center;">Unidad</th>
                                <th style="background: #CCC; text-align: center;">Base</th>
                                <th style="background: #CCC; text-align: center;">Pagos</th>
                                <th style="background: #CCC; text-align: center;">Descuentos</th>
                                <th style="background: #CCC; text-align: center;">Saldo Cuota</th>                                
                            </tr>';
                            $totalDesc = 0;
                            $totalPag = 0;
                            foreach($idItemBoucherPago as $itemBoucherPago){
                                $html.='<tr style="border-bottom: 1px solid #B0B0B0;">
                                    <td style="border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->nombre.'</td>';
                                    if($itemBoucherPago->fkConcepto == 58){
                                        $html.='<td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.round($diasPrima,2).'</td>';
                                    }
                                    else if($itemBoucherPago->fkConcepto == 66 || $itemBoucherPago->fkConcepto == 69){
                                        
                                        $html.='<td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.round($diasCes,2).'</td>';
                                    }
                                    else{
                                        $html.='<td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->cantidad.'</td>';
                                    }

                                    $html.='
                                    <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->tipoUnidad.'</td>
                                    <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$'.number_format($itemBoucherPago->base,0, ",", ".").'</td>';

                                    
                                    
                                    if($itemBoucherPago->valor > 0){
                                        $html.='<td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$'.number_format($itemBoucherPago->valor,0, ",", ".").'</td>
                                            <td style="border-bottom: 1px solid #B0B0B0;"></td>';
                                        $totalPag = $totalPag + $itemBoucherPago->valor;
                                    }
                                    else{
                                        $html.='<td style="border-bottom: 1px solid #B0B0B0;"></td>
                                            <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$'.number_format($itemBoucherPago->valor*-1,0, ",", ".").'</td>';
                                        $totalDesc = $totalDesc + $itemBoucherPago->valor;
                                    }
                                    $html.='<td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$0</td>';

                                $html.='</tr>';
                            }
                            $html.='<tr>
                                        
                                        <th colspan="4" style="text-align: right;">Totales</th>
                                        <th style="text-align: right; border: 1px solid #B0B0B0;">$'.number_format($totalPag,0, ",", ".").'</td>
                                        <td style="text-align: right;border: 1px solid #B0B0B0;" >$'.number_format($totalDesc*-1,0, ",", ".").'</td>
                                    </tr>
                            ';
                            $totalGen = $totalPag + $totalDesc;
                            if($totalGen<0){
                                $totalGen=0;
                            }
                            $html.='<tr>
                                        
                                        <th colspan="3" style="text-align: right;" >Neto a pagar</th>
                                        <td style="text-align: right;border: 1px solid #B0B0B0;" colspan="2">$'.number_format($totalGen,0, ",", ".").'</td>
                                        
                                    </tr>
                            ';
                            $valorText =$this->convertir(($totalPag + $totalDesc));
                            
                        $html.='</table>
                    </div>
                    <div style="border: 2px solid #000; padding: 10px 20px; font-size: 10px; font-weight: bold; margin-bottom: 5px;">
                        El valor neto a pagar es: '.strtoupper($valorText).' PESOS M/CTE
                    </div>
                    <div style="border: 2px solid #000; padding: 0px 10px; margin-bottom: 5px;">
                        <center><h4 style="margin:0px;" >Observaciones</h4></center>
                        <table>
                            <tr>
                                <th style="background: #CCC; text-align: center;">CONSTANCIAS - Se hace constar expresamente los siguiente:</th>
                            </tr>
                            <td style="font-size: 8px; text-align: justify;">
                            (1) Que el empleador ha incorporado en la anterior liquidación, en lo pertinente, la totalidad de los valores correspondientes a salarios, horas extras, recargos por trabajo noctur
                            descansos remunerados, cesantía, intereses de cesantía, vacaciones, accidentes de trabajo, primas, calzado y overoles, auxilio de transporte y, en general, todo concepto relacionado
                            con salarios, descansos, prestaciones, indemnizaciones de toda especie y en general, por toda acreencia laboral que tengan por causa el contrato de trabajo que ha quedado
                            extinguido. (2) En consideración a que la obtención de los datos contables, elaboración y revisión de la presente liquidación, su aprobación y el giro de cheques, ha exigido varios días,
                            por lo cual ha sido físicamente imposible pagar en el instante de la terminación del contrato, el trabajador conviene expresamente en que el término transcurrido entre la terminación
                            del contrato y la fecha de esta liquidación y pago ha sido el necesario razonable y prudencial para éstos efectos y que, en consecuencia, no ha habido mora en el pago. (3) Que no
                            obstante la anterior declaración, se hace constar por las partes que con el pago de la suma de dinero a que hace referencia la presente liquidación, queda transada cualquier
                            diferencia relativa al contrato de trabajo que ha quedado terminado, pues ha sido su común ánimo transar definitivamente, como en efecto se transa, todo reclamo pasado, presente o
                            futuro que le tenga por causa el mencionado contrato. Por consiguiente, esta transacción tiene como efecto la extinción de las obligaciones provenientes de la relación laboral que
                            existió entre empleador y trabajador, quienes recíprocamente se declaran a Paz y Salvo por los conceptos expresados, excepto en cuanto a derechos ciertos e indiscutibles del
                            trabajador que, por cualquier circunstancia, estén pendientes de reconocimiento o pago. (Art 15 CST). (4) Se deja constancia, que se entrega orden para examen médico de egreso
                            </td>
                        </table>
                        <table style="width: 100%;">
                            <tr>
                                <th style="border: 1px solid #000;">ELABORÓ:</th>
                                <th style="border: 1px solid #000;">REVISÓ:</th>
                                <th style="border: 1px solid #000;">APROBÓ:</th>
                            </tr>
                        </table>
                    </div>
                    <div style="position: absolute; bottom: 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <td>COLABORADOR</td>
                                <td></td>
                                <td>LA EMPRESA</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Cédula o NIT</td>
                                <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                                <td>NIT</td>
                                <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td>Fecha de elaboración:&nbsp;&nbsp;&nbsp; '.date("d/m/Y").'</td>
                            </tr>
                        </table>
                    </div>

                </div>';
            }
            else{
                $html.='<div class="page">
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                        ';
                        
                        
                        
                         $html.='
                        
                         <img style="float:left; max-width: 40px; max-height: 40px; margin-right: 5px;" src="'.(isset($empresayLiquidacion->logoEmpresa) ? "data:image/png;base64,'.$base64.'" : '').'" class="logoEmpresa" />
                        <b>'.$empresayLiquidacion->razonSocial.'</b>
                        <br>
                        <b>'.$empresayLiquidacion->documento.'-'.$empresayLiquidacion->digitoVerificacion.'</b>
                        <center>
                            <h2 style="margin:0; margin-bottom: 10px;">Comprobante pago nómina</h2>
                        </center>
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <th>
                                    Nómina
                                </th>
                                <td>
                                    '.$empresayLiquidacion->nom_nombre.'
                                </td>
                                <th>
                                    Periodo liquidación
                                </th>
                                <td>
                                    '.date("Y",strtotime($empresayLiquidacion->fechaInicio))."/".$arrMeses[date("m",strtotime($empresayLiquidacion->fechaInicio)) - 1].'/'.date("d",strtotime($empresayLiquidacion->fechaInicio)).' 
                                    a
                                    '.date("Y",strtotime($empresayLiquidacion->fechaFin))."/".$arrMeses[date("m",strtotime($empresayLiquidacion->fechaFin)) - 1].'/'.date("d",strtotime($empresayLiquidacion->fechaFin)).' 
                                </td>
                            </tr>
                            <tr>
                                <th>Empleado</th>
                                <td>'.$empleado->primerApellido." ".$empleado->segundoApellido." ".$empleado->primerNombre." ".$empleado->segundoNombre.'</td>
                                <th>Salario</th>
                                <td>$ '.number_format($conceptoSalario->valor,0, ",", ".").'</td>
                            </tr>
                            <tr>
                                <th>Identificación</th>
                                <td>'.$empleado->tipoidentificacion.' '.$empleado->numeroIdentificacion.'</td>
                                <th>Cargo</th>
                                <td>'.$empleado->nombreCargo.'</td>
                            </tr>
                            <tr>
                                <th>Entidad Bancaria</th>
                                <td>'.(isset($entidadBancaria->razonSocial) ? $entidadBancaria->razonSocial : "").'</td>
                                <th>Cuenta</th>
                                <td>'.(isset($entidadBancaria->numeroCuenta) ? $entidadBancaria->numeroCuenta : "").'</td>
                            </tr>
                            <tr>
                                <th>EPS</th>
                                <td>'.$salud->razonSocial.'</td>
                                <th>Fondo Pensiones</th>
                                <td>'.(isset($pension->razonSocial) ? $pension->razonSocial : "").'</td>
                            </tr>
                        </table>
                    </div><br>
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <th style="background: #d89290; text-align: center;" colspan="2">Devengado</th>
                                <th style="background: #d89290; text-align: center;">Deducciones</th>                        
                            </tr>
                            <tr>
                                <th style="background: #CCC; text-align: center;">Conceptos Liquidados</th>
                                <th style="background: #CCC; text-align: center;">Cantidad</th>
                                <th style="background: #CCC; text-align: center;">Unidad</th>
                                <th style="background: #CCC; text-align: center;">Pagos</th>
                                <th style="background: #CCC; text-align: center;">Beneficios</th>
                                <th style="background: #CCC; text-align: center;">Descuentos</th>
                            </tr>';
                            $totalDesc = 0;
                            $totalPag = 0;
                
                            foreach($idItemBoucherPago as $itemBoucherPago){
                                $html.='<tr style="border-bottom: 1px solid #B0B0B0;">
                                    <td style="border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->nombre.'</td>
                                    <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->cantidad.'</td>
                                    <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">'.$itemBoucherPago->tipoUnidad.'</td>';
                                    
                                    if($itemBoucherPago->valor > 0){
                                        $html.='<td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$'.number_format($itemBoucherPago->valor,0, ",", ".").'</td>
                                            <td style="border-bottom: 1px solid #B0B0B0;"></td>
                                            <td style="border-bottom: 1px solid #B0B0B0;"></td>';
                                        $totalPag = $totalPag + $itemBoucherPago->valor;
                                    }
                                    else{
                                        $html.='<td style="border-bottom: 1px solid #B0B0B0;"></td>
                                            <td style="border-bottom: 1px solid #B0B0B0;"></td>
                                            <td style="text-align: right;border-bottom: 1px solid #B0B0B0;">$'.number_format($itemBoucherPago->valor*-1,0, ",", ".").'</td>';
                                        $totalDesc = $totalDesc + $itemBoucherPago->valor;
                                    }

                                $html.='</tr>';
                            }
                            $html.='<tr>
                                        
                                        <th colspan="3" style="text-align: right;">Totales</th>
                                        <td style="text-align: right; border: 1px solid #B0B0B0;">$'.number_format($totalPag,0, ",", ".").'</td>
                                        <td style="text-align: right; border: 1px solid #B0B0B0;"></td>
                                        <td style="text-align: right;border: 1px solid #B0B0B0;" >$'.number_format($totalDesc*-1,0, ",", ".").'</td>
                                    </tr>
                            ';
                            $totalGen = $totalPag + $totalDesc;
                            if($totalGen<0){
                                $totalGen=0;
                            }
                            
                            $html.='<tr>
                                        
                                        <th colspan="3" style="text-align: right;" >Neto a pagar en cuenta nómina</th>
                                        <td style="text-align: right;border: 1px solid #B0B0B0;" colspan="2">$'.number_format($totalGen,0, ",", ".").'</td>
                                        
                                    </tr>
                            ';
                            
                        $html.='</table>

                    </div>
                    <br>
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <th style="background: #CCC; text-align: center;" colspan="2">Bases para cálculo de seguridad social</th>
                            </tr>
                            <tr>
                                <td>Ingreso Base Cotización Salud</td><td style="text-align: right;">$'.number_format($empresayLiquidacion->ibc_eps,0, ",", ".").'</td>
                            </tr>
                            <tr>
                                <td>Ingreso Base Cotización Pension</td><td style="text-align: right;">$'.number_format($empresayLiquidacion->ibc_afp,0, ",", ".").'</td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <th style="background: #CCC; text-align: center;" colspan="2">Mensaje Empresarial</th>
                            </tr>
                            <tr>
                                <td style="text-align: justify;">Si tienes preguntas acerca de la atención de pacientes con infección respiratoria, comunícate con el Ministerio de Salud y Protección Social al número telefónico: Bogotá (1) 330 50 41 |
                                Línea gratuita nacional 01 8000 955 590 | Fuera del país +571 330 50 41. Informarnos correctamente es la primera línea de defensa ante el Coronavirus (COVID-19).</td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div style="position: absolute; bottom: 20px;">
                        <table style="width: 100%; text-align: left;">
                            <tr>
                                <td>COLABORADOR</td>
                                <td></td>
                                <td>LA EMPRESA</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Cédula o NIT</td>
                                <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                                <td>NIT</td>
                                <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td>Fecha de elaboración:&nbsp;&nbsp;&nbsp; '.date("d/m/Y").'</td>
                            </tr>
                        </table>

                    </div>
                </div>
                ';
            }
            if(sizeof($novedadesVacacionActual) > 0){
                
                $novedadesRetiro = DB::table("novedad","n")
                ->select("r.fecha")
                ->join("retiro AS r", "r.idRetiro","=","n.fkRetiro")
                    ->where("n.fkEmpleado", "=", $empleado->idempleado)
                ->whereIn("n.fkEstado",["7", "8"])
                ->whereNotNull("n.fkRetiro")
                ->whereBetween("n.fechaRegistro",[$empresayLiquidacion->fechaInicio, $empresayLiquidacion->fechaFin])->first();
                $fechaFinalVaca = $empresayLiquidacion->fechaFin;
                $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$empresayLiquidacion->fechaFin) + 1 ;
                if(isset($novedadesRetiro)){
                    if(strtotime($empresayLiquidacion->fechaFin) > strtotime($novedadesRetiro->fecha)){
                        $periodoPagoVac = $this->days_360($empleado->fechaIngreso,$novedadesRetiro->fecha) + 1 ;
                        $fechaFinalVaca = $$novedadesRetiro->fecha;
                    }
                }

                $diasVac = $periodoPagoVac * 15 / 360;

                $novedadesVacacion = DB::table("novedad","n")
                ->select("v.*", "c.nombre","c.idconcepto", "ibpn.valor")
                ->join("concepto as c","c.idconcepto","=","n.fkConcepto")
                ->join("vacaciones as v","v.idVacaciones","=","n.fkVacaciones")
                ->join("item_boucher_pago_novedad as ibpn","ibpn.fkNovedad","=","n.idNovedad")
                ->join("item_boucher_pago as ibp","ibp.idItemBoucherPago","=","ibpn.fkItemBoucher")
                ->where("n.fkEmpleado","=",$empleado->idempleado)
                ->where("ibp.fkBoucherPago","<>",$idBoucherPago)
                ->whereIn("n.fkEstado",["7"]) // Pagada o sin pagar-> no que este eliminada
                ->whereNotNull("n.fkVacaciones")
                ->get();
                //$diasVac = $totalPeriodoPagoAnioActual * 15 / 360;
                foreach($novedadesVacacion as $novedadVacacion){
                    $diasVac = $diasVac - $novedadVacacion->diasCompensar;
                }
                if(isset($diasVac) && $diasVac < 0){
                    $diasVac = 0;
                }

              
                
                $html.='<div class="page_break"></div>
                    <div class="page">
                        <div style="border: 2px solid #000; padding: 10px 20px;">
                            <img style="float:left; max-width: 40px; max-height: 40px; margin-right: 5px;" src="'.(isset($empresayLiquidacion->logoEmpresa) ? "data:image/png;base64,'.$base64.'" : '').'" class="logoEmpresa" />
                            <b>'.$empresayLiquidacion->razonSocial.'</b>
                            <br>
                            <b>'.$empresayLiquidacion->documento.'-'.$empresayLiquidacion->digitoVerificacion.'</b>
                            <center>
                                <h2 style="margin:0; margin-bottom: 10px;">Comprobante de Pago de Vacaciones</h2>
                            </center>
                            <table style="width: 100%; text-align: left;">
                                <tr>
                                    <th>Empleado</th>
                                    <td>'.$empleado->primerApellido." ".$empleado->segundoApellido." ".$empleado->primerNombre." ".$empleado->segundoNombre.'</td>
                                    <th>Fecha ingreso</th>
                                    <td>'.date("Y",strtotime($empleado->fechaIngreso))."/".$arrMeses[date("m",strtotime($empleado->fechaIngreso)) - 1].'/'.date("d",strtotime($empleado->fechaIngreso)).'</td>
                                </tr>
                                <tr>
                                    <th>Identificación</th>
                                    <td>'.$empleado->tipoidentificacion.' '.$empleado->numeroIdentificacion.'</td>
                                    <th>Cargo</th>
                                    <td>'.$empleado->nombreCargo.'</td>
                                </tr>
                                <tr>
                                    <th>Días Pendientes Consolidado</th>
                                    <td>'.round($diasVac,2).'</td>
                                    <th>Fecha Corte Consolidado:</th>
                                    <td>'.date("Y",strtotime($fechaFinalVaca))."/".$arrMeses[date("m",strtotime($fechaFinalVaca)) - 1].'/'.date("d",strtotime($fechaFinalVaca)).'</td>
                                </tr>
                                
                            </table>
                    </div>
                    <br>
                    <div style="border: 2px solid #000; padding: 10px 20px;">
                    <table style="width: 100%; text-align: left;">
                        <tr>
                            <th style="background: #d89290; text-align: center;" colspan="11">Liquidación de Vacaciones</th>                         
                        </tr>
                        <tr>
                            <th style="background: #CCC; text-align: center;" rowspan="2">Tipo Movimiento</th>
                            <th style="background: #CCC; text-align: center;" colspan="2">Periodo Causación</th>
                            <th style="background: #CCC; text-align: center;" colspan="4">Periodo Vacaciones</th>
                            <th style="background: #CCC; text-align: center;" colspan="2">Días Pagados</th>
                            <th style="background: #CCC; text-align: center;" rowspan="2">Promedio<br>Diario</th>
                            <th style="background: #CCC; text-align: center;" rowspan="2">Valor<br>Liquidado</th>
                        </tr>
                        <tr>
                            <th style="background: #CCC; text-align: center;">Fecha Inicio</th>
                            <th style="background: #CCC; text-align: center;">Fecha Fin</th>
                            <th style="background: #CCC; text-align: center;">Fecha Inicio</th>
                            <th style="background: #CCC; text-align: center;">Fecha Fin</th>
                            <th style="background: #CCC; text-align: center;">Días</th>
                            <th style="background: #CCC; text-align: center;">Regreso</th>
                            <th style="background: #CCC; text-align: center;">Tiempo</th>
                            <th style="background: #CCC; text-align: center;">Dinero</th>
                        </tr>
                        
                ';
                $totalVac = 0;
                foreach($novedadesVacacionActual as $novedadVacacion){
                    $tipoMov = str_replace("VACACIONES", "", $novedadVacacion->nombre);
                    $html.='
                        <tr>
                            <td style="border-bottom: 1px solid #B0B0B0;">'.$tipoMov.'</td>
                            <td style="border-bottom: 1px solid #B0B0B0;" width="50">'.$empleado->fechaIngreso.'</td>
                            <td style="border-bottom: 1px solid #B0B0B0;" width="50">'.$novedadVacacion->fechaInicio.'</td>';
                        if($novedadVacacion->idconcepto == 29){
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;" width="50">'.$novedadVacacion->fechaInicio.'</td>';
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;" width="50">'.$novedadVacacion->fechaFin.'</td>';
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;" width="50">'.$novedadVacacion->diasCompensar.'</td>';
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;" width="50">'.date("Y-m-d",strtotime($novedadVacacion->fechaFin."+1 day")).'</td>';
                        }
                        else{
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;"></td>';
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;"></td>';
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;">'.$novedadVacacion->diasCompensar.'</td>';
                            $html.='<td style="border-bottom: 1px solid #B0B0B0;"></td>';
                        }
                        
                        $html.='<td style="border-bottom: 1px solid #B0B0B0;">'.$novedadVacacion->diasCompensar.'</td>';
                        $html.='<td style="border-bottom: 1px solid #B0B0B0;">$'.number_format($novedadVacacion->valor,0, ",", ".").'</td>';
                        $html.='<td style="border-bottom: 1px solid #B0B0B0;">$'.number_format($novedadVacacion->valor/$novedadVacacion->diasCompensar,0, ",", ".").'</td>';
                        $html.='<td style="border-bottom: 1px solid #B0B0B0;">$'.number_format($novedadVacacion->valor,0, ",", ".").'</td>';

                        $html.='</tr>
                    ';
                    $totalVac = $totalVac + $novedadVacacion->valor;
                    if($totalVac<0){
                        $totalVac=0;
                    }
                }    
                $html.='
                    <tr>
                        <th style="text-align: right;" colspan="9">TOTAL LIQUIDADO VACACIONES</th>
                        <td style="text-align: right; border: 1px solid #B0B0B0;" colspan="2">$'.number_format($totalVac,0, ",", ".").'</td>
                    </tr>            
                    </table>
                </div>
                <br>
                    <center><h4>Observaciones</h4></center>
                <br>
                <div style="border: 2px solid #000; padding: 10px 20px; min-height: 50px;">
                <br><br><br>
                </div>
                <div style="position: absolute; bottom: 20px;">
                    <table style="width: 100%; text-align: left;">
                        <tr>
                            <td>COLABORADOR</td>
                            <td></td>
                            <td>LA EMPRESA</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Cédula o NIT</td>
                            <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                            <td>NIT</td>
                            <td style="width: 2in; border-bottom: 1px solid #000;"> </td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td>Fecha de elaboración:&nbsp;&nbsp;&nbsp; '.date("d/m/Y").'</td>
                        </tr>
                    </table>
                    <table style="width: 100%;">
                        <tr>
                            <th style="border: 1px solid #000;">ELABORÓ:</th>
                            <th style="border: 1px solid #000;">REVISÓ:</th>
                            <th style="border: 1px solid #000;">APROBÓ:</th>
                        </tr>
                    </table>
                </div>
                ';
            }            

            
            $html.='
            </body>
        </html>
        ';
        
        $dompdf->loadHtml($html ,'UTF-8');

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('Letter', 'portrait');
        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("Comprobante de Pago ".$idBoucherPago.".pdf", array('compress' => 1, 'Attachment' => 0));
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
        return response()->json([
            "success" => true,
            "diasVac" => floatval($diasVac),
            "fechaIngreso" => $empleado->fechaIngreso,
            "fechaCorteCalculo" => $fechaFin
        ]);


    }
    public function seleccionarDocumentoSeguridad(){
        $empresas = DB::table("empresa", "e")
        ->get();

        return view('/reportes.seleccionarDocumentoSeguridad',[
            'empresas' => $empresas            
        ]);
    }
    public function documentoSSTxt(Request $req){
        //Anexo-tecnico-2-2016-pila.pdf Seccion 2.1.1.1 ---- Pag 20 pdf
        $idEmpresa = $req->empresa;
        $fechaDocumento = $req->fechaDocumento;
        $fechaInicioMesActual = date("Y-m-01", strtotime($fechaDocumento));
        $fechaFinMesActual = date("Y-m-t", strtotime($fechaDocumento));

        $fechaInicioMes = date("Y-m-01", strtotime($fechaDocumento));

        $empresa = DB::table('empresa',"e")
        ->select("e.razonSocial","e.documento","ti.siglaPila", "te.codigoTercero as codigoArl", "e.digitoVerificacion")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion","=","e.fkTipoIdentificacion")
        ->join("tercero as te","te.idTercero","=","e.fkTercero_ARL")
        ->where("idEmpresa","=",$idEmpresa)
        ->first();


        $empleados = DB::table('empleado', 'e')
        ->selectRaw("count(*) as cuenta")
        ->join("boucherpago as bp","bp.fkEmpleado", "=", "e.idempleado")
        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina", "=","bp.fkLiquidacion")
        ->join("nomina as n","n.idNomina", "=","ln.fkNomina")
        ->where("n.fkEmpresa","=",$idEmpresa)
        ->whereIn("ln.fkTipoLiquidacion",["1","2"]) //1 - Normal, 2- Retiro
        ->where("ln.fkEstado","=","5")
        ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")->first();
        


        $arrayMuestra = array();
        $arrayMuestra[0] = array();
        $arrayMuestra[0][0] = $this->plantillaTxt("01",2,"","right");
        $arrayMuestra[0][1] = $this->plantillaTxt("1",1,"","right");
        $arrayMuestra[0][2] = $this->plantillaTxt("1",4,"0","right");
        $arrayMuestra[0][3] = $this->plantillaTxt($empresa->razonSocial,200," ","left");
        $arrayMuestra[0][4] = $this->plantillaTxt($empresa->siglaPila,2,"","left");
        $arrayMuestra[0][5] = $this->plantillaTxt($empresa->documento,16," ","left");
        $arrayMuestra[0][6] = $this->plantillaTxt($empresa->digitoVerificacion,1," ","left");
        $arrayMuestra[0][7] = $this->plantillaTxt("E",1," ","left");//Planilla empleados
        $arrayMuestra[0][8] = $this->plantillaTxt("",10," ","left");//Número de la planilla asociada
        $arrayMuestra[0][9] = $this->plantillaTxt("",10," ","left");//Fecha de pago Planilla asociada
        $arrayMuestra[0][10] = $this->plantillaTxt("U",1,"","left");//Unico
        $arrayMuestra[0][11] = $this->plantillaTxt("",10," ","left");//Código de la sucursal del aportante
        $arrayMuestra[0][12] = $this->plantillaTxt("",40," ","left");//Nombre de la sucursal
        $arrayMuestra[0][13] = $this->plantillaTxt($empresa->codigoArl,6," ","left");
        $arrayMuestra[0][14] = $this->plantillaTxt(date("Y-m",strtotime($fechaDocumento)),7," ","left");
        $arrayMuestra[0][15] = $this->plantillaTxt(date("Y-m",strtotime($fechaInicioMes." +1 month")),7," ","left");
        $arrayMuestra[0][16] = $this->plantillaTxt("",10,"0","left");//Número de radicación
        $arrayMuestra[0][17] = $this->plantillaTxt("",10," ","left");//Fecha de pago
        $arrayMuestra[0][18] = $this->plantillaTxt($empleados->cuenta,5,"0","right");//Número total de cotizantes
        $arrayMuestra[0][19] = $this->plantillaTxt("",12,"0","right");//Valor total nomina
        $arrayMuestra[0][20] = $this->plantillaTxt("1",2,"0","right");//Tipo de aportante
        $arrayMuestra[0][21] = $this->plantillaTxt("0",2,"0","right");//Código del operador de información
        
        $empleadosGen = DB::table('empleado', 'e')
        ->select("e.*", "dp.*", "ti.siglaPila")
        ->join("datospersonales AS dp", "e.fkDatosPersonales", "=" , "dp.idDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion","=","dp.fkTipoIdentificacion")
        ->join("boucherpago as bp","bp.fkEmpleado", "=", "e.idempleado")
        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina", "=","bp.fkLiquidacion")
        ->join("nomina as n","n.idNomina", "=","ln.fkNomina")
        ->where("n.fkEmpresa","=",$idEmpresa)
        ->whereIn("ln.fkTipoLiquidacion",["1","2","3","4","5","6"]) //1 - Normal, 2- Retiro
        ->where("ln.fkEstado","=","5")
        ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
        ->distinct()->get();
        $contador=1;
        $totalNomina = 0;
        $numeroEmpleados = 0;
        foreach($empleadosGen as $empleado){
            $arrayFila = array();
            $arrayNuevoRegistro = array();
            $numeroEmpleados ++;
            $fechaFin = $fechaFinMesActual;
            $arrayFila[0] = $this->plantillaTxt("02",2,"","right");
            $arrayFila[1] = $this->plantillaTxt($contador,5,"0","right");
            $arrayFila[2] = $this->plantillaTxt($empleado->siglaPila,2," ","right");
            $arrayFila[3] = $this->plantillaTxt($empleado->numeroIdentificacion,16," ","left");
            $arrayFila[4] = $this->plantillaTxt("1",2,"0","right");//Tipo cotizante
            $arrayFila[5] = $this->plantillaTxt($empleado->esPensionado,2,"0","right");//Subtipo de cotizante

            //Extranjero no obligado a cotizar a pensiones
            if($empleado->fkTipoIdentificacion == "4"){
                $arrayFila[6] = $this->plantillaTxt("X",1,"","left");
            }
            else{
                $arrayFila[6] = $this->plantillaTxt(" ",1,"","left");
            }

            //Colombiano en el exterior
            if(substr($empleado->fkUbicacionResidencia,0,2) != "57" && ($empleado->fkTipoIdentificacion == "1" || $empleado->fkTipoIdentificacion == "6")){
                $arrayFila[7] = $this->plantillaTxt("X",1,"","left");
            }
            else{
                $arrayFila[7] = $this->plantillaTxt(" ",1," ","left");
            }

            //Código del departamento de la ubicación laboral
            if(substr($empleado->fkUbicacionLabora,0,2) == "57"){
                $arrayFila[8] = $this->plantillaTxt(substr("0".substr($empleado->fkUbicacionLabora,2,2),-2),2,"","right");
            }
            else{
                $arrayFila[8] = $this->plantillaTxt("",2," ","left");
            }


            //Código del municipio de ubicación laboral
            if(substr($empleado->fkUbicacionLabora,0,2) == "57"){
                $arrayFila[9] = $this->plantillaTxt(substr("00".substr($empleado->fkUbicacionLabora,4),-3),3,"","right");
            }
            else{
                $arrayFila[9] = $this->plantillaTxt("",3," ","left");
            }


            $arrayFila[10] = $this->plantillaTxt($empleado->primerApellido,20," ","left");
            $arrayFila[11] = $this->plantillaTxt($empleado->segundoApellido,30," ","left");
            $arrayFila[12] = $this->plantillaTxt($empleado->primerNombre,20," ","left");
            $arrayFila[13] = $this->plantillaTxt($empleado->segundoNombre,30," ","left");
        
            $arraySinNada = $arrayFila;
            $periodoTrabajado = 30;
            //Salario
            $conceptoFijoSalario = DB::table("conceptofijo", "cf")
            ->whereIn("cf.fkConcepto",["1","2"])
            ->where("cf.fkEmpleado", "=", $empleado->idempleado)
            ->first();

            $ultimoBoucher = DB::table("boucherpago", "bp")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
            ->orderBy("bp.idBoucherPago","desc")
            ->first();

            
            $esRetiroNegativo = 0;
            if($ultimoBoucher->ibc_afp < 0){
                $esRetiroNegativo = 1;
                $ultimoBoucher->ibc_afp = ($conceptoFijoSalario->valor / 30);
                $ultimoBoucher->ibc_eps = ($conceptoFijoSalario->valor / 30);
                $ultimoBoucher->ibc_arl = ($conceptoFijoSalario->valor / 30);
                $itemVacacionesRetiro = DB::table("item_boucher_pago","ibp")
                ->selectRaw("Sum(ibp.valor) as vacaciones")
                ->whereIn("ibp.fkConcepto",["30","29"])
                ->where("ibp.fkBoucherPago","=",$ultimoBoucher->idBoucherPago)
                ->first();
                $ultimoBoucher->ibc_ccf = ($conceptoFijoSalario->valor / 30);
                if($ultimoBoucher->ibc_otros > 0){
                    $ultimoBoucher->ibc_otros = ($conceptoFijoSalario->valor / 30);
                }
                
                if(isset($itemVacacionesRetiro->vacaciones)){
                    $ultimoBoucher->ibc_ccf = $ultimoBoucher->ibc_ccf + $itemVacacionesRetiro->vacaciones;
                    if($ultimoBoucher->ibc_otros > 0){
                        $ultimoBoucher->ibc_otros = $ultimoBoucher->ibc_otros + $itemVacacionesRetiro->vacaciones;
                    }
                }
                
                $periodoTrabajado = 1;
            }

            $ibcAFP = $ultimoBoucher->ibc_afp;
            $ibcEPS = $ultimoBoucher->ibc_eps;
            $ibcARL = $ultimoBoucher->ibc_arl;
            $ibcCCF = $ultimoBoucher->ibc_ccf;
            $ibcOtros = $ultimoBoucher->ibc_otros;  
            
            $arrayFila[41] = $this->plantillaTxt(round($ibcAFP),9,"0","right");
            
            //ING
            if(strtotime($fechaInicioMesActual) < strtotime($empleado->fechaIngreso)){
                $arrayFila[14] = $this->plantillaTxt("X",1,"","left");
                
                $arrayFila[79] = $this->plantillaTxt($empleado->fechaIngreso,10,"","left");
                $periodoTrabajado = $periodoTrabajado - intval(substr($empleado->fechaIngreso,8,2)) + 1;
                
            }
            else{
                $arrayFila[14] = $this->plantillaTxt(" ",1,"","left");
                $arrayFila[79] = $this->plantillaTxt("",10," ","left");
            }

            
            //RET
            $novedadesRetiro = DB::table("novedad","n")
                ->select("r.fecha")
                ->join("retiro as r", "r.idRetiro", "=","n.fkRetiro")
                ->where("n.fkEmpleado","=", $empleado->idempleado)
                ->whereIn("n.fkEstado",["8"]) // Pagada-> no que este eliminada
                ->whereNotNull("n.fkRetiro")
                ->whereBetween("n.fechaRegistro",[$fechaInicioMesActual, $fechaFinMesActual])
                ->get();
            
            if(sizeof($novedadesRetiro)>0){
                $arrayFila[15] = $this->plantillaTxt("X",1," ","left");
                $arrayFila[80] = $this->plantillaTxt($novedadesRetiro[0]->fecha,10,"","left");
                $diasRetiro = 30 - intval(substr($novedadesRetiro[0]->fecha,8,2));
                if($diasRetiro>0){
                    if($esRetiroNegativo == 0){
                        $periodoTrabajado = $periodoTrabajado - $diasRetiro;
                    }
                    
                } 
                if($esRetiroNegativo == 1){
                    $arrayFila[80] = $this->plantillaTxt($fechaInicioMesActual,10,"","left");//
                }               
            }
            else{
                $arrayFila[15] = $this->plantillaTxt("",1," ","left");
                $arrayFila[80] = $this->plantillaTxt("",10," ","left");
            }

            
            
            
            //TDE
            $fechaInicioParaMesAntes = date("Y-m-01", strtotime($fechaInicioMesActual."  -1 month"));
            $fechaFinParaMesAntes = date("Y-m-t", strtotime($fechaInicioParaMesAntes));
            
            $cambioAfiliacionEps = DB::table("cambioafiliacion","ca")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "3") //3-Salud
                ->whereBetween("ca.fechaCambio", [$fechaInicioParaMesAntes, $fechaFinParaMesAntes])
                ->get();
            if(sizeof($cambioAfiliacionEps)>0){
                $arrayPlace = $arraySinNada;
                $arrayPlace[16] = $this->plantillaTxt("X",1," ","left");
                array_push($arrayNuevoRegistro, $arrayPlace);
            }
            $arrayFila[16] = $this->plantillaTxt(" ",1," ","left");
            

            //TAE
            $cambioAfiliacionEps2 = DB::table("cambioafiliacion","ca")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "3") //3-Salud
                ->whereBetween("ca.fechaCambio", [$fechaInicioMesActual, $fechaFinMesActual])
                ->get();
            if(sizeof($cambioAfiliacionEps2)>0){
                $arrayPlace = $arraySinNada;
                $arrayPlace[17] = $this->plantillaTxt("X",1," ","left");
                $arrayPlace[33] = $this->plantillaTxt($cambioAfiliacionEps2[0]->codigoTercero,6," ","left");
                array_push($arrayNuevoRegistro, $arrayPlace);
            }
            $arrayFila[17] = $this->plantillaTxt(" ",1," ","left");
            $arrayFila[33] = $this->plantillaTxt(" ",6," ","left");
            //TDP
            $cambioAfiliacionPension = DB::table("cambioafiliacion","ca")
                ->join("tercero as t", "t.idTercero", "=", "ca.fkTerceroNuevo")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "4") //4-Pension
                ->whereBetween("ca.fechaCambio", [$fechaInicioParaMesAntes, $fechaFinParaMesAntes])
                ->get();
            if(sizeof($cambioAfiliacionPension)>0){
                $arrayPlace = $arraySinNada;
                $arrayPlace[18] = $this->plantillaTxt("X",1," ","left");
                
                array_push($arrayNuevoRegistro, $arrayPlace);
            }
            $arrayFila[18] = $this->plantillaTxt(" ",1," ","left");

            //TAP
            $cambioAfiliacionPension2 = DB::table("cambioafiliacion","ca")
                ->join("tercero as t", "t.idTercero", "=", "ca.fkTerceroNuevo")
                ->where("ca.fkEmpleado", "=", $empleado->idempleado)
                ->where("ca.fkTipoAfiliacionNueva", "=", "4") //4-Pension
                ->whereBetween("ca.fechaCambio", [$fechaInicioMesActual, $fechaFinMesActual])
                ->get();

            if(sizeof($cambioAfiliacionPension2)>0){
                $arrayPlace = $arraySinNada;
                $arrayPlace[19] = $this->plantillaTxt("X",1," ","left");


                $arrayPlace[31] = $this->plantillaTxt($cambioAfiliacionPension2[0]->codigoTercero,6," ","left");
                array_push($arrayNuevoRegistro, $arrayPlace);
            }
            $arrayFila[19] = $this->plantillaTxt(" ",1," ","left");
            $arrayFila[31] = $this->plantillaTxt(" ",6," ","left");
            //VSP
            $cambioSalario = DB::table("cambiosalario","cs")
                ->where("cs.fkEmpleado", "=", $empleado->idempleado)
                ->whereBetween("cs.fechaCambio", [$fechaInicioMesActual, $fechaFinMesActual])
                ->get();

            if(sizeof($cambioSalario)>0){
                $arrayFila[20] = $this->plantillaTxt("X",1," ","left");
                $arrayFila[81] = $this->plantillaTxt($cambioSalario[0]->fechaCambio,10,"","left");
            }
            else{
                $arrayFila[20] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[81] = $this->plantillaTxt("",10," ","left");
            }
            
            //Correcciones
            $arrayFila[21] = $this->plantillaTxt(" ",1," ","left");

            //VST
            $itemsBoucherPago = DB::table("item_boucher_pago", "ibp")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
            ->where("gcc.fkGrupoConcepto","=","10") //10 - CONCEPTOS QUE GENERAN VST	
            ->get();

        
            
            if(sizeof($itemsBoucherPago)>0){
                $arrayFila[22] = $this->plantillaTxt("X",1," ","left");
            }
            else{
                $arrayFila[22] = $this->plantillaTxt(" ",1," ","left");
            }
            
            //SLN
            $novedadesSancion = DB::table("novedad","n")
            ->join("ausencia AS a","a.idAusencia", "=", "n.fkAusencia")
            ->where("a.cantidadDias",">=", "1")
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["7", "8"]) // Pagada o sin pagar-> no que este eliminada
            ->whereNotNull("n.fkAusencia")
            ->whereBetween("n.fechaRegistro",[$fechaInicioMesActual, $fechaFinMesActual])
            ->get();
            $periodoTrabajadoSinNov = $periodoTrabajado;
            
            foreach($novedadesSancion as $novedadSancion){
                $arrayPlace = $arraySinNada;
                $arrayPlace[23] = $this->plantillaTxt("X",1," ","left");

                $novedadSancion->cantidadDias = intval( $novedadSancion->cantidadDias);

                $periodoTrabajado = $periodoTrabajado - $novedadSancion->cantidadDias;


                
                if($empleado->esPensionado == 0){
                    $arrayPlace[35] = $this->plantillaTxt($novedadSancion->cantidadDias,2,"0","right");
                }
                else{
                    $arrayPlace[35] = $this->plantillaTxt("",2,"0","right");
                }
                $arrayPlace[36] = $this->plantillaTxt($novedadSancion->cantidadDias,2,"0","right");
                $arrayPlace[37] = $this->plantillaTxt($novedadSancion->cantidadDias,2,"0","right"); 
                $arrayPlace[38] = $this->plantillaTxt($novedadSancion->cantidadDias,2,"0","right"); 
                

                $fechaInicioSLN = date("Y-m-d",strtotime($novedadSancion->fechaInicio));
                $fechaFinSLN =  date("Y-m-d",strtotime($novedadSancion->fechaFin));
                $arrayPlace[82] = $this->plantillaTxt($fechaInicioSLN,10," ","left");
                $arrayPlace[83] = $this->plantillaTxt($fechaFinSLN,10," ","left");

                //Tarifa en 0 para ausentismos
                $arrayPlace[60] =  $this->plantillaTxt("0.0",9,"0","left");
                $arrayPlace[62] = $this->plantillaTxt("0",9,"0","right");
    
                array_push($arrayNuevoRegistro, $arrayPlace);   

                
            }
            $arrayFila[23] = $this->plantillaTxt(" ",1," ","left");
            $arrayFila[82] = $this->plantillaTxt("",10," ","left");
            $arrayFila[83] = $this->plantillaTxt("",10," ","left");
            
            
            //IGE
            $novedadesIncapacidadNoLab = DB::table("novedad","n")
            ->join("incapacidad as i","i.idIncapacidad","=", "n.fkIncapacidad")
            ->where("i.fkTipoAfilicacion","=","3") //3- Salud
            ->whereNotIn("i.tipoIncapacidad",["Maternidad", "Paternidad"])
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["8"]) // Pagada -> no que este eliminada
            ->whereNotNull("n.fkIncapacidad")
            ->whereBetween("n.fechaRegistro",[$fechaInicioMesActual, $fechaFinMesActual])
            ->get();

            foreach($novedadesIncapacidadNoLab as $novedadIncapacidadNoLab){
                $arrayPlace = $arraySinNada;
                $arrayPlace[24] = $this->plantillaTxt("X",1," ","left");

                $novedadIncapacidadNoLab->numDias = intval($novedadIncapacidadNoLab->numDias);

                $periodoTrabajado = $periodoTrabajado - $novedadIncapacidadNoLab->numDias;
                if($empleado->esPensionado == 0){
                    $arrayPlace[35] = $this->plantillaTxt($novedadIncapacidadNoLab->numDias,2,"0","right");
                }
                else{
                    $arrayPlace[35] = $this->plantillaTxt("",2,"0","right");
                }
                $arrayPlace[36] = $this->plantillaTxt($novedadIncapacidadNoLab->numDias,2,"0","right");
                $arrayPlace[37] = $this->plantillaTxt($novedadIncapacidadNoLab->numDias,2,"0","right"); 
                $arrayPlace[38] = $this->plantillaTxt($novedadIncapacidadNoLab->numDias,2,"0","right"); 
                
                $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                ->where("ibpn.fkNovedad", "=",$novedadIncapacidadNoLab->idNovedad)
                ->first();
               
                
                $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                $arrayPlace[41] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[42] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[43] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[44] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[94] = $this->plantillaTxt(round($valorNovedad),9,"0","right");

                $arrayFila[62] = $this->plantillaTxt(0,9,"0","right");


                $ibcAFP = $ibcAFP - $valorNovedad;
                $ibcEPS = $ibcEPS - $valorNovedad;
                $ibcARL = $ibcARL - $valorNovedad;
                $ibcCCF = $ibcCCF - $valorNovedad;
                $ibcOtros = $ibcOtros - $valorNovedad;



                $fechaInicioIGE = date("Y-m-d",strtotime($novedadIncapacidadNoLab->fechaInicial));
                $fechaFinIGE =  date("Y-m-d",strtotime($novedadIncapacidadNoLab->fechaFinal));
                $arrayPlace[84] = $this->plantillaTxt($fechaInicioIGE,10," ","left");
                $arrayPlace[85] = $this->plantillaTxt($fechaFinIGE,10," ","left");

                //Tarifa en 0 para ausentismos
                $arrayPlace[60] =  $this->plantillaTxt("0.0",9,"0","left");
                $arrayPlace[62] = $this->plantillaTxt("0",9,"0","right");

                array_push($arrayNuevoRegistro, $arrayPlace);   
            }
            $arrayFila[24] = $this->plantillaTxt(" ",1," ","left");
            $arrayFila[84] = $this->plantillaTxt("",10," ","left");
            $arrayFila[85] = $this->plantillaTxt("",10," ","left");

            //LMA
            $novedadesIncapacidadNoLaMat = DB::table("novedad","n")
            ->join("incapacidad as i","i.idIncapacidad","=", "n.fkIncapacidad")
            ->whereIn("i.tipoIncapacidad",["Maternidad", "Paternidad"])
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["8"]) // Pagada-> no que este eliminada
            ->whereNotNull("n.fkIncapacidad")
            ->whereBetween("n.fechaRegistro",[$fechaInicioMesActual, $fechaFinMesActual])
            ->get();

            foreach($novedadesIncapacidadNoLaMat as $novedadIncapacidadNoLaMat){
                $arrayPlace = $arraySinNada;
                $arrayPlace[25] = $this->plantillaTxt("X",1," ","left");

                $novedadIncapacidadNoLaMat->numDias = intval( $novedadIncapacidadNoLaMat->numDias);

                $periodoTrabajado = $periodoTrabajado - $novedadIncapacidadNoLaMat->numDias;
                if($empleado->esPensionado == 0){
                    $arrayPlace[35] = $this->plantillaTxt($novedadIncapacidadNoLaMat->numDias,2,"0","right");
                }
                else{
                    $arrayPlace[35] = $this->plantillaTxt("",2,"0","right");
                }
                $arrayPlace[36] = $this->plantillaTxt($novedadIncapacidadNoLaMat->numDias,2,"0","right");
                $arrayPlace[37] = $this->plantillaTxt($novedadIncapacidadNoLaMat->numDias,2,"0","right"); 
                $arrayPlace[38] = $this->plantillaTxt($novedadIncapacidadNoLaMat->numDias,2,"0","right"); 

                $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                ->where("ibpn.fkNovedad", "=",$novedadIncapacidadNoLaMat->idNovedad)
                ->first();
                
                
                $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                $arrayPlace[41] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[42] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[43] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[44] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[94] = $this->plantillaTxt(round($valorNovedad),9,"0","right");

                $arrayFila[62] = $this->plantillaTxt(0,9,"0","right");


                $ibcAFP = $ibcAFP - $valorNovedad;
                $ibcEPS = $ibcEPS - $valorNovedad;
                $ibcARL = $ibcARL - $valorNovedad;
                $ibcCCF = $ibcCCF - $valorNovedad;
                $ibcOtros = $ibcOtros - $valorNovedad;







                $fechaInicioLMA = date("Y-m-d",strtotime($novedadIncapacidadNoLaMat->fechaInicial));
                $fechaFinLMA =  date("Y-m-d",strtotime($novedadIncapacidadNoLaMat->fechaFinal));
                $arrayPlace[86] = $this->plantillaTxt($fechaInicioLMA,10," ","left");
                $arrayPlace[87] = $this->plantillaTxt($fechaFinLMA,10," ","left");

                //Tarifa en 0 para ausentismos
                $arrayPlace[60] =  $this->plantillaTxt("0.0",9,"0","left");
                $arrayPlace[62] = $this->plantillaTxt("0",9,"0","right");

                array_push($arrayNuevoRegistro, $arrayPlace);   
            }
            $arrayFila[25] = $this->plantillaTxt(" ",1," ","left");
            $arrayFila[86] = $this->plantillaTxt("",10," ","left");
            $arrayFila[87] = $this->plantillaTxt("",10," ","left");

            //VAC
            $sqlWhere = "( 
                ('".$fechaInicioMesActual."' BETWEEN v.fechaInicio AND v.fechaFin) OR
                ('".$fechaFinMesActual."' BETWEEN v.fechaInicio AND v.fechaFin) OR
                (v.fechaInicio BETWEEN '".$fechaInicioMesActual."' AND '".$fechaFinMesActual."') OR
                (v.fechaFin BETWEEN '".$fechaInicioMesActual."' AND '".$fechaFinMesActual."')
            )";

            $novedadesVac = DB::table("novedad","n")
            ->join("vacaciones as v","v.idVacaciones","=", "n.fkVacaciones")
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["8","16"]) // Pagada-> no que este eliminada o parcialmente paga (para las de pago parcial)
            ->whereNotNull("n.fkVacaciones")
            ->where("n.fkConcepto","=", "29")
            ->whereRaw($sqlWhere)
            ->get();

            foreach($novedadesVac as $novedadVac){
                $arrayPlace = $arraySinNada;
                $arrayPlace[26] = $this->plantillaTxt("X",1," ","left");
                
                $diasCompensar = 0;
                $diasPagoVac = 0;
                $fechaFin = $fechaFinMesActual;
                $diaI="";
                $diaF="";

                if(strtotime($novedadVac->fechaInicio)>=strtotime($fechaInicioMesActual)
                    &&  strtotime($novedadVac->fechaInicio)<=strtotime($fechaFin) 
                    &&  strtotime($novedadVac->fechaFin)>=strtotime($fechaFin))
                {
                    $diaI = $novedadVac->fechaInicio;
                    $diaF = $fechaFin;
                    $diasCompensar = $this->days_360($novedadVac->fechaInicio, $fechaFin) + 1;
                    if(substr($novedadVac->fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                        $diasCompensar--;
                    }
                    $diasPagoVac = $diasCompensar;
                    if(substr($fechaFin, 8, 2) == "31"){
                        $diasCompensar--;   
                    }
                    
                
                }
                else if(strtotime($novedadVac->fechaFin)>=strtotime($fechaInicioMesActual)  
                &&  strtotime($novedadVac->fechaFin)<=strtotime($fechaFin) 
                &&  strtotime($novedadVac->fechaInicio)<=strtotime($fechaInicioMesActual))
                {
                    $diaI = $fechaInicioMesActual;
                    $diaF = $novedadVac->fechaFin;

                    $diasCompensar = $this->days_360($fechaInicioMesActual, $novedadVac->fechaFin) + 1;
                    if(substr($fechaInicioMesActual, 8, 2) == "31" && substr($novedadVac->fechaFin, 8, 2) == "31"){
                        $diasCompensar--;   
                    }
                    $diasPagoVac = $diasCompensar;
                    if(substr($novedadVac->fechaFin, 8, 2) == "31"){
                        $diasCompensar--;   
                    }
                }
                else if(strtotime($novedadVac->fechaInicio)<=strtotime($fechaInicioMesActual)  
                &&  strtotime($novedadVac->fechaFin)>=strtotime($fechaFin)) 
                {
                    $diaI = $fechaInicioMesActual;
                    $diaF = $fechaFin;
                    $diasCompensar = $this->days_360($fechaInicioMesActual, $fechaFin) + 1;
                    if(substr($fechaInicioMesActual, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                        $diasCompensar--;   
                    }
                    $diasPagoVac = $diasCompensar;
                    if(substr($fechaFin, 8, 2) == "31"){
                        $diasCompensar--;   
                    }
                }
                else if(strtotime($fechaInicioMesActual)<=strtotime($novedadVac->fechaInicio)  
                &&  strtotime($fechaFin)>=strtotime($novedadVac->fechaFin)) 
                {
                    $diaI = $novedadVac->fechaInicio;
                    $diaF = $novedadVac->fechaFin;
                    $diasCompensar = $this->days_360($novedadVac->fechaInicio, $novedadVac->fechaFin) + 1;

                    if(substr($novedadVac->fechaInicio, 8, 2) == "31" && substr($novedadVac->fechaFin, 8, 2) == "31"){
                        $diasCompensar--;   
                    }
                    $diasPagoVac = $diasCompensar;
                    if(substr($novedadVac->fechaFin, 8, 2) == "31"){
                        $diasCompensar--;   
                    }
                    
                }
                
                $diasTotales = $novedadVac->diasCompensar;
                $novedadVac->diasCompensar = intval( $diasCompensar);

                $periodoTrabajado = $periodoTrabajado - $novedadVac->diasCompensar;
                if($empleado->esPensionado == 0){
                    $arrayPlace[35] = $this->plantillaTxt($novedadVac->diasCompensar,2,"0","right");
                }
                else{
                    $arrayPlace[35] = $this->plantillaTxt("",2,"0","right");
                }
                $arrayPlace[36] = $this->plantillaTxt($novedadVac->diasCompensar,2,"0","right");
                $arrayPlace[37] = $this->plantillaTxt($novedadVac->diasCompensar,2,"0","right"); 
                $arrayPlace[38] = $this->plantillaTxt($novedadVac->diasCompensar,2,"0","right"); 

                $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                ->join("item_boucher_pago as ibp","ibp.idItemBoucherPago", "=","ibpn.fkItemBoucher")
                ->join("boucherpago as bp","bp.idBoucherPago", "=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->selectRaw("sum(ibpn.valor) as valor")
                ->where("ibpn.fkNovedad", "=",$novedadVac->idNovedad)
                ->whereBetween("ln.fechaLiquida",[$fechaInicioMesActual, $fechaFin])
                ->first();                

                $restaIbc = 0;
                if($novedadVac->pagoAnticipado == 1){
                    if(isset($itemBoucherNovedad) && $itemBoucherNovedad->valor > 0){
                        $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                        $restaIbc = $valorNovedad;
                        $valorNovedad = $diasPagoVac*$valorNovedad/$diasTotales;
                        
                    }
                    else{
                        $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                        ->where("ibpn.fkNovedad", "=",$novedadVac->idNovedad)
                        ->first();
                        $restaIbc = 0;
                        $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                        $valorNovedad = $diasPagoVac*$valorNovedad/$diasTotales;
                       
                    }
                   
                    
                }
                else{
                    
                    
                    
                    $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);    
                    $restaIbc = $valorNovedad;
                }
                


                $arrayPlace[41] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[42] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[43] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[44] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[94] = $this->plantillaTxt(round($valorNovedad),9,"0","right");

                $arrayFila[62] = $this->plantillaTxt(0,9,"0","right");


                $ibcAFP = $ibcAFP - $restaIbc;
                $ibcEPS = $ibcEPS - $restaIbc;
                $ibcARL = $ibcARL - $restaIbc;
                $ibcCCF = $ibcCCF - $restaIbc;
                $ibcOtros = $ibcOtros - $restaIbc;



                if(strtotime($novedadVac->fechaFin) > strtotime($fechaFin)){
                    $novedadVac->fechaFin=$fechaFin;
                }
                if(strtotime($novedadVac->fechaInicio) < strtotime($fechaInicioMesActual)){
                    $novedadVac->fechaInicio=$fechaInicioMesActual;
                }
                
                if(substr($novedadVac->fechaFin, 8, 2) == "31"){
                    
                    $arrayPlace[22] = $this->plantillaTxt("X",1," ","left");
                    $novedadVac->fechaFin = substr($novedadVac->fechaFin, 0, 8)."30";
                }


                $fechaInicioVAC = date("Y-m-d",strtotime($novedadVac->fechaInicio));
                $fechaFinVAC =  date("Y-m-d",strtotime($novedadVac->fechaFin));
                $arrayPlace[88] = $this->plantillaTxt($fechaInicioVAC,10," ","left");
                $arrayPlace[89] = $this->plantillaTxt($fechaFinVAC,10," ","left");


                //Tarifa en 0 para ausentismos
                $arrayPlace[60] =  $this->plantillaTxt("0.0",9,"0","left");
                $arrayPlace[62] = $this->plantillaTxt("0",9,"0","right");

                array_push($arrayNuevoRegistro, $arrayPlace);   
            }

            

            
            //LICENCIAS REMUNERADAS
            $novedadesLIC = DB::table("novedad","n")
            ->join("licencia as l","l.idLicencia","=", "n.fkLicencia")
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["8"]) // Pagada-> no que este eliminada
            ->whereNotNull("n.fkLicencia")
            ->whereBetween("n.fechaRegistro",[$fechaInicioMesActual, $fechaFinMesActual])
            ->get();

            foreach($novedadesLIC as $novedadLIC){
                $arrayPlace = $arraySinNada;
                $arrayPlace[26] = $this->plantillaTxt("L",1," ","left");
        
                $novedadLIC->numDias = intval( $novedadLIC->numDias);

                $periodoTrabajado = $periodoTrabajado - $novedadLIC->numDias;
                if($empleado->esPensionado == 0){
                    $arrayPlace[35] = $this->plantillaTxt($novedadLIC->numDias,2,"0","right");
                }
                else{
                    $arrayPlace[35] = $this->plantillaTxt("",2,"0","right");
                }
                $arrayPlace[36] = $this->plantillaTxt($novedadLIC->numDias,2,"0","right");
                $arrayPlace[37] = $this->plantillaTxt($novedadLIC->numDias,2,"0","right"); 
                $arrayPlace[38] = $this->plantillaTxt($novedadLIC->numDias,2,"0","right"); 

                $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                ->where("ibpn.fkNovedad", "=",$novedadLIC->idNovedad)
                ->first();
                
                $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                $arrayPlace[41] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[42] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[43] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[44] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[94] = $this->plantillaTxt(round($valorNovedad),9,"0","right");

                $arrayFila[62] = $this->plantillaTxt(0,9,"0","right");


                $ibcAFP = $ibcAFP - $valorNovedad;
                $ibcEPS = $ibcEPS - $valorNovedad;
                $ibcARL = $ibcARL - $valorNovedad;
                $ibcCCF = $ibcCCF - $valorNovedad;
                $ibcOtros = $ibcOtros - $valorNovedad;




                $fechaInicioLIC = date("Y-m-d",strtotime($novedadLIC->fechaInicial));
                $fechaFinLIC =  date("Y-m-d",strtotime($novedadLIC->fechaFinal));
                $arrayPlace[88] = $this->plantillaTxt($fechaInicioLIC,10," ","left");
                $arrayPlace[89] = $this->plantillaTxt($fechaFinLIC,10," ","left");

                //Tarifa en 0 para ausentismos
                $arrayPlace[60] =  $this->plantillaTxt("0.0",9,"0","left");
                $arrayPlace[62] = $this->plantillaTxt("0",9,"0","right");


                array_push($arrayNuevoRegistro, $arrayPlace);   
            }


            $arrayFila[26] = $this->plantillaTxt(" ",1," ","left");
            $arrayFila[88] = $this->plantillaTxt("",10," ","left");
            $arrayFila[89] = $this->plantillaTxt("",10," ","left");

            
            //AVP
            $itemsBoucherAVP = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.descuento) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","ibp.fkConcepto")                
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
            ->where("gcc.fkGrupoConcepto","=","6") //6 - APORTE VOLUNTARIO PENSION	
            ->get();

            $aporteVoluntarioPension = 0;
            if(sizeof($itemsBoucherAVP)>0){
                if($itemsBoucherAVP[0]->suma > 0){
                    

                    $arrayPlace = $arraySinNada;
                    $aporteVoluntarioPension = $itemsBoucherAVP[0]->suma;
                    
                    $arrayPlace[47] = $this->plantillaTxt($itemsBoucherAVP[0]->suma,9,"0","right");
                    $arrayPlace[27] = $this->plantillaTxt("X",1," ","left");
                    array_push($arrayNuevoRegistro, $arrayPlace);  
                }
            } 
            $arrayFila[27] = $this->plantillaTxt(" ",1," ","left");
            $arrayFila[47] = $this->plantillaTxt("",9,"0","right");
            
            
            //VCT
            $cambioCentroTrab = DB::table("cambiocentrotrabajo","cct")
                ->where("cct.fkEmpleado", "=", $empleado->idempleado)
                ->whereBetween("cct.fechaCambio", [$fechaInicioMesActual, $fechaFinMesActual])
                ->get();
            
            if(sizeof($cambioCentroTrab)>0){
                $arrayPlace = $arraySinNada;
                $arrayPlace[28] = $this->plantillaTxt("X",1," ","left");
                $arrayPlace[90] = $this->plantillaTxt($cambioCentroTrab[0]->fechaCambio,10," ","left");
                $arrayPlace[91] = $this->plantillaTxt($fechaFinMesActual,10," ","left");
                array_push($arrayNuevoRegistro, $arrayPlace);  
            }
            else{
                $arrayFila[28] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[90] = $this->plantillaTxt("",10," ","left");
                $arrayFila[91] = $this->plantillaTxt("",10," ","left");
            }
            
            //IRL
            $novedadesIncapacidadLab = DB::table("novedad","n")
            ->join("incapacidad as i","i.idIncapacidad","=", "n.fkIncapacidad")
            ->whereNull("i.fkTipoAfilicacion") // NULL - Accidente laboral
            ->whereNotIn("i.tipoIncapacidad",["Maternidad", "Paternidad"])
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["8"]) // Pagada o sin pagar-> no que este eliminada
            ->whereNotNull("n.fkIncapacidad")
            ->whereBetween("n.fechaRegistro",[$fechaInicioMesActual, $fechaFinMesActual])
            ->get();
            foreach($novedadesIncapacidadLab as $novedadIncapacidadLab){
                $arrayPlace = $arraySinNada;
                $arrayPlace[29] = $this->plantillaTxt($novedadIncapacidadLab->numDias,2,"0","right");

                $novedadIncapacidadLab->numDias = intval( $novedadIncapacidadLab->numDias);

                $periodoTrabajado = $periodoTrabajado - $novedadIncapacidadLab->numDias;
                if($empleado->esPensionado == 0){
                    $arrayPlace[35] = $this->plantillaTxt($novedadIncapacidadLab->numDias,2,"0","right");
                }
                else{
                    $arrayPlace[35] = $this->plantillaTxt("",2,"0","right");
                }
                $arrayPlace[36] = $this->plantillaTxt($novedadIncapacidadLab->numDias,2,"0","right");
                $arrayPlace[37] = $this->plantillaTxt($novedadIncapacidadLab->numDias,2,"0","right"); 
                $arrayPlace[38] = $this->plantillaTxt($novedadIncapacidadLab->numDias,2,"0","right"); 

                
                $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                ->where("ibpn.fkNovedad", "=",$novedadIncapacidadLab->idNovedad)
                ->first();
                
                
                $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                $arrayPlace[41] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[42] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[43] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[44] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                $arrayPlace[94] = $this->plantillaTxt(round($valorNovedad),9,"0","right");
                


                $ibcAFP = $ibcAFP - $valorNovedad;
                $ibcEPS = $ibcEPS - $valorNovedad;
                $ibcARL = $ibcARL - $valorNovedad;
                $ibcCCF = $ibcCCF - $valorNovedad;
                $ibcOtros = $ibcOtros - $valorNovedad;



                $fechaInicioIRL = date("Y-m-d",strtotime($novedadIncapacidadLab->fechaInicial));
                $fechaFinIRL =  date("Y-m-d",strtotime($novedadIncapacidadLab->fechaFinal));
                $arrayPlace[92] = $this->plantillaTxt($fechaInicioIRL,10," ","left");
                $arrayPlace[93] = $this->plantillaTxt($fechaFinIRL,10," ","left");
                array_push($arrayNuevoRegistro, $arrayPlace);   
            }
            $arrayFila[29] = $this->plantillaTxt("",2,"0","right");
            $arrayFila[92] = $this->plantillaTxt("",10," ","left");
            $arrayFila[93] = $this->plantillaTxt("",10," ","left");


            //Codigo Pension
            if($empleado->esPensionado == 0){
                $pension = DB::table("afiliacion","a")
                ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
                ->where("a.fkEmpleado", "=", $empleado->idempleado)
                ->where("a.fkTipoAfilicacion", "=", "4") // 4 - Tipo Afiliacion = Pension
                ->first();
                $arrayFila[30] = $this->plantillaTxt($pension->codigoTercero,6," ","left");
            }
            else{
                $arrayFila[30] = $this->plantillaTxt("",6," ","left");
            }
        


            $salud = DB::table("afiliacion","a")
            ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
            ->where("a.fkEmpleado", "=", $empleado->idempleado)
            ->where("a.fkTipoAfilicacion", "=", "3") // 3 - Tipo Afiliacion = Salud
            ->first();

            $arrayFila[32] = $this->plantillaTxt($salud->codigoTercero,6," ","left");
            
            
            //CCF
            $ccf = DB::table("afiliacion","a")
            ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
            ->where("a.fkEmpleado", "=", $empleado->idempleado)
            ->where("a.fkTipoAfilicacion", "=", "2") // 2 - Tipo Afiliacion = Caja de compensacion
            ->first();
            $arrayFila[34] = $this->plantillaTxt($ccf->codigoTercero,6," ","left");
            
            //AFP días

            
            
            if($empleado->esPensionado == 0){
                $arrayFila[35] = $this->plantillaTxt($periodoTrabajado,2,"0","right");
            }
            else{
                $arrayFila[35] = $this->plantillaTxt("0",2,"0","right");
            }
            //EPS días
            $arrayFila[36] = $this->plantillaTxt($periodoTrabajado,2,"0","right");
            //ARL días
            $arrayFila[37] = $this->plantillaTxt($periodoTrabajado,2,"0","right");
            //CCF días
            $arrayFila[38] = $this->plantillaTxt($periodoTrabajado,2,"0","right");

            
            
            $arrayFila[39] = $this->plantillaTxt(intval($conceptoFijoSalario->valor),9,"0","right");

            if($empleado->tipoRegimen=="Salario Integral"){
                $arrayFila[40] = $this->plantillaTxt("X",1," ","left");
            }
            else{
                if(date("Y",strtotime($fechaFin))<=2020 && date("m",strtotime($fechaFin))<=6){
                    $arrayFila[40] = $this->plantillaTxt(" ",1," ","left");
                }
                else{
                    $arrayFila[40] = $this->plantillaTxt("F",1," ","left");
                }
                
                
            }

            
            
        

            //$ibcAFP = ($ultimoBoucher->ibc_afp/30) * $periodoTrabajadoSinNov;
            
            $minimosRedondeo = DB::table("tabla_smmlv_redondeo")->where("dias","=",$periodoTrabajado)->first();
            if(!isset($minimosRedondeo)){
                $minimosRedondeo = DB::table("tabla_smmlv_redondeo")->where("dias","=","1")->first();
            }
            
            //$ibcAFP = $ultimoBoucher->ibc_afp;
            //$ibcAFP = ($ultimoBoucher->ibc_afp * $periodoTrabajado) / $periodoTrabajadoSinNov;
            if($ibcAFP < $minimosRedondeo->ibc && $ibcAFP > 0){
                $ibcAFP = $minimosRedondeo->ibc;
            }


            $arrayFila[41] = $this->plantillaTxt(round($ibcAFP),9,"0","right");

            //$ibcEPS = ($ultimoBoucher->ibc_eps/30) * $periodoTrabajadoSinNov;

            //$ibcEPS = $ultimoBoucher->ibc_eps;
            //$ibcEPS = ($ultimoBoucher->ibc_eps * $periodoTrabajado) / $periodoTrabajadoSinNov;
            if($ibcEPS < $minimosRedondeo->ibc && $ibcEPS > 0){
                $ibcEPS = $minimosRedondeo->ibc;
            }

            $arrayFila[42] = $this->plantillaTxt(round($ibcEPS),9,"0","right");

            //$ibcARL = ($ultimoBoucher->ibc_arl/30) * $periodoTrabajadoSinNov;

            //$ibcARL = $ultimoBoucher->ibc_arl;
            //$ibcARL = ($ultimoBoucher->ibc_arl * $periodoTrabajado) / $periodoTrabajadoSinNov;
            if($ibcARL < $minimosRedondeo->ibc && $ibcARL > 0){
                $ibcARL = $minimosRedondeo->ibc;
            }

            $arrayFila[43] = $this->plantillaTxt(round($ibcARL),9,"0","right");

           


            //$ibcCCF = ($ultimoBoucher->ibc_ccf/30) * $periodoTrabajado;
            //$ibcCCF = $ultimoBoucher->ibc_ccf;
            //$ibcCCF = ($ultimoBoucher->ibc_ccf * $periodoTrabajado) / $periodoTrabajadoSinNov;
            if($ibcCCF < $minimosRedondeo->ibc && $ibcCCF > 0){
                $ibcCCF = $minimosRedondeo->ibc;
            }

            $arrayFila[44] = $this->plantillaTxt(round($ibcCCF),9,"0","right");
            $totalNomina = $totalNomina + $ibcCCF;



            $parafiscales = DB::table("parafiscales","p")
            ->selectRaw("Sum(p.afp) as suma_afp, Sum(p.eps) as suma_eps, Sum(p.arl) as suma_arl, Sum(p.ccf) as suma_ccf, Sum(p.icbf) as suma_icbf, Sum(p.sena) as suma_sena")
            ->join("boucherpago as bp","bp.idBoucherPago","=","p.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
            ->get();

            $itemsBoucherAFP = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.descuento) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
            ->where("ibp.fkConcepto","=","19") //19 - PENSION
            ->get();

            $varsPension = DB::table("variable", "v")->whereIn("v.idVariable",["51","52"])->get();
            $totalPorcentajePension = 0;
            foreach($varsPension as $varPension){
                $totalPorcentajePension = $totalPorcentajePension + floatval($varPension->valor);
            }
            
            $cotizacionPension = 0;
            
            if($empleado->esPensionado==0){
                //TARIFA AFP
                $arrayFila[45] = $this->plantillaTxt($totalPorcentajePension,7,"0","left");

            
                foreach($itemsBoucherAFP as $itemBoucherAFP){
                    $cotizacionPension = $cotizacionPension + $itemBoucherAFP->suma;
                }
                foreach($parafiscales as $parafiscal){
                    $cotizacionPension = $cotizacionPension + $parafiscal->suma_afp;
                }
                //$cotizacionPension= round(($cotizacionPension/30) * $periodoTrabajadoSinNov, -2);
                //Cotizacion AFP
                
                $cotizacionPension = $ibcAFP*$totalPorcentajePension;

                $cotizacionPension = $this->roundSup($cotizacionPension, -2);
                if($cotizacionPension < $minimosRedondeo->pension && $cotizacionPension > 0){
                    $cotizacionPension = $minimosRedondeo->pension;
                }


                $arrayFila[46] = $this->plantillaTxt($cotizacionPension,9,"0","right");

                

            }
            else{
                $arrayFila[45] = $this->plantillaTxt("0.0",7,"0","left");
                $arrayFila[46] = $this->plantillaTxt("",9,"0","right");
            }

            //Aporte voluntario del aportante
            $arrayFila[48] = $this->plantillaTxt("",9,"0","right");

            //total cotizacion AFP
            //$totalCotizacionAFP = $cotizacionPension + $aporteVoluntarioPension;
            $totalCotizacionAFP = $cotizacionPension;
            $arrayFila[49] = $this->plantillaTxt(intval($totalCotizacionAFP),9,"0","right");



            //FSP SOLIDARIDAD	            

            $itemsBoucherFPS = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.descuento) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
            ->where("ibp.fkConcepto","=","33") //33 - FPS
            ->get();

            $totalFPS = 0;
            foreach($itemsBoucherFPS as $itemBoucherFPS){
                $totalFPS = $totalFPS + $itemBoucherFPS->suma;
            }
            
           /* if($empleado->idempleado == 32){
                dd($ultimoBoucher);
            }*/
            

            if($totalFPS > 0){
                $valorSalario = $ultimoBoucher->ibc_afp;        

                $variablesAporteFondo = DB::table("variable")->whereIn("idVariable",[11,12,13,14,15])->get();
                $varAporteFondo = array();
                foreach($variablesAporteFondo as $variablesAporteFond){
                    $varAporteFondo[$variablesAporteFond->idVariable] = $variablesAporteFond->valor;
                }
                
                $variables = DB::table("variable")->where("idVariable","=","1")->first();
                $valorSalarioMinimo = $variables->valor;
            
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
                
                $totalFPS = $ibcAFP*$porcentajeDescuento;
                
                $paraFPS = ($totalFPS * 0.005)/$porcentajeDescuento;
                
                $paraFS = $totalFPS - $paraFPS;

                $paraFPS = $this->roundSup($paraFPS, -2);
                
                $paraFS = $this->roundSup($paraFS, -2);

                
                $arrayFila[50] = $this->plantillaTxt(intval($paraFPS),9,"0","right");
                $arrayFila[51] = $this->plantillaTxt(intval($paraFS),9,"0","right");
            }
            else{
                $arrayFila[50] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[51] = $this->plantillaTxt("",9,"0","right");
            }
            $arrayFila[52] = $this->plantillaTxt("",9,"0","right");



            $varsEPS = DB::table("variable", "v")->whereIn("v.idVariable",["49","50"])->get();
            $totalPorcentajeEPS = 0;
            foreach($varsEPS as $varEPS){
                if($ultimoBoucher->ibc_otros==0 && $varEPS->idVariable == "50"){
                    
                }
                else{
                    $totalPorcentajeEPS = $totalPorcentajeEPS + floatval($varEPS->valor);
                }

                
            }

            $arrayFila[53] =$this->plantillaTxt($totalPorcentajeEPS,7,"0","left");   

            $itemsBoucherESP = DB::table("item_boucher_pago", "ibp")
            ->selectRaw("Sum(ibp.descuento) as suma")
            ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->where("bp.fkEmpleado","=",$empleado->idempleado)
            ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
            ->where("ibp.fkConcepto","=","18") //18 - SALUD
            ->get();
            $cotizacionSalud = 0;
            foreach($itemsBoucherESP as $itemBoucherESP){
                $cotizacionSalud = $cotizacionSalud + $itemBoucherESP->suma;
            }
            foreach($parafiscales as $parafiscal){
                $cotizacionSalud = $cotizacionSalud + $parafiscal->suma_eps;
            }

            $cotizacionSalud = $ibcEPS*$totalPorcentajeEPS;


            if($totalPorcentajeEPS == 0.125){
                if($cotizacionSalud < $minimosRedondeo->salud_12_5 && $cotizacionSalud > 0){
                    $cotizacionSalud = $minimosRedondeo->salud_12_5;
                }
            }
            if($totalPorcentajeEPS == 0.12){
                if($cotizacionSalud < $minimosRedondeo->salud_12 && $cotizacionSalud > 0){
                    $cotizacionSalud = $minimosRedondeo->salud_12;
                }
            }
            if($totalPorcentajeEPS == 0.1){
                if($cotizacionSalud < $minimosRedondeo->salud_10 && $cotizacionSalud > 0){
                    $cotizacionSalud = $minimosRedondeo->salud_10;
                }
            }
            if($totalPorcentajeEPS == 0.08){
                if($cotizacionSalud < $minimosRedondeo->salud_8 && $cotizacionSalud > 0){
                    $cotizacionSalud = $minimosRedondeo->salud_8;
                }
            }
            if($totalPorcentajeEPS == 0.04){
                if($cotizacionSalud < $minimosRedondeo->salud_4 && $cotizacionSalud > 0){
                    $cotizacionSalud = $minimosRedondeo->salud_4;
                }
            }

            
            //$cotizacionSalud= round(($cotizacionSalud/30) * $periodoTrabajadoSinNov, -2);
            $cotizacionSalud = $this->roundSup($cotizacionSalud, -2);
            $arrayFila[54] =$this->plantillaTxt(round($cotizacionSalud, -2),9,"0","right");

            //Valor de la UPC adicional.
            $arrayFila[55] =$this->plantillaTxt("",9,"0","right");
            
            $arrayFila[56] =$this->plantillaTxt("",15," ","left");
            $arrayFila[57] =$this->plantillaTxt("",9,"0","left");
            $arrayFila[58] =$this->plantillaTxt("",15," ","left");
            $arrayFila[59] =$this->plantillaTxt("",9,"0","left");


            //TARIFA RIESGOS
            $nivelesArl = DB::table("nivel_arl","na")
            ->where("na.idnivel_arl","=",$empleado->fkNivelArl)
            ->first();
            $arrayFila[60] = $this->plantillaTxt(($nivelesArl->porcentaje / 100),9,"0","left");
            
            //Centro de Trabajo
            $centroTrabajo = DB::table("centrotrabajo","ct")
            ->where("ct.idCentroTrabajo","=",$empleado->fkCentroTrabajo)
            ->first();

            $arrayFila[61] = $this->plantillaTxt($centroTrabajo->codigo,9,"0","right");


            $cotizacionArl = 0;
            foreach($parafiscales as $parafiscal){
                $cotizacionArl = $cotizacionArl + $parafiscal->suma_arl;
            }

            //$cotizacionArl = round(($cotizacionArl/30) * $periodoTrabajadoSinNov, -2);
            $cotizacionArl = $ibcARL*($nivelesArl->porcentaje / 100);

            
            $cotizacionArl = $this->roundSup($cotizacionArl, -2);
            if($empleado->fkNivelArl == 1){
                if($cotizacionArl < $minimosRedondeo->riesgos_1 && $cotizacionArl > 0){
                    $cotizacionArl = $minimosRedondeo->riesgos_1;
                }
            }
            if($empleado->fkNivelArl == 2){
                if($cotizacionArl < $minimosRedondeo->riesgos_2 && $cotizacionArl > 0){
                    $cotizacionArl = $minimosRedondeo->riesgos_2;
                }
            }
            if($empleado->fkNivelArl == 3){
                if($cotizacionArl < $minimosRedondeo->riesgos_3 && $cotizacionArl > 0){
                    $cotizacionArl = $minimosRedondeo->riesgos_3;
                }
            }
            if($empleado->fkNivelArl == 4){
                if($cotizacionArl < $minimosRedondeo->riesgos_4 && $cotizacionArl > 0){
                    $cotizacionArl = $minimosRedondeo->riesgos_4;
                }
            }
            if($empleado->fkNivelArl == 5){
                if($cotizacionArl < $minimosRedondeo->riesgos_5 && $cotizacionArl > 0){
                    $cotizacionArl = $minimosRedondeo->riesgos_5;
                }
            }

            $arrayFila[62] = $this->plantillaTxt($cotizacionArl,9,"0","right");



            //TARIFA CCF
            $varsCCF = DB::table("variable", "v")->whereIn("v.idVariable",["53"])->get();
            $totalPorcentajeCCF = 0;
            foreach($varsCCF as $varCCF){
                $totalPorcentajeCCF = $totalPorcentajeCCF + floatval($varCCF->valor);
            }
            $arrayFila[63] = $this->plantillaTxt($totalPorcentajeCCF,7,"0","left");    

            //VALOR CCF
            $ccfFinal = 0;
            foreach($parafiscales as $parafiscal){
                $ccfFinal = $ccfFinal + $parafiscal->suma_ccf;
            }
            //$ccfFinal = ($ccfFinal/30) * $periodoTrabajado;

            
            $ccfFinal = $ibcCCF*$totalPorcentajeCCF;
            $ccfFinal = $this->roundSup($ccfFinal, -2);

            if($ccfFinal < $minimosRedondeo->ccf && $ccfFinal > 0){
                $ccfFinal = $minimosRedondeo->ccf;
            }

            $arrayFila[64] = $this->plantillaTxt($ccfFinal,9,"0","right");



            //TARIFA SENA
            $varsSENA = DB::table("variable", "v")->whereIn("v.idVariable",["55"])->get();
            $totalPorcentajeSENA = 0;
            foreach($varsSENA as $varSENA){
                $totalPorcentajeSENA = $totalPorcentajeSENA + floatval($varSENA->valor);
            }
            if($ultimoBoucher->ibc_otros==0){
                $totalPorcentajeSENA = "0.0";
            }

            $arrayFila[65] = $this->plantillaTxt($totalPorcentajeSENA,7,"0","left");  

            //VALOR SENA
            $SENAFinal = 0;
            foreach($parafiscales as $parafiscal){
                $SENAFinal = $SENAFinal + $parafiscal->suma_sena;
            }
            //$SENAFinal = ($SENAFinal/30) * $periodoTrabajadoSinNov;
            $SENAFinal = $ibcOtros*$totalPorcentajeSENA;
            $SENAFinal = $this->roundSup($SENAFinal, -2);

            if($totalPorcentajeSENA == 0.005){
                if($SENAFinal < $minimosRedondeo->sena_0_5 && $SENAFinal > 0){
                    $SENAFinal = $minimosRedondeo->sena_0_5;
                }
            }
            if($totalPorcentajeSENA == 0.02){
                if($SENAFinal < $minimosRedondeo->sena_2 && $SENAFinal > 0){
                    $SENAFinal = $minimosRedondeo->sena_2;
                }
            }
            
            $arrayFila[66] = $this->plantillaTxt(intval($SENAFinal),9,"0","right");

            //TARIFA ICBF
            $varsICBF = DB::table("variable", "v")->whereIn("v.idVariable",["54"])->get();
            $totalPorcentajeICBF = 0;
            foreach($varsICBF as $varICBF){
                $totalPorcentajeICBF = $totalPorcentajeICBF + floatval($varICBF->valor);
            }
            if($ultimoBoucher->ibc_otros==0){
                $totalPorcentajeICBF = "0.0";
            }
            $arrayFila[67] = $this->plantillaTxt($totalPorcentajeICBF,7,"0","left");  

            //VALOR ICBF
            $ICBFFinal = 0;
            foreach($parafiscales as $parafiscal){
                $ICBFFinal = $ICBFFinal + $parafiscal->suma_icbf;
            }
            //$ICBFFinal = ($ICBFFinal/30) * $periodoTrabajadoSinNov;     
            
            $ICBFFinal = $ibcOtros*$totalPorcentajeICBF;
            $ICBFFinal = $this->roundSup($ICBFFinal, -2);


            if($ICBFFinal < $minimosRedondeo->icbf && $ICBFFinal > 0){
                $ICBFFinal = $minimosRedondeo->icbf;
            }
   
            
            $arrayFila[68] = $this->plantillaTxt(intval($ICBFFinal),9,"0","right");

            $arrayFila[69] = $this->plantillaTxt("0.0",7,"0","left");  
            $arrayFila[70] = $this->plantillaTxt("",9,"0","right");

            $arrayFila[71] = $this->plantillaTxt("0.0",7,"0","left");  
            $arrayFila[72] = $this->plantillaTxt("",9,"0","right");


            $arrayFila[73] = $this->plantillaTxt("",2," ","right");
            $arrayFila[74] = $this->plantillaTxt("",16," ","right");

            if($ultimoBoucher->ibc_otros==0){
                $arrayFila[75] = $this->plantillaTxt("S",1,"","left");
            }
            else{
                $arrayFila[75] = $this->plantillaTxt("N",1,"","left");
            }

            $arrayFila[76] = $this->plantillaTxt($empresa->codigoArl,6," ","left");

            $arrayFila[77] = $this->plantillaTxt($empleado->fkNivelArl,1,"","left");

            $arrayFila[78] = $this->plantillaTxt("",1," ","left");

            //$arrayFila[94] = $this->plantillaTxt($ultimoBoucher->ibc_otros,9,"0","right");
            if($ultimoBoucher->ibc_otros!=0){
                //$ibcOtros = ($ultimoBoucher->ibc_otros/30) * $periodoTrabajado;
          
                //$ibcOtros = $this->roundSup($ibcOtros, -2);
                if($ibcOtros < $minimosRedondeo->ibc && $ibcOtros > 0){
                    $ibcOtros = $minimosRedondeo->ibc;
                }
               
                $arrayFila[94] = $this->plantillaTxt(round($ibcOtros),9,"0","right");
            }
            else{
                $arrayFila[94] = $this->plantillaTxt("0",9,"0","right");
            }
            
            $horasTrabajadas = $periodoTrabajado*8;
            $novedadesHorasExtras = DB::table("novedad","n")
            ->join("horas_extra as h","h.idHoraExtra","=", "n.fkHorasExtra")
            ->where("n.fkEmpleado","=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["8"]) // Pagada o sin pagar-> no que este eliminada
            ->whereNotNull("n.fkHorasExtra")
            ->whereBetween("n.fechaRegistro",[$fechaInicioMesActual, $fechaFinMesActual])
            ->get();

            foreach($novedadesHorasExtras as $novedadHorasExtras){
                $horasTrabajadas= $horasTrabajadas + ceil($novedadHorasExtras->cantidadHoras);
                
            }
            if($horasTrabajadas > 300){
                $horasTrabajadas = 300;
            }
            
            $arrayFila[95] = $this->plantillaTxt($horasTrabajadas,3,"0","right");
            $arrayFila[96] = $this->plantillaTxt("",10," ","right");
            $arrayFila = $this->upperCaseAllArray($arrayFila);
            if($periodoTrabajado > 0){
                array_push($arrayMuestra, $arrayFila);
                $contador++;
            }
            
            
           
            foreach($arrayNuevoRegistro as $arrayRegistroN){
              
                $arrayFila2 = $arrayRegistroN;
                $arrayFila2[1] = $this->plantillaTxt($contador,5,"0","right");
                $arrayFila2[14] = $this->plantillaTxt(" ",1,"","left");
                $arrayFila2[79] = $this->plantillaTxt("",10," ","left");
                $arrayFila2[15] = $this->plantillaTxt("",1," ","left");
                $arrayFila2[80] = $this->plantillaTxt("",10," ","left");
                if(!isset($arrayFila2[16])){
                    $arrayFila2[16] = $this->plantillaTxt(" ",1," ","left");
                }
                if(!isset($arrayFila2[17])){
                    $arrayFila2[17] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[33] = $this->plantillaTxt(" ",6," ","left");
                }
                if(!isset($arrayFila2[18])){
                    $arrayFila2[18] = $this->plantillaTxt(" ",1," ","left");
                }
                if(!isset($arrayFila2[19])){
                    $arrayFila2[19] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[31] = $this->plantillaTxt(" ",6," ","left");
                }
                $arrayFila2[20] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila2[81] = $this->plantillaTxt("",10," ","left");
                $arrayFila2[21] = $this->plantillaTxt(" ",1," ","left");
                if(!isset($arrayFila2[22])){
                    $arrayFila2[22] = $this->plantillaTxt(" ",1," ","left");
                }
            
                if(!isset($arrayFila2[23])){
                    $arrayFila2[23] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[82] = $this->plantillaTxt("",10," ","left");
                    $arrayFila2[83] = $this->plantillaTxt("",10," ","left");
                
                }
                $periodoTrabajado = 0;
                if(isset($arrayFila2[36])){
                    $periodoTrabajado = intval($arrayFila2[36]);
                }
                else{
                    $arrayFila2[35] = $this->plantillaTxt("",2,"0","right");
                    $arrayFila2[36] = $this->plantillaTxt("",2,"0","right");
                    $arrayFila2[37] = $this->plantillaTxt("",2,"0","right"); 
                    $arrayFila2[38] = $this->plantillaTxt("",2,"0","right"); 
                }


                if(!isset($arrayFila2[24])){
                    $arrayFila2[24] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[84] = $this->plantillaTxt("",10," ","left");
                    $arrayFila2[85] = $this->plantillaTxt("",10," ","left");
                
                }
            
                if(!isset($arrayFila2[25])){
                    $arrayFila2[25] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[86] = $this->plantillaTxt("",10," ","left");
                    $arrayFila2[87] = $this->plantillaTxt("",10," ","left");
                
                }

                if(!isset($arrayFila2[26])){
                    $arrayFila2[26] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[88] = $this->plantillaTxt("",10," ","left");
                    $arrayFila2[89] = $this->plantillaTxt("",10," ","left");
                
                }

                if(!isset($arrayFila2[27])){
                    $arrayFila2[27] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[47] = $this->plantillaTxt("",9,"0","right");
                }

                if(!isset($arrayFila2[28])){
                    $arrayFila2[28] = $this->plantillaTxt(" ",1," ","left");
                    $arrayFila2[90] = $this->plantillaTxt("",10," ","left");
                    $arrayFila2[91] = $this->plantillaTxt("",10," ","left");
                }

                if(!isset($arrayFila2[29])){
                    $arrayFila2[29] = $this->plantillaTxt("",2,"0","right");
                    $arrayFila2[92] = $this->plantillaTxt("",10," ","left");
                    $arrayFila2[93] = $this->plantillaTxt("",10," ","left");
                }

                if($empleado->esPensionado == 0){
                    $pension = DB::table("afiliacion","a")
                    ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
                    ->where("a.fkEmpleado", "=", $empleado->idempleado)
                    ->where("a.fkTipoAfilicacion", "=", "4") // 4 - Tipo Afiliacion = Pension
                    ->first();
                    $arrayFila2[30] = $this->plantillaTxt($pension->codigoTercero,6," ","left");
                }
                else{
                    $arrayFila2[30] = $this->plantillaTxt("",6," ","left");
                }
            
                $salud = DB::table("afiliacion","a")
                ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
                ->where("a.fkEmpleado", "=", $empleado->idempleado)
                ->where("a.fkTipoAfilicacion", "=", "3") // 3 - Tipo Afiliacion = Salud
                ->first();
    
                $arrayFila2[32] = $this->plantillaTxt($salud->codigoTercero,6," ","left");
                
                
                //CCF
                $ccf = DB::table("afiliacion","a")
                ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
                ->where("a.fkEmpleado", "=", $empleado->idempleado)
                ->where("a.fkTipoAfilicacion", "=", "2") // 2 - Tipo Afiliacion = Caja de compensacion
                ->first();
                $arrayFila2[34] = $this->plantillaTxt($ccf->codigoTercero,6," ","left");;
                
                //AFP días
                if($empleado->esPensionado == 0){
                    $arrayFila2[35] = $this->plantillaTxt($periodoTrabajado,2,"0","right");
                }
                else{
                    $arrayFila2[35] = $this->plantillaTxt("0",2,"0","right");
                }
                //EPS días
                $arrayFila2[36] = $this->plantillaTxt($periodoTrabajado,2,"0","right");
                //ARL días
                $arrayFila2[37] = $this->plantillaTxt($periodoTrabajado,2,"0","right");
                //CCF días
                $arrayFila2[38] = $this->plantillaTxt($periodoTrabajado,2,"0","right");
    
                //Salario
                $conceptoFijoSalario = DB::table("conceptofijo", "cf")
                ->whereIn("cf.fkConcepto",["1","2"])
                ->where("cf.fkEmpleado", "=", $empleado->idempleado)
                ->first();
                
                $arrayFila2[39] = $this->plantillaTxt(intval($conceptoFijoSalario->valor),9,"0","right");
    
                if($empleado->tipoRegimen=="Salario Integral"){
                    $arrayFila2[40] = $this->plantillaTxt("X",1," ","left");
                }
                else{
                    if(date("Y",strtotime($fechaFin))<=2020 && date("m",strtotime($fechaFin))<=6){
                        $arrayFila2[40] = $this->plantillaTxt(" ",1," ","left");
                    }
                    else{
                        $arrayFila2[40] = $this->plantillaTxt("F",1," ","left");
                    }
                    //$arrayFila2[40] = $this->plantillaTxt("F",1," ","left");
                    //$arrayFila2[40] = $this->plantillaTxt(" ",1," ","left");
                }
    
                $ultimoBoucher = DB::table("boucherpago", "bp")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
                ->orderBy("bp.idBoucherPago","desc")
                ->first();
    
                //Parte 2
                if(!isset($arrayFila2[41])){
                    $ibcAFP = ($ultimoBoucher->ibc_afp * $periodoTrabajado) / $periodoTrabajadoSinNov;
                }
                else{
                    $ibcAFP = intval($arrayFila2[41]);
                }
                /*if($empleado->idempleado == 502){
                    echo $periodoTrabajado."<br>";
                    echo $ultimoBoucher->ibc_afp."<br>";
                    echo $periodoTrabajadoSinNov."<br>";
                    dd($ibcAFP);
                }*/
                
                $minimosRedondeo = DB::table("tabla_smmlv_redondeo")->where("dias","=",$periodoTrabajado)->first();

                //$ibcAFP = $ultimoBoucher->ibc_afp;


                if($ibcAFP < $minimosRedondeo->ibc && $ibcAFP > 0){
                    $ibcAFP = $minimosRedondeo->ibc;
                }

                $arrayFila2[41] = $this->plantillaTxt(round($ibcAFP),9,"0","right");
    
                //$ibcEPS = ($ultimoBoucher->ibc_eps/30) * $periodoTrabajadoSinNov;

                //$ibcEPS = $ultimoBoucher->ibc_eps;
                if(!isset($arrayFila2[42])){
                    $ibcEPS = ($ultimoBoucher->ibc_eps * $periodoTrabajado) / $periodoTrabajadoSinNov;
                }
                else{
                    $ibcEPS = intval($arrayFila2[42]);
                    
                }
                
                
                if($ibcEPS < $minimosRedondeo->ibc && $ibcEPS > 0){
                    $ibcEPS = $minimosRedondeo->ibc;
                }
                
                $arrayFila2[42] = $this->plantillaTxt(round($ibcEPS),9,"0","right");
    
                //$ibcARL = ($ultimoBoucher->ibc_arl/30) * $periodoTrabajadoSinNov;

                //$ibcARL = $ultimoBoucher->ibc_arl;
                
                if(!isset($arrayFila2[43])){
                    $ibcARL = ($ultimoBoucher->ibc_arl * $periodoTrabajado) / $periodoTrabajadoSinNov;
                }
                else{
                    $ibcARL = intval($arrayFila2[43]);
                }

                if($ibcARL < $minimosRedondeo->ibc && $ibcARL > 0){
                    $ibcARL = $minimosRedondeo->ibc;
                }


               
                $arrayFila2[43] = $this->plantillaTxt(round($ibcARL),9,"0","right");
    
                //$ibcCCF = ($ultimoBoucher->ibc_ccf/30) * $periodoTrabajado;
                
                
                if(!isset($arrayFila2[44])){
                    $ibcCCF = ($ultimoBoucher->ibc_ccf * $periodoTrabajado) / $periodoTrabajadoSinNov;
                }
                else{
                    $ibcCCF = intval($arrayFila2[44]);
                }
                
                if($ibcCCF < $minimosRedondeo->ibc && $ibcCCF > 0){
                    $ibcCCF = $minimosRedondeo->ibc;
                }
                $totalNomina = $totalNomina + $ibcCCF;
                $arrayFila2[44] = $this->plantillaTxt(round($ibcCCF),9,"0","right");
                
                $parafiscales = DB::table("parafiscales","p")
                ->selectRaw("Sum(p.afp) as suma_afp, Sum(p.eps) as suma_eps, Sum(p.arl) as suma_arl, Sum(p.ccf) as suma_ccf, Sum(p.icbf) as suma_icbf, Sum(p.sena) as suma_sena")
                ->join("boucherpago as bp","bp.idBoucherPago","=","p.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
                ->get();
    
                $itemsBoucherAFP = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.descuento) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
                ->where("ibp.fkConcepto","=","19") //19 - PENSION
                ->get();
    
                $varsPension = DB::table("variable", "v")->whereIn("v.idVariable",["51","52"])->get();
                $totalPorcentajePension = 0;
                foreach($varsPension as $varPension){
                    $totalPorcentajePension = $totalPorcentajePension + floatval($varPension->valor);
                }
                
                $cotizacionPension = 0;
                
                if($empleado->esPensionado==0){
                    //TARIFA AFP
                    $arrayFila2[45] = $this->plantillaTxt($totalPorcentajePension,7,"0","left");
    
                
                    foreach($itemsBoucherAFP as $itemBoucherAFP){
                        $cotizacionPension = $cotizacionPension + $itemBoucherAFP->suma;
                    }
                    foreach($parafiscales as $parafiscal){
                        $cotizacionPension = $cotizacionPension + $parafiscal->suma_afp;
                    }
                   
                    //$cotizacionPension= round(($cotizacionPension/30) * $periodoTrabajadoSinNov, -2);
                    
                    //Cotizacion AFP
                    $cotizacionPension = $ibcAFP*$totalPorcentajePension;

                    if($cotizacionPension < $minimosRedondeo->pension && $cotizacionPension > 0){
                        $cotizacionPension = $minimosRedondeo->pension;
                    }
                    $cotizacionPension = $this->roundSup($cotizacionPension, -2);
                    $arrayFila2[46] = $this->plantillaTxt($cotizacionPension,9,"0","right");
    
                    
    
                }
                else{
                    $arrayFila2[45] = $this->plantillaTxt("",7,"0","left");
                    $arrayFila2[46] = $this->plantillaTxt("",9,"0","right");
                }
    
                //Aporte voluntario del aportante
                $arrayFila2[48] = $this->plantillaTxt("",9,"0","right");
                $aporteVoluntarioPension = intval($arrayFila2[47]);
                //total cotizacion AFP
                $totalCotizacionAFP = $cotizacionPension + $aporteVoluntarioPension;
                $arrayFila2[49] = $this->plantillaTxt($totalCotizacionAFP,9,"0","right");
    
    
    
                //FSP SOLIDARIDAD	            
    
                $itemsBoucherFPS = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.descuento) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
                ->where("ibp.fkConcepto","=","33") //33 - FPS
                ->get();
    
                $totalFPS = 0;
                foreach($itemsBoucherFPS as $itemBoucherFPS){
                    $totalFPS = $totalFPS + $itemBoucherFPS->suma;
                }
    
                if($totalFPS > 0){
                    $valorSalario = $ultimoBoucher->ibc_afp;            
                    $variablesAporteFondo = DB::table("variable")->whereIn("idVariable",[11,12,13,14,15])->get();
                    $varAporteFondo = array();
                    foreach($variablesAporteFondo as $variablesAporteFond){
                        $varAporteFondo[$variablesAporteFond->idVariable] = $variablesAporteFond->valor;
                    }
                    
                    $variables = DB::table("variable")->where("idVariable","=","1")->first();
                    $valorSalarioMinimo = $variables->valor;
                
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
    
                    $totalFPS = $ibcAFP*$porcentajeDescuento;

                    $paraFPS = ($totalFPS * 0.005)/$porcentajeDescuento;
                
                    $paraFS = $totalFPS - $paraFPS;
                        
                    $paraFPS = $this->roundSup($paraFPS, -2);
                    
                    $paraFS = $this->roundSup($paraFS, -2);

                    $arrayFila2[50] = $this->plantillaTxt(intval($paraFPS),9,"0","right");
                    $arrayFila2[51] = $this->plantillaTxt(intval($paraFS),9,"0","right");
                }
                else{
                    $arrayFila2[50] = $this->plantillaTxt("",9,"0","right");
                    $arrayFila2[51] = $this->plantillaTxt("",9,"0","right");
                }
                $arrayFila2[52] = $this->plantillaTxt("",9,"0","right");
    
    
    
                $varsEPS = DB::table("variable", "v")->whereIn("v.idVariable",["49","50"])->get();
                $totalPorcentajeEPS = 0;
                foreach($varsEPS as $varEPS){
                    if($ultimoBoucher->ibc_otros==0 && $varEPS->idVariable == "50"){
                        
                    }
                    else{
                        $totalPorcentajeEPS = $totalPorcentajeEPS + floatval($varEPS->valor);
                    }
                    
                }
    
                $arrayFila2[53] =$this->plantillaTxt($totalPorcentajeEPS,7,"0","left");   
    
                $itemsBoucherESP = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.descuento) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$empleado->idempleado)
                ->whereRaw("(ln.fechaFin <= '".$fechaFinMesActual."' and ln.fechaInicio >= '".$fechaInicioMesActual."')")
                ->where("ibp.fkConcepto","=","18") //19 - PENSION
                ->get();
                $cotizacionSalud = 0;
                foreach($itemsBoucherESP as $itemBoucherESP){
                    $cotizacionSalud = $cotizacionSalud + $itemBoucherESP->suma;
                }
                foreach($parafiscales as $parafiscal){
                    $cotizacionSalud = $cotizacionSalud + $parafiscal->suma_eps;
                }

                $cotizacionSalud = $ibcEPS*$totalPorcentajeEPS;

                $cotizacionSalud = $this->roundSup($cotizacionSalud, -2);

                
                if($totalPorcentajeEPS == 0.125){
                    if($cotizacionSalud < $minimosRedondeo->salud_12_5 && $cotizacionSalud > 0){
                        $cotizacionSalud = $minimosRedondeo->salud_12_5;
                    }
                }
                if($totalPorcentajeEPS == 0.12){
                    if($cotizacionSalud < $minimosRedondeo->salud_12 && $cotizacionSalud > 0){
                        $cotizacionSalud = $minimosRedondeo->salud_12;
                    }
                }
                if($totalPorcentajeEPS == 0.1){
                    if($cotizacionSalud < $minimosRedondeo->salud_10 && $cotizacionSalud > 0){
                        $cotizacionSalud = $minimosRedondeo->salud_10;
                    }
                }
                if($totalPorcentajeEPS == 0.08){
                    if($cotizacionSalud < $minimosRedondeo->salud_8 && $cotizacionSalud > 0){
                        $cotizacionSalud = $minimosRedondeo->salud_8;
                    }
                }
                if($totalPorcentajeEPS == 0.04){
                    if($cotizacionSalud < $minimosRedondeo->salud_4 && $cotizacionSalud > 0){
                        $cotizacionSalud = $minimosRedondeo->salud_4;
                    }
                }
    
                //$cotizacionSalud= round(($cotizacionSalud/30) * $periodoTrabajadoSinNov, -2);
                $arrayFila2[54] =$this->plantillaTxt(round($cotizacionSalud),9,"0","right");
    
                //Valor de la UPC adicional.
                $arrayFila2[55] =$this->plantillaTxt("",9,"0","right");
                
                $arrayFila2[56] =$this->plantillaTxt("",15," ","left");
                $arrayFila2[57] =$this->plantillaTxt("",9,"0","left");
                $arrayFila2[58] =$this->plantillaTxt("",15," ","left");
                $arrayFila2[59] =$this->plantillaTxt("",9,"0","left");
    
    
                //TARIFA RIESGOS
                $nivelesArl = DB::table("nivel_arl","na")
                ->where("na.idnivel_arl","=",$empleado->fkNivelArl)
                ->first();
                //Parte 2
                if(!isset($arrayFila2[60])){
                    $arrayFila2[60] = $this->plantillaTxt(($nivelesArl->porcentaje / 100),9,"0","left");
                }
                
                
                //Centro de Trabajo
                $centroTrabajo = DB::table("centrotrabajo","ct")
                ->where("ct.idCentroTrabajo","=",$empleado->fkCentroTrabajo)
                ->first();
    
                $arrayFila2[61] = $this->plantillaTxt($centroTrabajo->codigo,9,"0","right");
    
    
                $cotizacionArl = 0;
                foreach($parafiscales as $parafiscal){
                    $cotizacionArl = $cotizacionArl + $parafiscal->suma_arl;
                }
    
                //$cotizacionArl = round(($cotizacionArl/30) * $periodoTrabajadoSinNov, -2);
                $cotizacionArl = $ibcARL*($nivelesArl->porcentaje / 100);

                $cotizacionArl = $this->roundSup($cotizacionArl, -2);
                if($empleado->fkNivelArl == 1){
                    if($cotizacionArl < $minimosRedondeo->riesgos_1 && $cotizacionArl > 0){
                        $cotizacionArl = $minimosRedondeo->riesgos_1;
                    }
                }
                if($empleado->fkNivelArl == 2){
                    if($cotizacionArl < $minimosRedondeo->riesgos_2 && $cotizacionArl > 0){
                        $cotizacionArl = $minimosRedondeo->riesgos_2;
                    }
                }
                if($empleado->fkNivelArl == 3){
                    if($cotizacionArl < $minimosRedondeo->riesgos_3 && $cotizacionArl > 0){
                        $cotizacionArl = $minimosRedondeo->riesgos_3;
                    }
                }
                if($empleado->fkNivelArl == 4){
                    if($cotizacionArl < $minimosRedondeo->riesgos_4 && $cotizacionArl > 0){
                        $cotizacionArl = $minimosRedondeo->riesgos_4;
                    }
                }
                if($empleado->fkNivelArl == 5){
                    if($cotizacionArl < $minimosRedondeo->riesgos_5 && $cotizacionArl > 0){
                        $cotizacionArl = $minimosRedondeo->riesgos_5;
                    }
                }

                //Parte 2
                if(!isset($arrayFila2[62])){
                    $arrayFila2[62] = $this->plantillaTxt(round($cotizacionArl),9,"0","right");
                }    
                
    
    
                //TARIFA CCF
                $varsCCF = DB::table("variable", "v")->whereIn("v.idVariable",["53"])->get();
                $totalPorcentajeCCF = 0;
                foreach($varsCCF as $varCCF){
                    $totalPorcentajeCCF = $totalPorcentajeCCF + floatval($varCCF->valor);
                }
                $arrayFila2[63] = $this->plantillaTxt($totalPorcentajeCCF,7,"0","left");    
    
                //VALOR CCF
                $ccfFinal = 0;
                foreach($parafiscales as $parafiscal){
                    $ccfFinal = $ccfFinal + $parafiscal->suma_ccf;
                }
                $ccfFinal = $ibcCCF*($totalPorcentajeCCF);
                //$ccfFinal = ($ccfFinal/30) * $periodoTrabajado;
                $ccfFinal = $this->roundSup($ccfFinal, -2);
                if($ccfFinal < $minimosRedondeo->ccf && $ccfFinal > 0){
                    $ccfFinal = $minimosRedondeo->ccf;
                }
    
                $arrayFila2[64] = $this->plantillaTxt($ccfFinal,9,"0","right");
    
    
    
                //TARIFA SENA
                $varsSENA = DB::table("variable", "v")->whereIn("v.idVariable",["55"])->get();
                $totalPorcentajeSENA = 0;
                foreach($varsSENA as $varSENA){
                    $totalPorcentajeSENA = $totalPorcentajeSENA + floatval($varSENA->valor);
                }
                if($ultimoBoucher->ibc_otros==0){
                    $totalPorcentajeSENA = "0.0";
                }
    
                $arrayFila2[65] = $this->plantillaTxt($totalPorcentajeSENA,7,"0","left");  
                
                if(!isset($arrayFila2[94])){
                    $ibcOtros = $ibcCCF;
                }
                else{
                    $ibcOtros = intval($arrayFila2[94]);
                }
                if($ibcOtros<0){
                    $ibcOtros = 0;
                }


                //VALOR SENA
                $SENAFinal = 0;
                foreach($parafiscales as $parafiscal){
                    $SENAFinal = $SENAFinal + $parafiscal->suma_sena;
                }
                //$SENAFinal = ($SENAFinal/30) * $periodoTrabajadoSinNov;  
                $SENAFinal = $ibcOtros*($totalPorcentajeSENA);
                $SENAFinal = $this->roundSup($SENAFinal, -2);

                if($totalPorcentajeSENA == 0.005){
                    if($SENAFinal < $minimosRedondeo->sena_0_5 && $SENAFinal > 0){
                        $SENAFinal = $minimosRedondeo->sena_0_5;
                    }
                }
                if($totalPorcentajeSENA == 0.02){
                    if($SENAFinal < $minimosRedondeo->sena_2 && $SENAFinal > 0){
                        $SENAFinal = $minimosRedondeo->sena_2;
                    }
                }     
                $arrayFila2[66] = $this->plantillaTxt(intval($SENAFinal),9,"0","right");
    
                //TARIFA ICBF
                $varsICBF = DB::table("variable", "v")->whereIn("v.idVariable",["54"])->get();
                $totalPorcentajeICBF = 0;
                foreach($varsICBF as $varICBF){
                    $totalPorcentajeICBF = $totalPorcentajeICBF + floatval($varICBF->valor);
                }
                if($ultimoBoucher->ibc_otros==0){
                    $totalPorcentajeICBF = "0.0";
                }
                $arrayFila2[67] = $this->plantillaTxt($totalPorcentajeICBF,7,"0","left");  
    
                //VALOR ICBF
                $ICBFFinal = 0;
                foreach($parafiscales as $parafiscal){
                    $ICBFFinal = $ICBFFinal + $parafiscal->suma_icbf;
                }

                //$ICBFFinal = ($ICBFFinal/30) * $periodoTrabajadoSinNov;        
                $ICBFFinal = $ibcOtros*($totalPorcentajeICBF);
                $ICBFFinal = $this->roundSup($ICBFFinal, -2);

                if($ICBFFinal < $minimosRedondeo->icbf && $ICBFFinal > 0){
                    $ICBFFinal = $minimosRedondeo->icbf;
                }
                
                $arrayFila2[68] = $this->plantillaTxt(intval($ICBFFinal),9,"0","right");


    
                $arrayFila2[69] = $this->plantillaTxt("0.0",7,"0","left");  
                $arrayFila2[70] = $this->plantillaTxt("",9,"0","right");
    
                $arrayFila2[71] = $this->plantillaTxt("0.0",7,"0","left");  
                $arrayFila2[72] = $this->plantillaTxt("",9,"0","right");
    
    
                $arrayFila2[73] = $this->plantillaTxt("",2," ","right");
                $arrayFila2[74] = $this->plantillaTxt("",16," ","right");
    
                if($ultimoBoucher->ibc_otros==0){
                    $arrayFila2[75] = $this->plantillaTxt("S",1,"","left");
                }
                else{
                    $arrayFila2[75] = $this->plantillaTxt("N",1,"","left");
                }
    
                $arrayFila2[76] = $this->plantillaTxt($empresa->codigoArl,6," ","left");
    
                $arrayFila2[77] = $this->plantillaTxt($empleado->fkNivelArl,1,"","left");
    
                $arrayFila2[78] = $this->plantillaTxt("",1," ","left");
    
                
                if($ultimoBoucher->ibc_otros!=0){
              
                    
                    if($ibcOtros < $minimosRedondeo->ibc && $ibcOtros > 0){
                        $ibcOtros = $minimosRedondeo->ibc;
                    }
                    $arrayFila2[94] = $this->plantillaTxt(round($ibcOtros),9,"0","right");
                }
                else{
                    $arrayFila2[94] = $this->plantillaTxt("0",9,"0","right");
                }


                $arrayFila2[95] = $this->plantillaTxt("0",3,"0","right");
                $arrayFila2[96] = $this->plantillaTxt("",10," ","right");
                $arrayFila2 = $this->upperCaseAllArray($arrayFila2);

                
                array_push($arrayMuestra, $arrayFila2);
                $contador++;


            }
            
            $upcAdicionales = DB::table("upcadicional","u")
            ->select("u.*","ti.siglaPila","ub.zonaUPC")
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion","=","u.fkTipoIdentificacion")
            ->join("ubicacion as ub", "ub.idubicacion", "=","u.fkUbicacion")
            ->where("u.fkEmpleado","=",$empleado->idempleado)
            ->get();
            
            foreach($upcAdicionales as $upcAdicional){
                $arrayFila = array();
                
                $arrayFila[0] = $this->plantillaTxt("02",2,"","right");
                $arrayFila[1] = $this->plantillaTxt($contador,5,"0","right");
                $arrayFila[2] = $this->plantillaTxt($upcAdicional->siglaPila,2," ","right");
                $arrayFila[3] = $this->plantillaTxt($upcAdicional->numIdentificacion,16," ","left");
                $arrayFila[4] = $this->plantillaTxt("40",2,"0","right");//Tipo cotizante
                $arrayFila[5] = $this->plantillaTxt($empleado->esPensionado,2,"0","right");//Subtipo de cotizante

                //Extranjero no obligado a cotizar a pensiones
                $arrayFila[6] = $this->plantillaTxt(" ",1,"","left");
                $arrayFila[7] = $this->plantillaTxt(" ",1," ","left");
            
                //Código del departamento de la ubicación laboral
                $arrayFila[8] = $this->plantillaTxt("0",2,"0","right");

                //Código del municipio de ubicación laboral
                $arrayFila[9] = $this->plantillaTxt("0",3,"0","right");
        

                $arrayFila[10] = $this->plantillaTxt($upcAdicional->primerApellido,20," ","left");
                $arrayFila[11] = $this->plantillaTxt($upcAdicional->segundoApellido,30," ","left");
                $arrayFila[12] = $this->plantillaTxt($upcAdicional->primerNombre,20," ","left");
                $arrayFila[13] = $this->plantillaTxt($upcAdicional->segundoNombre,30," ","left");

                $salud = DB::table("afiliacion","a")
                ->join("tercero as t", "t.idTercero", "=", "a.fkTercero")
                ->where("a.fkEmpleado", "=", $empleado->idempleado)
                ->where("a.fkTipoAfilicacion", "=", "3") // 3 - Tipo Afiliacion = Salud
                ->first();
                $arrayFila[14] = $this->plantillaTxt(" ",1,"","left");
                $arrayFila[15] = $this->plantillaTxt("",1," ","left");
                $arrayFila[16] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[17] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[18] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[19] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[20] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[21] = $this->plantillaTxt(" ",1," ","left");	
                $arrayFila[22] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[23] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[24] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[25] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[26] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[27] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[28] = $this->plantillaTxt(" ",1," ","left");
                $arrayFila[29] = $this->plantillaTxt("",2,"0","right");
                $arrayFila[30] = $this->plantillaTxt("",6," ","left");
                $arrayFila[31] = $this->plantillaTxt(" ",6," ","left");
                $arrayFila[32] = $this->plantillaTxt($salud->codigoTercero,6," ","left");
                $arrayFila[33] = $this->plantillaTxt(" ",6," ","left");
                $arrayFila[34] = $this->plantillaTxt(" ",6," ","left");
                $arrayFila[35] = $this->plantillaTxt("0",2,"0","right");
                $arrayFila[36] = $this->plantillaTxt("30",2,"0","right");
                $arrayFila[37] = $this->plantillaTxt("0",2,"0","right");
                $arrayFila[38] = $this->plantillaTxt("0",2,"0","right");
                $arrayFila[39] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[40] = $this->plantillaTxt("",1," ","left");
                $arrayFila[41] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[42] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[43] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[44] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[45] = $this->plantillaTxt("0.0",7,"0","left");
                $arrayFila[46] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[47] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[48] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[49] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[50] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[51] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[52] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[53] =$this->plantillaTxt("0.0",7,"0","left");   
                $arrayFila[54] =$this->plantillaTxt("0",9,"0","right");


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

                
                $tarifa = 0;
                foreach($tarifasUpc as $tarifaUpc){
                    if(!isset($tarifaUpc->fkGenero) || $tarifaUpc->fkGenero == $upcAdicional->fkGenero){
                        $tarifa = $tarifaUpc->valor;
                    }
                }
                $arrayFila[55] = $this->plantillaTxt($tarifa,9,"0","right");

                $arrayFila[56] =$this->plantillaTxt("",15," ","left");
                $arrayFila[57] =$this->plantillaTxt("",9,"0","left");
                $arrayFila[58] =$this->plantillaTxt("",15," ","left");
                $arrayFila[59] =$this->plantillaTxt("",9,"0","left");

                $arrayFila[60] = $this->plantillaTxt("0.0",9,"0","left");
                $arrayFila[61] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[62] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[63] = $this->plantillaTxt("0.0",7,"0","left");    
                $arrayFila[64] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[65] = $this->plantillaTxt("0.0",7,"0","left");  
                $arrayFila[66] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[67] = $this->plantillaTxt("0.0",7,"0","left");  
                $arrayFila[68] = $this->plantillaTxt("0",9,"0","right");

                $arrayFila[69] = $this->plantillaTxt("0.0",7,"0","left");
                $arrayFila[70] = $this->plantillaTxt("",9,"0","right");
                $arrayFila[71] = $this->plantillaTxt("0.0",7,"0","left");  
                $arrayFila[72] = $this->plantillaTxt("",9,"0","right");

                $arrayFila[73] = $this->plantillaTxt($empleado->siglaPila,2," ","right");
                $arrayFila[74] = $this->plantillaTxt($empleado->numeroIdentificacion,16," ","left");
                $arrayFila[75] = $this->plantillaTxt("N",1,"","left");
                $arrayFila[76] = $this->plantillaTxt("",6," ","left");
                $arrayFila[77] = $this->plantillaTxt(" ",1,"","left");
                $arrayFila[78] = $this->plantillaTxt("",1," ","left");


                $arrayFila[79] = $this->plantillaTxt("",10," ","left");
                $arrayFila[80] = $this->plantillaTxt("",10," ","left");
                $arrayFila[81] = $this->plantillaTxt("",10," ","left");
                $arrayFila[82] = $this->plantillaTxt("",10," ","left");
                $arrayFila[83] = $this->plantillaTxt("",10," ","left");
                $arrayFila[84] = $this->plantillaTxt("",10," ","left");
                $arrayFila[85] = $this->plantillaTxt("",10," ","left");
                $arrayFila[86] = $this->plantillaTxt("",10," ","left");
                $arrayFila[87] = $this->plantillaTxt("",10," ","left");
                $arrayFila[88] = $this->plantillaTxt("",10," ","left");
                $arrayFila[89] = $this->plantillaTxt("",10," ","left");
                $arrayFila[90] = $this->plantillaTxt("",10," ","left");
                $arrayFila[91] = $this->plantillaTxt("",10," ","left");
                $arrayFila[92] = $this->plantillaTxt("",10," ","left");
                $arrayFila[93] = $this->plantillaTxt("",10," ","left");
                
                $arrayFila[94] = $this->plantillaTxt("0",9,"0","right");
                $arrayFila[95] = $this->plantillaTxt("0",3,"0","right");
                $arrayFila[96] = $this->plantillaTxt("",10," ","right");
                $arrayFila = $this->upperCaseAllArray($arrayFila);
                array_push($arrayMuestra, $arrayFila);
                $contador++;
            }
            

            



        }

        //$arrayMuestra[0][18] = $this->plantillaTxt($contador,5,"0","right");//Número total de cotizantes
        $arrayMuestra[0][19] = $this->plantillaTxt(round($totalNomina),12,"0","right");//Valor total nomina
        $arrayMuestra[0][18] = $this->plantillaTxt($numeroEmpleados,5,"0","right");//Número total de cotizantes
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=Informe_ss_.txt');
        foreach($arrayMuestra[0] as $arr){
            echo $arr;
        }
        echo "\n"; 
        for($j=1; $j<sizeof($arrayMuestra); $j++){
            for($i=0; $i<=96; $i++){
                echo $arrayMuestra[$j][$i];                
            }
            echo "\n";
        }


    

    }
    public function seleccionarDocumentoProvisiones(){
        $empresas = DB::table("empresa", "e")
        ->get();

        return view('/reportes.seleccionarDocumentoProvisiones',[
            'empresas' => $empresas            
        ]);
    }

    public function documentoProv(Request $req){
        $nombreDoc = date("M",strtotime($req->fechaDocumento))."-". date("Y",strtotime($req->fechaDocumento));
        $mesFechaDocumento = date("m",strtotime($req->fechaDocumento));
        $anioFechaDocumento = date("Y",strtotime($req->fechaDocumento));

        $datosProv = DB::table('provision','p')
        ->select("dp.numeroIdentificacion","dp.primerNombre","dp.segundoNombre","dp.primerApellido","dp.segundoApellido","p.*")
        ->join("empleado as e", "e.idempleado", "=", "p.fkEmpleado","left")
        ->join("nomina as n", "n.idNomina", "=", "e.fkNomina","left")
        ->join("datospersonales as dp", "dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->where("p.anio","=",$anioFechaDocumento)
        ->where("p.mes","<=",$mesFechaDocumento)
        ->where("p.fkConcepto","=",$req->provision)
        ->where("n.fkEmpresa","=",$req->empresa)
        ->orderBy("p.fkEmpleado")
        ->get();

        $arrMeses = ["ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];


    

        $arrDatos=array();
        $arrTitulo = array(
            "Identificacion",
            "Primer Apellido",
            "Segundo Apellido",
            "Primer Nombre",
            "Segundo Nombre",
            "Saldo"
        );
        for($i=0; $i<$mesFechaDocumento; $i++){
            array_push($arrTitulo, "PROVISION ".$arrMeses[$i]);
            array_push($arrTitulo, "PAGO ".$arrMeses[$i]);
        }
        array_push($arrDatos, $arrTitulo);

        $arrInt = array();
        for($j = 0; $j <= 29; $j++){
            if(!isset($arrInt[$j])){
                $arrInt[$j] = " ";
            }
        }
        $idEmpleado = 0;
        $row = 0;
        /*foreach($datosProv as $dato){
            dd($dato);
        }*/
    




        foreach($datosProv as $datoProv){
            
            if($idEmpleado != $datoProv->fkEmpleado){
                
                if($idEmpleado != 0){
                
                    array_push($arrDatos, $arrInt);
                    $arrInt = array();
                    for($j = 0; $j <= 28; $j++){
                        if(!isset($arrInt[$j])){
                            $arrInt[$j] = " ";
                        }
                    }
                }
                $arrInt[0]= $datoProv->numeroIdentificacion;
                $arrInt[1]= $datoProv->primerApellido;
                $arrInt[2]= $datoProv->segundoApellido;
                $arrInt[3]= $datoProv->primerNombre;
                $arrInt[4]= $datoProv->segundoNombre;
            
                $idEmpleado = $datoProv->fkEmpleado;
                $saldo = DB::table("saldo","s")
                ->where("s.fkEmpleado","=",$datoProv->fkEmpleado)
                ->where("s.mesAnterior","=","12")
                ->where("s.anioAnterior","=",($anioFechaDocumento - 1))
                ->where("s.fkConcepto","=",$req->provision)
                ->first();
                if(isset($saldo)){
                    $arrInt[5]= $saldo->valor;
                }
                

            
            }
            
            if($datoProv->mes==1){
                $row = 5;
            }
            if($datoProv->mes==2){
                $row = 7;
            }
            if($datoProv->mes==3){
                $row = 9;
            }
            if($datoProv->mes==4){
                $row = 11;
            }
            if($datoProv->mes==5){
                $row = 13;
            }
            if($datoProv->mes==6){
                $row = 15;
            }
            if($datoProv->mes==7){
                $row = 17;
            }
            if($datoProv->mes==8){
                $row = 19;
            }
            if($datoProv->mes==9){
                $row = 21;
            }
            if($datoProv->mes==10){
                $row = 23;
            }
            if($datoProv->mes==11){
                $row = 25;
            }
            if($datoProv->mes==12){
                $row = 27;
            }
            $row++;
            

            /*$datosProvResta = DB::table('provision','p')
                ->selectRaw("sum(p.valor) as suma")
                ->where("p.anio","=",$datoProv->anio)
                ->where("p.mes","<",$datoProv->mes)
                ->where("p.fkEmpleado","=",$datoProv->fkEmpleado)
                ->where("p.fkConcepto","=",$datoProv->fkConcepto)
                ->first();
                
            if(isset($datosProvResta)){
                $arrInt[$row] = $datoProv->valor - $datosProvResta->suma;    
            }
            else{*/
                $arrInt[$row] = $datoProv->valor;    
            /*}*/

            
            
            $pago = 0;
            if($req->provision=="73"){
                $itemsBoucherPrima = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$datoProv->fkEmpleado)
                ->whereRaw("MONTH(ln.fechaFin) = '".$datoProv->mes."'")
                ->where("ibp.fkConcepto","=","58") //19 - PENSION
                ->first();
                if(isset($itemsBoucherPrima)){
                    $pago = $itemsBoucherPrima->suma;
                }
            }

            if($req->provision=="71"){
                $itemsBoucherCes = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$datoProv->fkEmpleado)
                ->whereRaw("MONTH(ln.fechaFin) = '".$datoProv->mes."'")
                ->where("ibp.fkConcepto","=","66") //19 - PENSION
                ->first();
                if(isset($itemsBoucherCes)){
                    $pago = $itemsBoucherCes->suma;
                }
            }
            if($req->provision=="72"){
                $itemsBoucherIntCes = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$datoProv->fkEmpleado)
                ->whereRaw("MONTH(ln.fechaFin) = '".$datoProv->mes."'")
                ->where("ibp.fkConcepto","=","69") //19 - PENSION
                ->first();
                if(isset($itemsBoucherIntCes)){
                    $pago = $itemsBoucherIntCes->suma;
                }
            }
            if($req->provision=="74"){

                $fechaInicioMesActual = date("Y-m-01",strtotime($datoProv->anio."-".$datoProv->mes."-01"));
                $fechaFinMesActual = date("Y-m-t",strtotime($datoProv->anio."-".$datoProv->mes."-01"));
                $pago = 0;
                $sqlWhere = "( 
                    ('".$fechaInicioMesActual."' BETWEEN v.fechaInicio AND v.fechaFin) OR
                    ('".$fechaFinMesActual."' BETWEEN v.fechaInicio AND v.fechaFin) OR
                    (v.fechaInicio BETWEEN '".$fechaInicioMesActual."' AND '".$fechaFinMesActual."') OR
                    (v.fechaFin BETWEEN '".$fechaInicioMesActual."' AND '".$fechaFinMesActual."')
                )";



                $itemsBoucherVac30 = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$datoProv->fkEmpleado)
                ->whereRaw("MONTH(ln.fechaFin) = '".$datoProv->mes."' and YEAR(ln.fechaLiquida) = '".$datoProv->anio."' ")
                ->where("ibp.fkConcepto","=","30") //30 - RETIRO
                ->first();

                if(isset($itemsBoucherVac30)){
                    $pago = $pago + $itemsBoucherVac30->suma;                    
                    
                }
                
                

                $itemsBoucherVac28 = DB::table("item_boucher_pago", "ibp")
                ->selectRaw("Sum(ibp.pago) as suma")
                ->join("boucherpago as bp","bp.idBoucherPago","=","ibp.fkBoucherPago")
                ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                ->where("bp.fkEmpleado","=",$datoProv->fkEmpleado)
                ->whereRaw("MONTH(ln.fechaFin) = '".$datoProv->mes."' and YEAR(ln.fechaLiquida) = '".$datoProv->anio."' ")
                ->where("ibp.fkConcepto","=","28") //28 - compensadas
                ->first();
                if(isset($itemsBoucherVac28)){
                    $pago = $pago + $itemsBoucherVac28->suma;
                }
                


                $novedadesVac = DB::table("novedad","n")
                ->join("vacaciones as v","v.idVacaciones","=", "n.fkVacaciones")
                ->where("n.fkEmpleado","=", $datoProv->fkEmpleado)
                ->whereIn("n.fkEstado",["8","16"]) // Pagada-> no que este eliminada o parcialmente paga (para las de pago parcial)
                ->whereNotNull("n.fkVacaciones")
                ->where("n.fkConcepto","=", "29")
                ->whereRaw($sqlWhere)
                ->get();
                $fechaFin = $fechaFinMesActual;
                foreach($novedadesVac as $novedadVac){
                    if(strtotime($novedadVac->fechaInicio)>=strtotime($fechaInicioMesActual)
                        &&  strtotime($novedadVac->fechaInicio)<=strtotime($fechaFin) 
                        &&  strtotime($novedadVac->fechaFin)>=strtotime($fechaFin))
                    {
                        $diaI = $novedadVac->fechaInicio;
                        $diaF = $fechaFin;
                        $diasCompensar = $this->days_360($novedadVac->fechaInicio, $fechaFin) + 1;
                        if(substr($novedadVac->fechaInicio, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;
                        }
                        $diasPagoVac = $diasCompensar;
                        if(substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                        
                    
                    }
                    else if(strtotime($novedadVac->fechaFin)>=strtotime($fechaInicioMesActual)  
                    &&  strtotime($novedadVac->fechaFin)<=strtotime($fechaFin) 
                    &&  strtotime($novedadVac->fechaInicio)<=strtotime($fechaInicioMesActual))
                    {
                        $diaI = $fechaInicioMesActual;
                        $diaF = $novedadVac->fechaFin;

                        $diasCompensar = $this->days_360($fechaInicioMesActual, $novedadVac->fechaFin) + 1;
                        if(substr($fechaInicioMesActual, 8, 2) == "31" && substr($novedadVac->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                        $diasPagoVac = $diasCompensar;
                        if(substr($novedadVac->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($novedadVac->fechaInicio)<=strtotime($fechaInicioMesActual)  
                    &&  strtotime($novedadVac->fechaFin)>=strtotime($fechaFin)) 
                    {
                        $diaI = $fechaInicioMesActual;
                        $diaF = $fechaFin;
                        $diasCompensar = $this->days_360($fechaInicioMesActual, $fechaFin) + 1;
                        if(substr($fechaInicioMesActual, 8, 2) == "31" && substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                        $diasPagoVac = $diasCompensar;
                        if(substr($fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                    }
                    else if(strtotime($fechaInicioMesActual)<=strtotime($novedadVac->fechaInicio)  
                    &&  strtotime($fechaFin)>=strtotime($novedadVac->fechaFin)) 
                    {
                        $diaI = $novedadVac->fechaInicio;
                        $diaF = $novedadVac->fechaFin;
                        $diasCompensar = $this->days_360($novedadVac->fechaInicio, $novedadVac->fechaFin) + 1;

                        if(substr($novedadVac->fechaInicio, 8, 2) == "31" && substr($novedadVac->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                        $diasPagoVac = $diasCompensar;
                        if(substr($novedadVac->fechaFin, 8, 2) == "31"){
                            $diasCompensar--;   
                        }
                        
                    }
                    $diasTotales = $novedadVac->diasCompensar;
                    $novedadVac->diasCompensar = intval( $diasCompensar);

                    $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                    ->join("item_boucher_pago as ibp","ibp.idItemBoucherPago", "=","ibpn.fkItemBoucher")
                    ->join("boucherpago as bp","bp.idBoucherPago", "=","ibp.fkBoucherPago")
                    ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
                    ->selectRaw("sum(ibpn.valor) as valor")
                    ->where("ibpn.fkNovedad", "=",$novedadVac->idNovedad)
                    ->whereBetween("ln.fechaLiquida",[$fechaInicioMesActual, $fechaFin])
                    ->first();       
                    if($novedadVac->pagoAnticipado == 1){
                        if(isset($itemBoucherNovedad) && $itemBoucherNovedad->valor > 0){
                            $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                            $valorNovedad = $diasPagoVac*$valorNovedad/$diasTotales;
                         
                        }
                        else{
                            $itemBoucherNovedad = DB::table("item_boucher_pago_novedad", "ibpn")
                            ->where("ibpn.fkNovedad", "=",$novedadVac->idNovedad)
                            ->first();
                
                            $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);
                            $valorNovedad = $diasPagoVac*$valorNovedad/$diasTotales;
                         
                        }
                    
                        
                    }
                    else{
                        $valorNovedad = ($itemBoucherNovedad->valor > 0 ? $itemBoucherNovedad->valor : $itemBoucherNovedad->valor*-1);    
                    }

                    $pago = $pago + $valorNovedad;
                }
                
                if($pago < 0){
                    $datosProvSuma = DB::table('provision','p')
                    ->selectRaw("sum(p.valor) as suma")
                    ->where("p.anio","=",$datoProv->anio)
                    ->where("p.mes","<=",$datoProv->mes)
                    ->where("p.fkEmpleado","=",$datoProv->fkEmpleado)
                    ->where("p.fkConcepto","=",$datoProv->fkConcepto)
                    ->first();
                    
                    if(isset($datosProvResta)){
                        $pago = $datosProvSuma->suma;    
                    }
                    
                }
            }
            $row = $row + 1;
            $arrInt[$row] = intval($pago);
        
        
        }
        
        if($idEmpleado != 0){
            array_push($arrDatos, $arrInt);
        }
        

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=Informe_Provision_'.$nombreDoc.'.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($arrDatos);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->output('Informe_Provision_'.$nombreDoc.'.csv');
    }

    public function plantillaTxt($valor, $longitud, $relleno, $alinear){
        $string = "";
        $inicio = 0;
        if($alinear == "left"){
            $string.=$valor;
            $inicio=mb_strlen($valor);
        }
        
        for ($i=$inicio; $i < $longitud; $i++) { 
            $string.=$relleno;
        }
        
        if($alinear == "right"){
            $string.=$valor;
            $string = substr($string, $longitud*-1);
        }

        if(mb_strlen($string) > $longitud){
            $string = substr($string, 0 ,$longitud);
        }
        return $string;
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
    


    public function basico($numero) {
        $valor = array ('uno','dos','tres','cuatro','cinco','seis','siete','ocho',
        'nueve','diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve',
        'veinte','veintiuno','veintidos','veintitres','veinticuatro','veinticinco',
        'veintiséis','veintisiete','veintiocho','veintinueve');
        return $valor[$numero - 1];
    }
        
    public function decenas($n) {
        $decenas = array (30=>'treinta',40=>'cuarenta',50=>'cincuenta',60=>'sesenta',
        70=>'setenta',80=>'ochenta',90=>'noventa');
        if( $n <= 29){
            return $this->basico($n);
        } 
        $x = $n % 10;
        if ( $x == 0 ) {
            return $decenas[$n];
        } else{
            return $decenas[$n - $x].' y '. $this->basico($x);  
        } 
    }
        
    public function centenas($n) {
        $cientos = array (100 =>'cien',200 =>'doscientos',300=>'trecientos',
        400=>'cuatrocientos', 500=>'quinientos',600=>'seiscientos',
        700=>'setecientos',800=>'ochocientos', 900 =>'novecientos');
        if( $n >= 100) {
            if ( $n % 100 == 0 ) {
                return $cientos[$n];
            } 
            else {
                $u = (int) substr($n,0,1);
                $d = (int) substr($n,1,2);
                return (($u == 1)?'ciento':$cientos[$u*100]).' '.$this->decenas($d);
            }
        } else return $this->decenas($n);
    }
        
    public function miles($n) {
        if($n > 999) {
            if( $n == 1000) {
                return 'mil';
            }
            else {
                $l = strlen($n);
                $c = (int)substr($n,0,$l-3);
                $x = (int)substr($n,-3);
                if($c == 1) {
                    $cadena = 'mil '.$this->centenas($x);
                }
                else if($x != 0) {
                    $cadena = $this->centenas($c).' mil '.$this->centenas($x);
                }
                else{
                    $cadena = $this->centenas($c). ' mil';
                }
                return $cadena;
            }
        } 
        else{
            return $this->centenas($n);
        }
    }
        
    public function millones($n) {
        if($n == 1000000) {
            return 'un millón';
        }
        else {
            $l = strlen($n);
            $c = (int)substr($n,0,$l-6);
            $x = (int)substr($n,-6);
            if($c == 1) {
                $cadena = ' millón ';
            } else {
                $cadena = ' millones ';
            }
            return $this->miles($c).$cadena.(($x > 0)?$this->miles($x):'');
        }
    }
    public function convertir($n) {
        switch (true) {
            case ($n <= 0):
                return "CERO";
                break;
            case ( $n >= 1 && $n <= 29):
                return $this->basico($n);
                break;
            case ( $n >= 30 && $n < 100):
                return $this->decenas($n); 
                break;
            case ( $n >= 100 && $n < 1000):
                return $this->centenas($n); 
                break;
            case ($n >= 1000 && $n <= 999999): 
                return $this->miles($n); 
                break;
            case ($n >= 1000000): 
                return $this->millones($n);
                break;
        }
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
    public function indexReporteVacaciones(){
        $empresas = DB::table("empresa", "e")->get();
        return view('/reportes.reporteVacaciones',[
            "empresas" => $empresas
        ]);
    }
    public function reporteVacaciones(Request $req){

        $dataUsu = Auth::user();

        $empresa = DB::table("empresa", "e")->where("e.idempresa","=",$req->empresa)->first();

        $empleados = DB::table("empleado","e")
        ->select("e.*","dp.*","ccfijo.valor as valorSalario")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("conceptofijo as ccfijo","ccfijo.fkEmpleado", "=", "e.idempleado")
        ->join("nomina as n","n.idNomina", "=","e.fkNomina")
        ->where("n.fkEmpresa","=",$req->empresa)
        ->where("e.fkEstado","=","1")
        ->whereIn("ccfijo.fkConcepto",["1","2"])
        ->get();
        $dompdf = new Dompdf();
        $dompdf->getOptions()->setChroot($this->rutaBaseImagenes);
        $dompdf->getOptions()->setIsPhpEnabled(true);

        ob_start();?>
            <!DOCTYPE html>
            <html>
                <head>
                    <meta charset='utf-8'>
                    <style>
                        /** 
                            Set the margins of the page to 0, so the footer and the header
                            can be of the full height and width !
                        **/
                        @page {
                            margin: 0cm 0cm;
                        }

                        /** Define now the real margins of every page in the PDF **/
                        body {
                            margin-top: 3.5cm;
                            margin-left: 1cm;
                            margin-right: 1cm;
                            margin-bottom: 1cm;
                            font-family: sans-serif;
                            font-size: 12px;
                        }

                        /** Define the header rules **/
                        header {
                            position: fixed;
                            top: 0.5cm;
                            left: 0cm;
                            right: 0cm;
                            height: 3cm;
                        }
                        .logoEmpresa{
                            max-width: 3cm;
                            max-height: 3cm;
                        }
                        .tablaHeader th, .tablaHeader td{
                            text-align: left;
                            
                            padding-left: 20px;
                            
                        }
                        .tablaDatos{
                            border-collapse: collapse;
                            width: 100%
                        }
                        .tablaDatos td{
                            text-align: right;
                            font-size: 9px;
                        }
                        .tablaDatos th{
                            font-size: 9px;
                        }
                        .tablaDatos td.left{
                            text-align: left;
                        }
                        .tablaDatos td.arriba *, .tablaDatos td.arriba{
                            vertical-align: top;
                            padding: 0 5px;
                        }
                        .azul1{
                            background: #afeeee;
                        }
                        .azul2{
                            background: #add8e6;
                        }
                        
                    </style>
                    <title>Reporte vacaciones</title>
                </head>
                <body>
                    <header>
                        <table class="tablaHeader">
                            <tr>
                                <td rowspan="4" width="5cm" height="2cm">
                                    <img src="<?php if(isset($empresa->logoEmpresa)){ echo $this->rutaBaseImagenes; ?>storage/logosEmpresas/<?php echo $empresa->logoEmpresa; } ?>" class="logoEmpresa" />
                                    
                                </td>
                                <th width="8.1cm">LIBRO DE VACACIONES</th>
                                <th width="2cm">Fecha:</th>
                                <td width="4cm"><?php echo date("Y-m-d H:i:s"); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo $empresa->razonSocial; ?></th>
                                <th>Usuario:</th>
                                <td><?php echo $dataUsu->username; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo number_format($empresa->documento,0)." - ".$empresa->digitoVerificacion; ?></th>
                                <th>Reporte:</th>
                                <td>NOM_U_713</td>
                            </tr>
                            <tr>
                                <th>FECHA DE CORTE: <?php echo date("t/m/Y",strtotime($req->fechaFin)); ?></th>
                                <th>Página:</th>
                                <td>
                                    <script type="text/php">
                                    $text = '{PAGE_NUM} / {PAGE_COUNT}';
                                    $font = $fontMetrics->getFont("helvetica", "bold");
                                    $pdf->page_text(500, 70, $text, $font, 9);
                                    echo $text;
                                    </script>
                                </td>
                            </tr>
                        </table>
                    </header>
                    <main>
                        <table class="tablaDatos" border="1">
                            <tr>
                                <th rowspan="2">ID</th>
                                <th rowspan="2"width="180">NOMBRE</th>
                                <th rowspan="2" width="45">F ING</th>
                                <th rowspan="2">SUELDO</th>
                                <th rowspan="2">D TRA</th>
                                <th rowspan="2">D LNR</th>
                                <th rowspan="2">D NET</th>
                                <th rowspan="2">D VAC</th>
                                <th rowspan="2">D TOM</th>
                                <th rowspan="2" width="30">D PEN</th>
                                <th class="azul1" colspan="6">CAUSACIÓN</th>
                                <th class="azul2" colspan="3">DISFRUTE</th>
                            </tr>
                            <tr>
                                <th class="azul1">PER</th>
                                <th class="azul1" width="40">P INI</th>
                                <th class="azul1" width="40">P FIN</th>
                                <th class="azul1">D CAU</th>
                                <th class="azul1">D TOM</th>
                                <th class="azul1" width="25">D PEN</th>
                                <th class="azul2" width="40">INI</th>
                                <th class="azul2" width="40">FIN</th>
                                <th class="azul2">DÍAS</th>
                            </tr>

                        <?php 
                            foreach($empleados as $empleado){
                               
                                    
                                $fechaInicio = $empleado->fechaIngreso;
                                $fechaFinGen = date("Y-m-t",strtotime($req->fechaFin));
                                
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
                                ->selectRaw("sum(v.diasCompensar) as suma")
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
                                        $arrFilaInt['diaTom'] = $novedadVacacion->diasCompensar;
                                        array_push($arrFila['disfrute'], $arrFilaInt);
                                        $diasTomadosPeriodo = $diasTomadosPeriodo + $novedadVacacion->diasCompensar;    
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

                                
                                echo '<tr>
                                <td class="arriba" rowspan="'.$rowspan.'">'.$empleado->numeroIdentificacion.'</td>
                                <td class="arriba left"rowspan="'.$rowspan.'">'.$empleado->primerApellido." ".$empleado->segundoApellido." ".$empleado->primerNombre." ".$empleado->segundoNombre.'</td>
                                <td class="arriba" rowspan="'.$rowspan.'">'.$empleado->fechaIngreso.'</td>
                                <td class="arriba" rowspan="'.$rowspan.'">'.number_format($empleado->valorSalario,0,",", ".").'</td>';                            
                                echo '<td class="arriba" rowspan="'.$rowspan.'">'.$diasTrabajados.'</td>';
                                echo '<td class="arriba" rowspan="'.$rowspan.'">'.(isset($novedadesLIC->suma) ? $novedadesLIC->suma : 0).'</td>';
                                echo '<td class="arriba" rowspan="'.$rowspan.'">'.$diasNeto.'</td>';
                                echo '<td class="arriba" rowspan="'.$rowspan.'">'.round($diasVacGen,2).'</td>';
                                echo '<td class="arriba" rowspan="'.$rowspan.'">'.(isset($novedadesVacacionGen->suma) ? $novedadesVacacionGen->suma : 0).'</td>';
                                echo '<td class="arriba" rowspan="'.$rowspan.'">'.round($diasVacGen - (isset($novedadesVacacionGen->suma) ? $novedadesVacacionGen->suma : 0), 2).'</td>';
                                $aplico = 0;
                                foreach($arrDatos as $datoCau){
                                    $rowspanInt = 1;
                                    
                                    if(sizeof($datoCau['disfrute'])>0){
                                        $rowspanInt =  $rowspanInt + sizeof($datoCau['disfrute']) - 1;
                                    }
                                    if($aplico == 1){
                                        echo '<tr>';
                                    }
                                    echo '<td rowspan="'.$rowspanInt.'" class="azul1">'.$datoCau['periodo'].'</td>';
                                    echo '<td rowspan="'.$rowspanInt.'" class="azul1">'.$datoCau['fechaInicio'].'</td>';
                                    echo '<td rowspan="'.$rowspanInt.'" class="azul1">'.$datoCau['fechaFinInt'].'</td>';
                                    echo '<td rowspan="'.$rowspanInt.'" class="azul1">'.round($datoCau['diaCau'],2).'</td>';
                                    echo '<td rowspan="'.$rowspanInt.'" class="azul1">'.$datoCau['diaTom'].'</td>';
                                    echo '<td rowspan="'.$rowspanInt.'" class="azul1">'.round($datoCau['diaPen'],2).'</td>';
                                    
                                    if(sizeof($datoCau['disfrute'])>0){
                                        $aplico2 = 0;
                                        foreach($datoCau['disfrute'] as $disf){
                                            if($aplico2 == 1){
                                                echo '<tr>';
                                            }

                                            echo '<td class="azul2">'.$disf['diaIni'].'</td>';
                                            echo '<td class="azul2">'.$disf['diaFin'].'</td>';
                                            echo '<td class="azul2">'.$disf['diaTom'].'</td>';
                                            echo "</tr>";
                                            $aplico2 = 1;
                                        }
                                    }
                                    else{
                                        echo '<td class="azul2"></td>';
                                        echo '<td class="azul2"></td>';
                                        echo '<td class="azul2">0</td>';
                                        echo "</tr>";
                                    }
                                    $aplico = 1;
                                }


                                
                                
                            }
                        ?>
                        </table>
                    </main>
                </body>
            </html>
        <?php
        $html = ob_get_clean();
        
        $dompdf->loadHtml($html ,'UTF-8');
    
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('letter', 'landscape');
        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("Reporte vacaciones.pdf", array('compress' => 1, 'Attachment' => 1));


        
    }


    public function formulario220Dian(Request $req){


   
        $idempleado = $req->idempleado;
        $empleado = DB::table("empleado")->where("idempleado","=", $idempleado)->first();
        $empresa = DB::table("empresa","e")->where("idempresa","=", $empleado->fkEmpresa)->first();
        









    }

}
