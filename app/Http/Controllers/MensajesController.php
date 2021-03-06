<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MensajesController extends Controller
{
    public function index(){
        $mensajes = DB::table("mensaje")->whereNull("fkEmpresa")->get();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/mensajes.index', [
            "mensajes" => $mensajes,
            "dataUsu" => $usu
        ]);
    }

    public function mensajesxEmpresa($fkEmpresa){
        $mensajes = DB::table("mensaje")->where("fkEmpresa","=",$fkEmpresa)->get();
        $tipos = array();
        foreach ($mensajes as $mensaje){
            array_push($tipos, $mensaje->tipo);
        }
        
        $mensajesDefault = DB::table("mensaje")
        ->whereNull("fkEmpresa")
        ->whereNotIn("tipo",$tipos)
        ->get();

        $usu = UsuarioController::dataAdminLogueado();
        return view('/mensajes.indexMensajeEmpresa', [
            "mensajes" => $mensajes,
            "mensajesDefault" => $mensajesDefault,
            "dataUsu" => $usu,
            "fkEmpresa" => $fkEmpresa
        ]);
    }
    public function getFormEditxEmpresa($idMensaje, $idEmpresa){
        $mensaje = DB::table("mensaje")->where("idMensaje","=",$idMensaje)->first();
        $mensajeEmp = DB::table("mensaje")
        ->where("tipo","=",$mensaje->tipo)
        ->where("fkEmpresa","=", $idEmpresa)
        ->first();

        if(!isset($mensajeEmp)){
            $idMensaje = DB::table("mensaje")->insertGetId([
                "nombre" => $mensaje->nombre,
                "asunto" => $mensaje->asunto,
                "html" => $mensaje->html,
                "fkEmpresa" => $idEmpresa,
                "tipo" => $mensaje->tipo
            ],"idMensaje");

        }
        $mensaje = DB::table("mensaje")->where("idMensaje","=",$idMensaje)->first();
        $usu = UsuarioController::dataAdminLogueado();
        $adminController = new AdminCorreosController();
        $arrayCampos = $adminController->arrayCampos;
        return view('/mensajes.edit', [
            "mensaje" => $mensaje,
            "dataUsu" => $usu,
            "arrayCampos" => $arrayCampos          
        ]);

    }
    public function getFormEdit($idMensaje){
        $mensaje = DB::table("mensaje")->where("idMensaje","=", $idMensaje)->first();
        $usu = UsuarioController::dataAdminLogueado();
        $adminController = new AdminCorreosController();
        $arrayCampos = $adminController->arrayCampos;
        

        return view('/mensajes.edit', [
            "mensaje" => $mensaje,
            "dataUsu" => $usu,
            "arrayCampos" => $arrayCampos          
        ]);
    }

    public function modificar(Request $req){
        
        DB::table("mensaje")->where("idMensaje","=",$req->idMensaje)->update([
            "html" => $req->html,
            "asunto" => $req->asunto
        ]);

        $adminController = new AdminCorreosController();
        $arrayCampos = $adminController->arrayCampos;
        
        
        $mensaje = DB::table("mensaje")->where("idMensaje","=", $req->idMensaje)->first();
        $usu = UsuarioController::dataAdminLogueado();
        return redirect(action("MensajesController@getFormEdit",[$req->idMensaje]));
    }
}
