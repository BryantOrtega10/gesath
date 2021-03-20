<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActualizarDatosController extends Controller
{
    public function redondeos(){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $redondeos = DB::table("tabla_smmlv_redondeo")->get();
        return view('/actualizarDatos.redondeos',[
            "redondeos" => $redondeos,
            "dataUsu" => $dataUsu
        ]);
    }
    public function cambiarRedondeos(Request $req){
        $redondeos = DB::table("tabla_smmlv_redondeo")->get();
        
        foreach($redondeos as $redondeo){
            DB::table("tabla_smmlv_redondeo")->where("id","=",$redondeo->id)->update([
                "ibc" => $req->input("ibc_".$redondeo->id),
                "pension" => $req->input("pension_".$redondeo->id),
                "salud_12_5" => $req->input("salud_12_5_".$redondeo->id),
                "salud_12" => $req->input("salud_12_".$redondeo->id),
                "salud_10" => $req->input("salud_10_".$redondeo->id),
                "salud_8" => $req->input("salud_8_".$redondeo->id),
                "salud_4" => $req->input("salud_4_".$redondeo->id),
                "ccf" => $req->input("ccf_".$redondeo->id),
                "riesgos_5" => $req->input("riesgos_5_".$redondeo->id),
                "riesgos_4" => $req->input("riesgos_4_".$redondeo->id),
                "riesgos_3" => $req->input("riesgos_3_".$redondeo->id),
                "riesgos_2" => $req->input("riesgos_2_".$redondeo->id),
                "riesgos_1" => $req->input("riesgos_1_".$redondeo->id),
                "sena_0_5" => $req->input("sena_0_5_".$redondeo->id),
                "sena_2" => $req->input("sena_2_".$redondeo->id),
                "icbf" => $req->input("icbf_".$redondeo->id),
                "esap" => $req->input("esap_".$redondeo->id),
                "men" => $req->input("men_.$redondeo->id")
            ]);
        }
        return response()->json([
            "success" => true,
            "mensaje" => "Datos actualizados"
        ]);
    }
    public function upcAdicional(){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $tarifas = DB::table("upcadicionaltarifas")->get();
        $edades = [
            "Menores de 1 año",
            "De 1 a 4 años",
            "De 5 a 14 años",
            "De 15 a 18 años (Hombres)",
            "De 15 a 18 años (Mujeres)",
            "De 19 a 44 años (Hombres)",
            "De 19 a 44 años (Mujeres)",
            "De 45 a 49 años",
            "De 50 a 54 años",
            "De 55 a 59 años",
            "De 60 a 64 años",
            "De 65 a 69 años",
            "De 70 a 74 años",
            "De 75 y más años"
        ];
        
        return view('/actualizarDatos.upcadicional',[
            "tarifas" => $tarifas,
            "dataUsu" => $dataUsu,
            "edades" => $edades
        ]);
    }
    public function cambiarUpc(Request $req){
        $tarifas = DB::table("upcadicionaltarifas")->get();
        foreach ($tarifas as $tarifa){
            DB::table("upcadicionaltarifas")->where("idUpcAdicionalTarifas","=",$tarifa->idUpcAdicionalTarifas)->update([
                "valor" => $req->input("tarifa_".$tarifa->idUpcAdicionalTarifas)
            ]);
        }
        return response()->json([
            "success" => true,
            "mensaje" => "Datos actualizados"
        ]);
    }

    public function valoresRetencion(){
        $dataUsu = UsuarioController::dataAdminLogueado();
        $retencion = DB::table("tabla_retencion")->orderBy("minimo")->orderBy("maximo")->get();
        return view('/actualizarDatos.tablaRetencion',[
            "retencion" => $retencion,
            "dataUsu" => $dataUsu
        ]); 
    }
    
    public function getFormRetencion(){
        return view('/actualizarDatos.addRete');
    }

    public function getFormRetencionEdit($idTablaRetencion){
        $tabla = DB::table("tabla_retencion")->where("idTablaRetencion","=",$idTablaRetencion)->first();

        return view('/actualizarDatos.editRete',[
            "tabla" => $tabla
        ]);
    }
    
    
    public function insert(Request $req){
        if(!isset($req->minimo) && !isset($req->maximo)){
            return response()->json([
                "success" => false,
                "mensaje" => "Seleccione un minimo o maximo"
            ]);
        }
        if(empty($req->minimo) && empty($req->maximo)){
            return response()->json([
                "success" => false,
                "mensaje" => "Seleccione un minimo o maximo"
            ]);
        }

        $tabla_retencion = DB::table("tabla_retencion");
        
        if(!empty($req->maximo) && empty($req->minimo)){
            $tabla_retencion = $tabla_retencion->whereNull("minimo");            
        }
        else if(!empty($req->maximo)){
            $tabla_retencion = $tabla_retencion->where("minimo","<",$req->maximo)
            ->where("maximo",">=",$req->maximo);
        }
        
        if(!empty($req->minimo) && empty($req->maximo)){
            $tabla_retencion = $tabla_retencion->whereNull("maximo");
            
        }
        else if(!empty($req->minimo)){
            $tabla_retencion = $tabla_retencion->where("minimo","<=",$req->minimo)
            ->where("maximo",">=",$req->minimo);
        }



        $tabla_retencion = $tabla_retencion->first();
        
        if(isset($tabla_retencion)){
            return response()->json([
                "success" => false,
                "mensaje" => "Esta franja ya se encuentra ocupada, elimine alguna para continuar"
            ]);
        }
        else{
            DB::table("tabla_retencion")->insert([
                "minimo" => $req->minimo,
                "maximo" => $req->maximo,
                "adicion" => $req->adicion,
                "porcentaje" => ($req->porcentaje/100),
            ]);

            return response()->json([
                "success" => true,
                "mensaje" => "Datos actualizados correctamente"
            ]);
        }

    }

    public function delete($idTablaRetencion){
        DB::table("tabla_retencion")->where("idTablaRetencion","=",$idTablaRetencion)->delete();

        return response()->json([
            "success" => true,
            "mensaje" => "Fila eliminada con exito"
        ]);
    }

    public function update(Request $req, $idTablaRetencion){
        
        if(!isset($req->minimo) && !isset($req->maximo)){
            return response()->json([
                "success" => false,
                "mensaje" => "Seleccione un minimo o maximo"
            ]);
        }
        if(empty($req->minimo) && empty($req->maximo)){
            return response()->json([
                "success" => false,
                "mensaje" => "Seleccione un minimo o maximo"
            ]);
        }

        $tabla_retencion = DB::table("tabla_retencion");
        
        if(!empty($req->maximo) && empty($req->minimo)){
            $tabla_retencion = $tabla_retencion->whereNull("minimo");            
        }
        else if(!empty($req->maximo) && !empty($req->minimo)){
            $tabla_retencion = $tabla_retencion->where("minimo","<",$req->maximo)
            ->where("maximo",">=",$req->maximo);
        }
        else if(!empty($req->maximo)){
            $tabla_retencion = $tabla_retencion->where("minimo","<",$req->maximo)
            ->where("maximo",">=",$req->maximo);
        }
        else if(!empty($req->minimo) && empty($req->maximo)){
            $tabla_retencion = $tabla_retencion->whereNull("maximo");
            
        }
        else if(!empty($req->minimo)){
            $tabla_retencion = $tabla_retencion->where("minimo","<=",$req->minimo)
            ->where("maximo",">=",$req->minimo);
        }
        $tabla_retencion = $tabla_retencion->where("idTablaRetencion","<>",$idTablaRetencion);


        $tabla_retencion = $tabla_retencion->first();
        
        if(isset($tabla_retencion)){
            return response()->json([
                "success" => false,
                "mensaje" => "Esta franja ya se encuentra ocupada, elimine alguna para continuar"
            ]);
        }
        else{
            DB::table("tabla_retencion")->where("idTablaRetencion","=",$idTablaRetencion)->update([
                "minimo" => $req->minimo,
                "maximo" => $req->maximo,
                "adicion" => $req->adicion,
                "porcentaje" => ($req->porcentaje/100),
            ]);

            return response()->json([
                "success" => true,
                "mensaje" => "Fila modificada con exito"
            ]);
        }



        
    }

}
