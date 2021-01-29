<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariosController extends Controller
{
    public function codigosDiagnostico(Request $req){
        $codigosDiagnostico = DB::table('cod_diagnostico', 'cd');
        
        $arrConsulta = array();

        if(isset($req->nombre)){
            $codigosDiagnostico->where("cd.nombre", "LIKE", "%".$req->nombre."%");
            $arrConsulta["nombre"] = $req->nombre;
        }
        if(isset($req->codigo)){
            $codigosDiagnostico->where("cd.idCodDiagnostico", "LIKE", $req->codigo."%");
            $arrConsulta["codigo"] = $req->codigo;
        }
        $codigosDiagnostico = $codigosDiagnostico->paginate(15);

        return view('varios.ajax.codigosDiagnostico', ['codigosDiagnostico' => $codigosDiagnostico, 'arrConsulta' => $arrConsulta, 'req' => $req]);
    }
}
