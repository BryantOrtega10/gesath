<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\GrupoConcepto;
use App\GrupoConceptoConcepto;
use App\Concepto;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GrupoConceptoController extends Controller
{
    public function index(){
    	
        $grupoConceptos = GrupoConcepto::all();
        $usu = UsuarioController::dataAdminLogueado();
    	return view('/grupoConcepto.grupoConcepto', [
            'grupos' => $grupoConceptos,
            'dataUsu' => $usu
        ]);
    }
    public function getFormAdd(){
        $conceptos = DB::table('concepto')->orderBy('nombre')->get();        
        
    	return view('/grupoConcepto.add', ['conceptos' => $conceptos]);
    }

    public function getMasConceptos($idRegistro){
        $conceptos = DB::table('concepto')->get();        

        return view('/grupoConcepto/conceptos.addConcepto', [
            'conceptos' => $conceptos,
            'idDom' => $idRegistro
        ]);
    }
    public function insert(Request $req){
    	$grupoConcepto = new GrupoConcepto;
    	$grupoConcepto->nombre = $req->nombre; 
        $insert = $grupoConcepto->save();
        if ($insert) {
            foreach( $req->fkConcepto as $concepto) {
                if($concepto != ""){
                    GrupoConceptoConcepto::insert([
                        'fkGrupoConcepto' => $grupoConcepto->idgrupoConcepto,
                        'fkConcepto' => $concepto                
                    ]);
                }
                
            }
            /* foreach ($req->concepto as $concepto) {
                $grupoCon = new GrupoConceptoConcepto();
                $grupoCon->fkGrupoConcepto = $grupoConcepto->idgrupoConcepto;
                $grupoCon->fkConcepto = $concepto;
                $grupoCon->save();
            } */
        }          

        return response()->json([
			"success" => true
        ]);
    }
    public function edit($id) {
        try {
            $grupoConcepto = DB::table('grupoconcepto')->select(
                'grupoconcepto.*'
            )
            ->where('grupoconcepto.idgrupoConcepto', '=', $id)
            ->get();

            $grupoConceptoFKS = DB::table('grupoconcepto_concepto')->select(
                'grupoconcepto_concepto.*'
            )->where('grupoconcepto_concepto.fkGrupoConcepto', '=', $id)
            ->get();
            $conceptos = Concepto::all();
            $cantCon = sizeof($grupoConceptoFKS);
            $vistaConceptos = view('/grupoConcepto/conceptos.editConcepto', [
                'grupoConcepto' => $grupoConcepto,
                'conceptosFK' => $grupoConceptoFKS,
                'conceptos' => $conceptos
            ]);
            return view('/grupoConcepto.editGrupoConcepto', [
                'cantCon' => $cantCon,
                'grupoConcepto' => $grupoConcepto,
                'conceptosFK' => $grupoConceptoFKS,
                'conceptos' => $conceptos,
                'DomConceptos' => $vistaConceptos
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un grupo concepto con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $grupoConcepto = DB::table('grupoconcepto')->select(
                'grupoconcepto.*'
            )
            ->where('grupoconcepto.idgrupoConcepto', '=', $id)
            ->get();

            $grupoConceptoFKS = DB::table('grupoconcepto_concepto')->select(
                'grupoconcepto_concepto.*'
            )->where('grupoconcepto_concepto.fkGrupoConcepto', '=', $id)
            ->get();
            $conceptos = Concepto::all();
            $vistaConceptos = view('/grupoConcepto/conceptos.detailConcepto', [
                'grupoConcepto' => $grupoConcepto,
                'conceptosFK' => $grupoConceptoFKS,
                'conceptos' => $conceptos
            ]);
            return view('/grupoConcepto.detalleGrupoConcepto', [
                'grupoConcepto' => $grupoConcepto,
                'conceptosFK' => $grupoConceptoFKS,
                'conceptos' => $conceptos,
                'DomConceptos' => $vistaConceptos
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un grupo concepto con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $grupoConcepto = GrupoConcepto::findOrFail($id);
            $grupoConcepto->nombre = $request->nombre; 
            $grupoConcepto->save();
            $this->agregarConceptosFk($request->fkConcepto, $grupoConcepto->idgrupoConcepto);
            return response()->json([
                "success" => true,
                "mensaje" => "Grupo Concepto actualizado correctamente"
            ]);
        } catch (ModelNotFoundException $e) {
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un grupo concepto con este ID"]);
		}
    }

    public function delete($id) {
        try{
            $grupoConCon = GrupoConceptoConcepto::where('fkGrupoConcepto', $id)->delete();
            if ($grupoConCon) {
                $grupoConcepto = GrupoConcepto::findOrFail($id);
                if($grupoConcepto->delete()){
                    $success = true;
                    $mensaje = "Grupo concepto eliminado con exito";
                } else {
                    $success = false;
                    $mensaje = "Error al eliminar grupo concepto";
                }
            } else {
                $success = false;
                $mensaje = "Error al eliminar grupo concepto";
            }			
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un grupo concepto con este ID"]);
		}
    }
    public function agregarConceptosFk($conceptos, $idGrupo) {
        GrupoConceptoConcepto::where('fkGrupoConcepto', $idGrupo)->delete();
        foreach( $conceptos as $concepto) {
            GrupoConceptoConcepto::insert([
                'fkGrupoConcepto' => $idGrupo,
                'fkConcepto' => $concepto                
            ]);
        }
    }
    /* DOM ADICIONAL PARA CREAR Y ELIMINAR CONCEPTOS DE UN GRUPO */

    public function conceptoDOM($idDom) {
        $concepto = Concepto::where('fkEstado', 1)->get();
        return view('/grupoConcepto/conceptos.addConcepto', [
            'conceptos' => $concepto,
            'idDom' => $idDom
        ]);
    }
}
