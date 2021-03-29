<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function buscar($busqueda=null){
        $usu = UsuarioController::dataAdminLogueado();
        if(strlen($busqueda)>=3){
            $itemsMenu = DB::table("menu","m")
            ->join("permisos_user as pu","pu.fkMenu","=","m.idMenu")
            ->where("pu.fkUser","=",$usu->id)
            ->where("m.nombre", "LIKE","%".$busqueda."%")
            ->get();
            if($usu->fkRol == 3){
                $itemsMenu = DB::table("menu","m")
                ->where("m.nombre", "LIKE","%".$busqueda."%")
                ->get();
            }

            return view('/menu.listaBusqueda', [
                "itemsMenu" => $itemsMenu
            ]);
        }
        
        
    }
}
