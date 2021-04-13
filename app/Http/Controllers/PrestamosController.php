<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrestamosController extends Controller
{
    public function index(Request $req){
        $usu = UsuarioController::dataAdminLogueado();
        $prestamos = DB::table("prestamo","p")
        ->select("p.*","c.nombre as nombreConcepto", "est.nombre as nombreEstado","dp.numeroIdentificacion", "dp.primerApellido","dp.primerNombre", "dp.segundoNombre", "dp.segundoApellido")
        ->join("empleado as e","e.idempleado", "=","p.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("concepto as c","c.idconcepto", "=", "p.fkConcepto") 
        ->join("estado as est", "est.idEstado", "=", "p.fkEstado")
        ;
        $arrConsulta = array();
        if(isset($req->estado)){
            $arrConsulta["estado"]=$req->estado;
            $prestamos = $prestamos->where("p.fkEstado","=",$req->estado);
        }
        else{
            $prestamos = $prestamos->where("p.fkEstado","=","1");
        }
        
        if(isset($req->numDoc)){
            $arrConsulta["numDoc"]=$req->numDoc;
            $prestamos = $prestamos->where("dp.numeroIdentificacion","LIKE","%".$req->numDoc."%");
        }
        if(isset($req->nombre)){
            $arrConsulta["nombre"]=$req->nombre;
            $prestamos = $prestamos->where(function($query) use($req){
                $query->where("dp.primerNombre","LIKE","%".$req->nombre."%")
                ->orWhere("dp.segundoNombre","LIKE","%".$req->nombre."%")
                ->orWhere("dp.primerApellido","LIKE","%".$req->nombre."%")
                ->orWhere("dp.segundoApellido","LIKE","%".$req->nombre."%")
                ->orWhereRaw("CONCAT(dp.primerApellido,' ',dp.segundoApellido,' ',dp.primerNombre,' ',dp.segundoNombre) LIKE '%".$req->nombre."%'");
            });
        }

        if(isset($usu) && $usu->fkRol == 2){            
            $prestamos = $prestamos->whereIn("e.fkEmpresa", $usu->empresaUsuario);
        }
        $prestamos = $prestamos->orderBy("dp.primerApellido")->paginate(15);

        
        

        return view('/prestamos.index', [
            "prestamos" => $prestamos,
            "arrConsulta" => $arrConsulta,
            "dataUsu" => $usu,
            "req" => $req
        ]);
        
    }

    public function getFormAdd(){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $empresas = DB::table("empresa", "e");
        if(isset($dataUsu) && $dataUsu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $dataUsu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();

        $gruposConcepto  = DB::table("grupoconcepto")->orderBy("nombre")->get();
        $conceptos = DB::table("concepto","c")
        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","c.idconcepto")
        ->where("gcc.fkGrupoConcepto","=","41")
        ->orderBy("nombre")->get();
        

        return view('/prestamos.add', [
            "empresas" => $empresas,
            "gruposConcepto" => $gruposConcepto,
            "conceptos" => $conceptos,
            "dataUsu" => $dataUsu
        ]);
        
    }

    public function getFormEdit($idPrestamo){
        $usu = UsuarioController::dataAdminLogueado();
        $empresas = DB::table("empresa", "e");
        if(isset($usu) && $usu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $usu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();
        
        
        $gruposConcepto  = DB::table("grupoconcepto")->orderBy("nombre")->get();
        
        
        $prestamo = DB::table("prestamo","p")
        ->select("p.*","e.fkEmpresa", "e.fkNomina", "dp.primerApellido", "dp.segundoApellido", "dp.primerNombre", "dp.segundoNombre")
        ->join("empleado as e","e.idempleado", "=","p.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->where("p.idPrestamo","=", $idPrestamo)
        ->first();
        
        $nomina = DB::table("nomina")->where("idNomina","=",$prestamo->fkNomina)->first();
        
        $periocidad = DB::table("periocidad")->where("per_periodo","=",$nomina->periodo)->get();

        $nominas = DB::table("nomina")->where("fkEmpresa","=",$prestamo->fkEmpresa)->orderBy("nombre")->get();


        $embargo = DB::table("embargo")->where("fkPrestamo", "=", $idPrestamo)->first();
        if(isset($embargo)){
            $conceptos = DB::table("concepto","c")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","c.idconcepto")
            ->where("gcc.fkGrupoConcepto","=","42")
            ->orderBy("nombre")->get();
            
            $deptos = DB::table("ubicacion")
            ->where("fkTipoUbicacion","=","2")
            ->where("fkUbicacion","=","57")
            ->orderBy("nombre")->get();

            $ciudad = DB::table("ubicacion")->where("idubicacion","=",$embargo->fkUbicacion)->first();

            $ciudades = DB::table("ubicacion")
            ->where("fkTipoUbicacion","=","3")
            ->where("fkUbicacion","=",$ciudad->fkUbicacion)
            ->orderBy("nombre")->get();
            $deptoSelect = $ciudad->fkUbicacion; 
            

            $tercerosJuzgado = DB::table("tercero")->where("fk_actividad_economica","=","9")->get();
            $tercerosDemandante = DB::table("tercero")->where("fk_actividad_economica","=","7")->get();



            return view('/prestamos.editEmbargo', [
                'embargo' => $embargo,
                "empresas" => $empresas,
                "nominas" => $nominas,
                "gruposConcepto" => $gruposConcepto,
                "conceptos" => $conceptos,
                "dataUsu" => $usu,
                "deptos" => $deptos,
                "deptoSelect" => $deptoSelect,
                "ciudades" => $ciudades,
                "tercerosJuzgado" => $tercerosJuzgado,
                "tercerosDemandante" => $tercerosDemandante,
                "prestamo" => $prestamo,
                "periocidad" => $periocidad
            ]);
        }else{
            
            $conceptos = DB::table("concepto","c")
            ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","c.idconcepto")
            ->where("gcc.fkGrupoConcepto","=","41")
            ->orderBy("nombre")->get();
            return view('/prestamos.edit', [
                "empresas" => $empresas,
                "nominas" => $nominas,
                "gruposConcepto" => $gruposConcepto,
                "conceptos" => $conceptos,
                "dataUsu" => $usu,
                "prestamo" => $prestamo,
                "periocidad" => $periocidad
            ]);
        }
        
    }


    public function getFormAddEmbargo(){
        $usu = UsuarioController::dataAdminLogueado();
        $empresas = DB::table("empresa", "e");
        if(isset($usu) && $usu->fkRol == 2){            
            $empresas = $empresas->whereIn("idempresa", $usu->empresaUsuario);
        }
        $empresas = $empresas->orderBy("razonSocial")->get();

        $gruposConcepto  = DB::table("grupoconcepto")->orderBy("nombre")->get();
        $conceptos = DB::table("concepto","c")
        ->join("grupoconcepto_concepto as gcc","gcc.fkConcepto","=","c.idconcepto")
        ->where("gcc.fkGrupoConcepto","=","42")
        ->orderBy("nombre")->get();

        $deptos = DB::table("ubicacion")
        ->where("fkTipoUbicacion","=","2")
        ->where("fkUbicacion","=","57")
        ->orderBy("nombre")->get();


        $tercerosJuzgado = DB::table("tercero")->where("fk_actividad_economica","=","9")->get();
        $tercerosDemandante = DB::table("tercero")->where("fk_actividad_economica","=","7")->get();
        
        $usu = UsuarioController::dataAdminLogueado();

        return view('/prestamos.addEmbargo', [
            "empresas" => $empresas,
            "gruposConcepto" => $gruposConcepto,
            "conceptos" => $conceptos,
            "dataUsu" => $usu,
            "deptos" => $deptos,
            "tercerosJuzgado" => $tercerosJuzgado,
            "tercerosDemandante" => $tercerosDemandante
        ]);
        
    }
    
    public function periocidadxNomina($idNomina){

        $nomina = DB::table("nomina")->where("idNomina","=",$idNomina)->first();
        $periocidad = DB::table("periocidad")->where("per_periodo","=",$nomina->periodo)->get();
        $html = '<option value=""></option>';
        foreach($periocidad as $periocid){
            $html.="<option value='".$periocid->per_id."'>".$periocid->per_nombre."</option>";
        }
        return response()->json([
            "success" => true,
            "opcionesPeriocidad" => $html
        ]);
        
    }
    public function crear(Request $req){

        if($req->saldoActual == "0"){
            $req->saldoActual = $req->montoInicial;
        }
        DB::table("prestamo")->insert([
            "codPrestamo" => $req->codPrestamo, 
            "motivoPrestamo" => $req->motivoPrestamo,
            "fkEmpleado" => $req->idEmpleado, 
            "montoInicial" => $req->montoInicial, 
            "saldoActual" => $req->saldoActual, 
            "fkPeriocidad" => $req->periocidad, 
            "tipoDescuento" => $req->tipoDesc, 
            "numCuotas" => $req->cuotas, 
            "valorCuota" => $req->valorFijo,
            "porcentajeCuota" => $req->presPorcentaje,
            "fechaInicio" => $req->fechaInicio, 
            "fechaDesembolso" => $req->fechaDesembolso, 
            "fkGrupoConcepto" => $req->grupoConceptoPorcentaje, 
            "fkConcepto" => $req->claseCuota, 
            "pignoracion" => $req->pignoracion, 
            "hastaSalarioMinimo" => $req->hastaSalarioMinimo, 
            "fkEstado" => "1"
        ]);

        return response()->json([
            "success" => true,
            "mensaje" => "Prestamo registrado correctamente",
            "url" => '/prestamos/'
        ]);
            
    }
    
    
    public function modificar(Request $req){
        if($req->saldoActual == "0"){
            $req->saldoActual = $req->montoInicial;
        }

        DB::table("prestamo")
        ->where("idPrestamo","=", $req->idPrestamo)
        ->update([
            "codPrestamo" => $req->codPrestamo, 
            "motivoPrestamo" => $req->motivoPrestamo,
            "fkEmpleado" => $req->idEmpleado, 
            "montoInicial" => $req->montoInicial, 
            "saldoActual" => $req->saldoActual, 
            "fkPeriocidad" => $req->periocidad, 
            "tipoDescuento" => $req->tipoDesc, 
            "numCuotas" => $req->cuotas, 
            "valorCuota" => $req->valorFijo,
            "porcentajeCuota" => $req->presPorcentaje,
            "fechaInicio" => $req->fechaInicio, 
            "fechaDesembolso" => $req->fechaDesembolso, 
            "fkGrupoConcepto" => $req->grupoConceptoPorcentaje, 
            "fkConcepto" => $req->claseCuota, 
            "pignoracion" => $req->pignoracion, 
            "hastaSalarioMinimo" => $req->hastaSalarioMinimo, 
            "fkEstado" => "1"
        ]);

        return response()->json([
            "success" => true,
            "mensaje" => "Prestamo modificado correctamente",
            "url" => '/prestamos/'
        ]);
            
    }

    public function crearEmbargo(Request $req){

        if($req->saldoActual == "0"){
            $req->saldoActual = $req->montoInicial;
        }
        $idPrestamo = DB::table("prestamo")->insertGetId([
            "fkEmpleado" => $req->idEmpleado, 
            "montoInicial" => $req->montoInicial, 
            "saldoActual" => $req->saldoActual, 
            "fkPeriocidad" => $req->periocidad, 
            "tipoDescuento" => $req->tipoDesc, 
            "valorCuota" => $req->valorFijo,
            "porcentajeCuota" => $req->presPorcentaje,
            "fechaInicio" => $req->fechaInicio, 
            "fkGrupoConcepto" => $req->grupoConceptoPorcentaje, 
            "fkConcepto" => $req->claseCuota, 
            "pignoracion" => $req->pignoracion, 
            "hastaSalarioMinimo" => $req->hastaSalarioMinimo, 
            "fkEstado" => "1"
        ], "idPrestamo");


        DB::table("embargo")->insert([
            "fkPrestamo" => $idPrestamo,
            "numeroEmbargo" => $req->numeroEmbargo, 
            "numeroOficio" => $req->numeroOficio, 
            "numeroProceso" => $req->numeroProceso, 
            "fkUbicacion" => $req->ciudad, 
            "fkTerceroJuzgado" => $req->fkTerceroJuzgado, 
            "fechaCargaOficio" => $req->fechaCargaOficio, 
            "fechaRecepcionCarta" => $req->fechaRecepcionCarta,
            "fkTerceroDemandante" => $req->fkTerceroDemandante, 
            "numeroCuentaJudicial" => $req->numeroCuentaJudicial, 
            "numeroCuentaDemandante" => $req->numeroCuentaDemandante, 
            "valorTotalEmbargo" => $req->valorTotalEmbargo
        ]);

        return response()->json([
            "success" => true,
            "mensaje" => "Embargo registrado correctamente",
            "url" => '/prestamos/'
        ]);
            
    }

    public function modificarEmbargo(Request $req){
        DB::table("prestamo")
        ->where("idPrestamo","=", $req->idPrestamo)
        ->update([
            "fkEmpleado" => $req->idEmpleado, 
            "montoInicial" => $req->montoInicial, 
            "saldoActual" => $req->saldoActual, 
            "fkPeriocidad" => $req->periocidad, 
            "tipoDescuento" => $req->tipoDesc, 
            "valorCuota" => $req->valorFijo,
            "porcentajeCuota" => $req->presPorcentaje,
            "fechaInicio" => $req->fechaInicio, 
            "fkGrupoConcepto" => $req->grupoConceptoPorcentaje, 
            "fkConcepto" => $req->claseCuota, 
            "pignoracion" => $req->pignoracion, 
            "hastaSalarioMinimo" => $req->hastaSalarioMinimo, 
            "fkEstado" => "1"
        ]);

        DB::table("embargo")
        ->where("idEmbargo","=", $req->idEmbargo)
        ->update([
            "fkPrestamo" => $req->idPrestamo,
            "numeroEmbargo" => $req->numeroEmbargo, 
            "numeroOficio" => $req->numeroOficio, 
            "numeroProceso" => $req->numeroProceso, 
            "fkUbicacion" => $req->ciudad, 
            "fkTerceroJuzgado" => $req->fkTerceroJuzgado, 
            "fechaCargaOficio" => $req->fechaCargaOficio, 
            "fechaRecepcionCarta" => $req->fechaRecepcionCarta,
            "fkTerceroDemandante" => $req->fkTerceroDemandante, 
            "numeroCuentaJudicial" => $req->numeroCuentaJudicial, 
            "numeroCuentaDemandante" => $req->numeroCuentaDemandante, 
            "valorTotalEmbargo" => $req->valorTotalEmbargo
        ]);

        return response()->json([
            "success" => true,
            "mensaje" => "Embargo modificado correctamente",
            "url" => '/prestamos/'
        ]);
    }
    
    public function eliminar($idPrestamo){

        DB::table("prestamo")->where("idPrestamo","=", $idPrestamo)->update(["fkEstado" => "9"]);

        return response()->json([
            "success" => true,
            "mensaje" => "Prestamo eliminado correctamente",
            "url" => '/prestamos/'
        ]);
    }
}
