<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaccionController extends Controller
{
    public function index(Request $req){

        $transacciones = DB::table("transaccion", "tc")
        ->select("tc.*","gc.nombre as grupoConcepto_nm","cc3.nombre as centroCosto_nm","cc2.cuenta as cuenta_debito","cc1.cuenta as cuenta_credito","e.razonSocial")
        ->join("grupoconcepto as gc", "gc.idgrupoConcepto", "=","tc.fkGrupoConcepto")
        ->join("catalgocontable as cc1", "cc1.idCatalgoContable", "=","tc.fkCuentaCredito")
        ->join("catalgocontable as cc2", "cc2.idCatalgoContable", "=","tc.fkCuentaDebito")
        ->join("centrocosto as cc3", "cc3.idcentroCosto", "=","tc.fkCentroCosto")
        ->join("empresa as e", "e.idempresa", "=","cc3.fkEmpresa")
        ->paginate(15); 


        return view('/transaccion.index',
            ["transacciones" => $transacciones]
        );
    }
    public function getFormAdd(){
        $grupoconceptos = DB::table('grupoconcepto')->get();
        $cuentas = DB::table("catalgocontable")->get();
        $centrosCosto = DB::table("centrocosto","cc3")   
        ->select("cc3.*","e.razonSocial")
        ->join("empresa as e", "e.idempresa", "=","cc3.fkEmpresa")
        ->get();
        
        return view('/transaccion.formAdd',
            [
                "grupoconceptos" => $grupoconceptos, 
                "cuentas" => $cuentas,
                "centrosCosto" => $centrosCosto       
            ]
        );
    }
    public function crear(Request $req){
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'after_or_equal' => 'La :attribute debe ser mayor a la inicial.',
        ];
        $validator = Validator::make($req->all(), [
            
            'fkGrupoConcepto' => 'required',
            'fkCuentaDebito' => 'required',
            'fkCuentaCredito' => 'required',
            'fkCentroCosto' => 'required'
            
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $arrTransaccion = [
            "fkGrupoConcepto" => $req->fkGrupoConcepto,
            "fkCuentaDebito" => $req->fkCuentaDebito,
            "fkCuentaCredito" => $req->fkCuentaCredito,
            "fkCentroCosto" => $req->fkCentroCosto
            
        ];
        DB::table("transaccion")->insert($arrTransaccion);
        return response()->json(["success" => true]);
    }
}
