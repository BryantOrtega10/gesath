<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function buscar($busqueda=null){
        if(strlen($busqueda)>=3){
            $itemsMenu = DB::table("menu")
            ->where("nombre", "LIKE","%".$busqueda."%")
            ->get();
            return view('/menu.listaBusqueda', [
                "itemsMenu" => $itemsMenu
            ]);
        }
        
        
    }
}
