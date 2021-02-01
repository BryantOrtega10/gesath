<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MensajesController extends Controller
{
    public function index(){
        $mensajes = DB::table("mensaje")->get();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/mensajes.index', [
            "mensajes" => $mensajes,
            "dataUsu" => $usu
        ]);
    }

    public function getFormEdit($idMensaje){
        $mensaje = DB::table("mensaje")->where("idMensaje","=", $idMensaje)->first();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/mensajes.edit', [
            "mensaje" => $mensaje,
            "dataUsu" => $usu
        ]);
    }

    public function modificar(Request $req){
        
        DB::table("mensaje")->where("idMensaje","=",$req->idMensaje)->update([
            "html" => $req->html
        ]);

        $mensaje = DB::table("mensaje")->where("idMensaje","=", $req->idMensaje)->first();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/mensajes.edit', [
            "mensaje" => $mensaje,
            "dataUsu" => $usu,
            "modificacion" => "1"
        ]);
    }
}
