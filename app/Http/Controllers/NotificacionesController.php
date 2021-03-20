<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificacionesController extends Controller
{
    public function index(Request $req){
        $notificaciones = DB::table("notificacion","n")
        ->join("empleado as e","e.idempleado", "=","n.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales");
        
        $filtroVisto = true;
        if(isset($req->numDoc)){
            $filtroVisto = false;
            $notificaciones = $notificaciones->where("dp.numeroIdentificacion","LIKE","%".$req->numDoc."%");
        }
        if(isset($req->nombre)){
            $filtroVisto = false;
            $notificaciones = $notificaciones->where(function($query) use($req){
                $query->where("dp.primerNombre","LIKE","%".$req->nombre."%")
                ->orWhere("dp.segundoNombre","LIKE","%".$req->nombre."%")
                ->orWhere("dp.primerApellido","LIKE","%".$req->nombre."%")
                ->orWhere("dp.segundoApellido","LIKE","%".$req->nombre."%")
                ->orWhereRaw("CONCAT(dp.primerApellido,' ',dp.segundoApellido,' ',dp.primerNombre,' ',dp.segundoNombre) LIKE '%".$req->nombre."%'");
            });
        }
        if(isset($req->fechaInicio)){
            $filtroVisto = false;
            $notificaciones = $notificaciones->where("n.fecha",">=",$req->fechaInicio);
        }
        if(isset($req->fechaFin)){
            $filtroVisto = false;
            $notificaciones = $notificaciones->where("n.fecha","<=",$req->fechaFin);
        }
        
        if($filtroVisto){
            $notificaciones = $notificaciones->where("n.visto","=","0");
        }
        
        $notificaciones = $notificaciones->orderBy("n.fecha","desc")->paginate();
        $dataUsu = UsuarioController::dataAdminLogueado();
        return view('notificacion.index', [
            "notificaciones" => $notificaciones,
            "req" => $req,
            "dataUsu" => $dataUsu
        ]);
    }
    public function numeroNotificaciones(){
        $numNoVistos = DB::table("notificacion")
        ->selectRaw("count(visto) as suma")
        ->where("visto","=","0")
        ->first();
        
        return response()->json([
            "success" => true,
            "numNoVistos" => ($numNoVistos->suma ?? 0)
        ]);
    }
    
    public function modificarVisto(){
        DB::table("notificacion")->where("visto","=","0")->update([
            "visto" => 1
        ]);
        return redirect("/notificaciones");
    }

    public function verificarContratos(){

        $fecha = date("Y-m-d");
        //$fecha = "2021-04-17";

        $contratosFijosVencenHoy = DB::table("contrato","con")
        ->join("empleado as e","e.idempleado", "=","con.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->where("con.fechaFin","=",$fecha)
        ->get();

        $contratos20PorFijosVencen = DB::table("contrato","con")
        ->join("empleado as e","e.idempleado", "=","con.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->where("con.fechaFin","=",$fecha)
        ->where("con.fkTipoContrato","=","1")
        ->where("con.fechaFin",">",$fecha)
        ->whereRaw('CAST((DATEDIFF("'.$fecha.'", con.fechaInicio)*100)/DATEDIFF(con.fechaFin, con.fechaInicio) as integer)=20')
        ->get();

        $contratosFijos30DiasAntes = DB::table("contrato","con")
        ->join("empleado as e","e.idempleado", "=","con.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->where("con.fechaFin","=",$fecha)
        ->where("con.fkTipoContrato","=","1")
        ->where("con.fechaFin",">",$fecha)
        ->whereRaw('DATEDIFF(con.fechaFin,"'.$fecha.'") = 30')
        ->get();

        foreach($contratosFijosVencenHoy as $contratoFijosVencenHoy){
            DB::table("notificacion")->insert([
                "mensaje" => "El contrato vence hoy para ".$contratoFijosVencenHoy->numeroIdentificacion.": ".$contratoFijosVencenHoy->primerApellido." ".$contratoFijosVencenHoy->primerNombre,
                "fkEmpleado" => $contratoFijosVencenHoy->idempleado
            ]);
        }
        foreach($contratos20PorFijosVencen as $contratoFijosVencenHoy){
            DB::table("notificacion")->insert([
                "mensaje" => "El contrato ha completado el 20% para ".$contratoFijosVencenHoy->numeroIdentificacion.": ".$contratoFijosVencenHoy->primerApellido." ".$contratoFijosVencenHoy->primerNombre,
                "fkEmpleado" => $contratoFijosVencenHoy->idempleado
            ]);
        }
        foreach($contratosFijos30DiasAntes as $contratoFijosVencenHoy){
            DB::table("notificacion")->insert([
                "mensaje" => "El contrato finalizaré en 30 días para ".$contratoFijosVencenHoy->numeroIdentificacion.": ".$contratoFijosVencenHoy->primerApellido." ".$contratoFijosVencenHoy->primerNombre,
                "fkEmpleado" => $contratoFijosVencenHoy->idempleado
            ]);
        }

        return response()->json([
            "success" => true,
            "contratosFijosVencenHoy" => $contratosFijosVencenHoy,
            "contratos20PorFijosVencen" => $contratos20PorFijosVencen,
            "contratosFijos30DiasAntes" => $contratosFijos30DiasAntes,
        ]);
        
    
    }

}
