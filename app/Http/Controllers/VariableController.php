<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Variable;
class VariableController extends Controller
{
    public function index(){

        $variables = Variable::all();
        $arrConsulta = array();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/variables.variable', [
            'variables' => $variables,
            "arrConsulta" => $arrConsulta,
            'dataUsu' => $usu
        ]);
    }
    public function getFormulaVariableAdd(){
        $variables = Variable::whereIn("fkTipoCampo", ["1","2","4"])->get();
        $tipoOperaciones = DB::table('tipooperacion')->get();
        return view('/formulaVariable.addFormulaVariable', ['variables' => $variables, 'tipoOperaciones' => $tipoOperaciones]);
    }
    public function getFormulaVariableMas($idRegistro){
        $variables = Variable::whereIn("fkTipoCampo", ["1","2","4"])->get();
        $tipoOperaciones = DB::table('tipooperacion')->get();

        return view('/formulaVariable.masFormulaVariable', ['variables' => $variables, 
                                                            'tipoOperaciones' => $tipoOperaciones, 
                                                            'idRegistro' => $idRegistro]);
    }
    public function fillVariable(Request $req){

        $valorFinal = 0;
        
        if($req->tipoInicio == "variable"){
            $variable = Variable::where('idVariable', $req->variableInicial)->first();
            $valorFinal = $variable->valor;
            $html = '<input type="hidden" name="fkVariableInicial" value="'.$req->variableInicial.'" />';
        }
        else if($req->tipoInicio == "valor"){
            $valorFinal = $req->valorInicio;
            $html = '<input type="hidden" name="valorInicial" value="'.$req->valorInicio.'" />';
        }
        


        foreach ($req->tipoOperacion as $key => $operacion) {
            $html .= '<input type="hidden" name="fkTipoOperacion[]" value="'.$operacion.'" />';
            $html .= '<input type="hidden" name="fkVariableFinal[]" value="'.$req->variableFin[$key].'" />';
            $html .= '<input type="hidden" name="valorFinal[]" value="'.$req->valorFin[$key].'" />';

            $tipoOperaciones = DB::table('tipooperacion')->where('idtipoOperacion', $operacion)->first();
            $varActual=0;
            if($req->tipoFin[$key] == "variable"){
                $variable = Variable::where('idVariable', $req->variableFin[$key])->first();
                $varActual = $variable->valor;                
            }
            else if($req->tipoFin[$key] == "valor"){
                $varActual = $req->valorFin[$key];                
            }

            switch ($tipoOperaciones->nombre) {
                case 'SUMA':
                    $valorFinal = $valorFinal + $varActual;
                    break;
                case 'RESTA':
                    $valorFinal = $valorFinal - $varActual;
                    break;
                case 'MULTIPLICACION':
                    $valorFinal = $valorFinal * $varActual;
                    break;
                case 'DIVISION':
                    if($varActual != 0){
                        $valorFinal = $valorFinal / $varActual;    
                    }                    
                    break;
                default:
                    break;
            }
        }






        return response()->json([
            "success" => true,
            "valorFinal" => $valorFinal,
            "html" => $html
        ]);
    }




    public function getFormAdd(){
        $tipoCampo = DB::table('tipo_campo')->get();

    	return view('/variables.add', ['tipoCampo' => $tipoCampo]);
    }
    public function getTipoCampo($idTipoCampo){
        $tipoCampo = DB::table('tipo_campo')->where('idTipoCampo', $idTipoCampo)->first();

        return response()->json([
            "success" => true,
            "tipoValidacion" => $tipoCampo->tipoValidacion
        ]);
        
    }
    public function getFormEdit($idVariable){
    	$variable = Variable::where('idVariable', $idVariable)->join('tipo_campo AS tc', 'tc.idTipoCampo', '=', 'variable.fkTipoCampo')->first();
        $tipoCampo = DB::table('tipo_campo')->get();



    	return view('/variables.edit',['variable' => $variable, 'tipoCampo' => $tipoCampo]);
    }
    
    
    public function insert(Request $req){
    	
    	$variable = new Variable;
    	$variable->nombre = $req->nombre; 
    	$variable->descripcion = $req->descripcion; 
    	$variable->tipoGeneracion = $req->tipoGeneracion; 
        $variable->fkTipoCampo  = $req->tipoCampo; 
        $variable->valor = $req->valor; 
        $variable->save();
        $prevFormula=0;
        if($req->tipoGeneracion == "Formula"){
            $insertFormula = array('fkVariable' => $variable->idVariable);
            if(isset($req->fkVariableInicial)){
                $insertFormula["fkVariableInicial"] = $req->fkVariableInicial;
            }
            else if(isset($req->valorInicial)){
                $insertFormula["valorInicial"] = $req->valorInicial;
            }
            foreach ($req->fkTipoOperacion as $key => $operacion) {
                $insertFormula["fkTipoOperacion"] = $req->fkTipoOperacion[$key];
                if(isset($req->fkVariableFinal[$key])){
                    $insertFormula["fkVariableFinal"] = $req->fkVariableFinal[$key];
                }
                else if(isset($req->valorFinal[$key])){
                    $insertFormula["valorFinal"] = $req->valorFinal[$key];
                }
                $prevFormula = DB::table('formulavariable')->insertGetId($insertFormula, "idformulaVariable");
                
                $insertFormula = array('fkVariable' => $variable->idVariable, "fkFormulaVariable" => $prevFormula);
            }
        }
        
    	return response()->json([
			"success" => true
        ]);


    }
    public function update(Request $req){
    	
    	$variable = Variable::find($req->idVariable);
    	$variable->nombre = $req->nombre; 
    	$variable->descripcion = $req->descripcion; 
    	$variable->tipoGeneracion = $req->tipoGeneracion; 
        $variable->fkTipoCampo  = $req->tipoCampo; 
    	$variable->valor = $req->valor; 
    	return response()->json([
			"success" => $variable->save(),
        ]);

    }
    
}
