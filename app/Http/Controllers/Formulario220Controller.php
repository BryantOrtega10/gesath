<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Formulario220Controller extends Controller
{
    public function index(){
        $formularios = DB::table("formulario220")->get();
        return view('/formulario220.index', ["formularios" => $formularios]);
    }
    public function getFormAdd(){
		return view('/formulario220.add');
    }
    public function getFormEdit($idFormulario220){
        $formulario = DB::table("formulario220")->where("idFormulario220","=",$idFormulario220)->first();
		return view('/formulario220.edit',["formulario" => $formulario]);
    }
    
    public function modificar(Request $req){
        
        
           


            $foto = $req->fotoAnt;
            if ($req->hasFile('imagen')) {
                $foto = $req->file("imagen")->store("public/reportes");
                $foto = str_replace("public/","",$foto);
            }


            DB::table("formulario220")->where("idFormulario220","=", $req->idFormulario)->update([
                "anio" => $req->anio,
                "rutaImagen" => $foto,
                "punto1" => $req->punto1,
                "punto2" => $req->punto2,
                "punto3" => $req->punto3,
                "punto4" => $req->punto4,
                "punto5" => $req->punto5,
                "punto6" => $req->punto6
            ]);
            return response()->json([
                "success" => true
                
            ]);
        
    

    }
    
    public function crear(Request $req){
        







        if ($req->hasFile('imagen')) {
           
            $existe = DB::table("formulario220")->where("anio","=",$req->anio)->get();
            if(sizeof($existe) > 0){
                return response()->json([
                    "success" => false,
                    "mensaje" => "Error ese año ya posee un formulario"
                ]);
            }
            else{
                $foto = $req->file("imagen")->store("public/reportes");
                $foto = str_replace("public/","",$foto);
                DB::table("formulario220")->insert([
                    "anio" => $req->anio,
                    "rutaImagen" => $foto,
                    "punto1" => $req->punto1,
                    "punto2" => $req->punto2,
                    "punto3" => $req->punto3,
                    "punto4" => $req->punto4,
                    "punto5" => $req->punto5,
                    "punto6" => $req->punto6
                ]);
                return response()->json([
                    "success" => true
                    
                ]);
            }
            
        }
        else{
            return response()->json([
                "success" => false,
                "mensaje" => "Error imagen vacia"
            ]);
        }

    }
}
