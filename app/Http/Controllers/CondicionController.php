<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Condicion;
use Illuminate\Support\Facades\DB;
use App\Variable;

class CondicionController extends Controller
{
    public function index($idConcepto){
        $condiciones = Condicion::select('condicion.idcondicion','condicion.descripcion','condicion.mensajeMostrar',
                'tc.nombre AS tipoCondicion','tr.nombre AS tipoResultado')
        ->join('tipocondicionin AS tc', 'tc.idtipoCondicion', '=', 'condicion.fkTipoCondicion')
        ->join('tiporesultado AS tr', 'tr.idtipoResultado', '=', 'condicion.fkTipoResultado')
        ->where('condicion.fkConcepto', $idConcepto)
        ->get();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/concepto.condiciones.condiciones', [
            'condiciones' => $condiciones,
            'idConcepto' => $idConcepto,
            'dataUsu' => $usu
        ]);
    }
    public function getFormAdd($idConcepto){

        $tipoCondiciones = DB::table('tipocondicionin')->get();
        $tipoResultados = DB::table("tiporesultado")->get();
        $conceptos = DB::table('concepto')->get();
        $grupoconceptos = DB::table('grupoconcepto')->get();
        $operadores = DB::table("operadorcomparacion")->get();
        $variables = Variable::all();

        return view('/concepto.condiciones.addCondiciones', [   'tipoCondiciones' => $tipoCondiciones, 
                                                                'tipoResultados' => $tipoResultados,
                                                                'conceptos' => $conceptos, 
                                                                'grupoConceptos' => $grupoconceptos, 
                                                                'operadores' => $operadores, 
                                                                'variables' => $variables,
                                                                'idConcepto' => $idConcepto                                                                
                                                                ]);
    }

    public function camposOperador($idOperador){
        $operadores = DB::table("operadorcomparacion")->where("idoperadorComparacion", $idOperador)->first();

        return response()->json([
            "success" => true,
            "numCampos" => $operadores->numCampos
        ]);
    }
    public function masItems($numItem){

        $operadores = DB::table("operadorcomparacion")->get();
        $conceptos = DB::table('concepto')->get();
        $grupoConceptos = DB::table('grupoconcepto')->get();
        $operadores = DB::table("operadorcomparacion")->get();
        $tipocondiciones = DB::table("tipocondicion")->where('idtipoCondicion','!=','1')->get();
        $variables = Variable::all();

        $html = '<hr>
                <div class="form-group">
                    <label for="tipocondicion'.$numItem.'" class="control-label">Tipo condicion:</label>
                    <select class="form-control tipocondicion" id="tipocondicion'.$numItem.'" name="tipocondicion[]" data-id="'.$numItem.'">
                        <option value="">Seleccione uno</option>';
                        foreach($tipocondiciones as $tipocondicion){
                            $html.='<option value="'.$tipocondicion->idtipoCondicion.'">'.$tipocondicion->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html .= '
                <div class="form-group">
                    <label for="operador'.$numItem.'" class="control-label">Operador comparacion:</label>
                    <select class="form-control operador" id="operador'.$numItem.'" name="operador[]" data-id="'.$numItem.'">
                        <option value="">Seleccione uno</option>';
                        foreach($operadores as $operador){
                            $html.='<option value="'.$operador->idoperadorComparacion.'">'.$operador->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html.='<div class="form-group tipoFin1 oculto" data-id="'.$numItem.'">
                    <label for="tipoFin1'.$numItem.'" class="control-label">Tipo fin</label>
                    <select class="form-control selectTipoFin1" id="tipoFin1'.$numItem.'" name="tipoFin1[]" data-id="'.$numItem.'">
                        <option value="">Seleccione uno</option>
                        <option value="concepto">Concepto</option>
                        <option value="grupo">Grupo de concepto</option>
                        <option value="variable">Variable</option>
                        <option value="valor">Valor Fijo</option>
                    </select>
                </div>';
        $html.='<div class="form-group variableFin1 oculto" data-id="'.$numItem.'">
                    <label for="variableFin1'.$numItem.'" class="control-label">Variable Final:</label>
                    <select class="form-control cambiarValorFinal" id="variableFin1'.$numItem.'" name="variableFin1[]">
                        <option value="">Seleccione uno</option>';
                        foreach($variables as $variable){
                            $html.='<option value="'.$variable->idVariable.'">'.$variable->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html.='<div class="form-group conceptoFin1 oculto" data-id="'.$numItem.'">
                    <label for="conceptoFin1'.$numItem.'" class="control-label">Concepto Final:</label>
                    <select class="form-control" id="conceptoFin1'.$numItem.'" name="conceptoFin1[]">
                        <option value="">Seleccione uno</option>';
                        foreach($conceptos as $concepto){
                            $html.='<option value="'.$concepto->idconcepto.'">'.$concepto->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';           
        $html.='<div class="form-group grupoFin1 oculto" data-id="'.$numItem.'">
                    <label for="grupoFin1'.$numItem.'" class="control-label">Grupo concepto Final:</label>
                    <select class="form-control" id="grupoFin1'.$numItem.'" name="grupoFin1[]">
                        <option value="">Seleccione uno</option>';
                        foreach($grupoConceptos as $grupoConcepto){
                            $html.='<option value="'.$grupoConcepto->idgrupoConcepto.'">'.$grupoConcepto->nombre.'</option>';
                        }
                        $html.='</select>
                </div>';
        $html.='<div class="form-group valorFin1 oculto" data-id="'.$numItem.'">
                    <label for="valorFin1'.$numItem.'" class="control-label">Valor Final:</label>
                    <input type="text" class="form-control cambiarValorFinal" id="valorFin1'.$numItem.'" name="valorFin1[]" />
                </div>';
        $html.='<div class="form-group multiplicadorFin1 oculto" data-id="'.$numItem.'">
                <label for="multiplicadorFin1'.$numItem.'" class="control-label">Multiplicado por:</label>
                <input type="text" class="form-control" id="multiplicadorFin1'.$numItem.'" name="multiplicadorFin1[]" />
            </div>';



        $html.='<div class="form-group tipoFin2 oculto" data-id="'.$numItem.'">
                    <label for="tipoFin2'.$numItem.'" class="control-label">Tipo fin</label>
                    <select class="form-control selectTipoFin2" id="tipoFin2'.$numItem.'" name="tipoFin2[]" data-id="'.$numItem.'">
                        <option value="">Seleccione uno</option>
                        <option value="concepto">Concepto</option>
                        <option value="grupo">Grupo de concepto</option>
                        <option value="variable">Variable</option>
                        <option value="valor">Valor Fijo</option>
                    </select>
                </div>';
        $html.='<div class="form-group variableFin2 oculto" data-id="'.$numItem.'">
                    <label for="variableFin2'.$numItem.'" class="control-label">Variable Final:</label>
                    <select class="form-control cambiarValorFinal" id="variableFin2'.$numItem.'" name="variableFin2[]">
                        <option value="">Seleccione uno</option>';
                        foreach($variables as $variable){
                            $html.='<option value="'.$variable->idVariable.'">'.$variable->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html.='<div class="form-group conceptoFin2 oculto" data-id="'.$numItem.'">
                <label for="conceptoFin2'.$numItem.'" class="control-label">Concepto Final:</label>
                <select class="form-control" id="conceptoFin2'.$numItem.'" name="conceptoFin2[]">
                    <option value="">Seleccione uno</option>';
                    foreach($conceptos as $concepto){
                        $html.='<option value="'.$concepto->idconcepto.'">'.$concepto->nombre.'</option>';
                    }
                $html.='</select>
            </div>';
        $html.='<div class="form-group grupoFin2 oculto" data-id="'.$numItem.'">
                    <label for="grupoFin2'.$numItem.'" class="control-label">Grupo concepto Final:</label>
                    <select class="form-control" id="grupoFin2'.$numItem.'" name="grupoFin2[]">
                        <option value="">Seleccione uno</option>';
                        foreach($grupoConceptos as $grupoConcepto){
                            $html.='<option value="'.$grupoConcepto->idgrupoConcepto.'">'.$grupoConcepto->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html.='<div class="form-group valorFin2 oculto" data-id="'.$numItem.'">
                    <label for="valorFin2'.$numItem.'" class="control-label">Valor Final:</label>
                    <input type="text" class="form-control cambiarValorFinal" id="valorFin2'.$numItem.'" name="valorFin2[]" />
                </div>';      
        $html.='<div class="form-group multiplicadorFin2 oculto" data-id="'.$numItem.'">
                <label for="multiplicadorFin2'.$numItem.'" class="control-label">Multiplicado por:</label>
                <input type="text" class="form-control" id="multiplicadorFin2'.$numItem.'" name="multiplicadorFin2[]" />
            </div>'; 
                


        $html.='<div class="form-group tipoFin3 oculto" data-id="'.$numItem.'">
                    <label for="tipoFin3'.$numItem.'" class="control-label">Tipo fin</label>
                    <select class="form-control selectTipoFin3" id="tipoFin3'.$numItem.'" name="tipoFin3[]" data-id="'.$numItem.'">
                        <option value="">Seleccione uno</option>
                        <option value="concepto">Concepto</option>
                        <option value="grupo">Grupo de concepto</option>
                        <option value="variable">Variable</option>
                        <option value="valor">Valor Fijo</option>
                    </select>
                </div>';
        $html.='<div class="form-group variableFin3 oculto" data-id="'.$numItem.'">
                    <label for="variableFin3'.$numItem.'" class="control-label">Variable Final:</label>
                    <select class="form-control cambiarValorFinal" id="variableFin3'.$numItem.'" name="variableFin3[]">
                        <option value="">Seleccione uno</option>';
                        foreach($variables as $variable){
                            $html.='<option value="'.$variable->idVariable.'">'.$variable->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html.='<div class="form-group conceptoFin3 oculto" data-id="'.$numItem.'">
                    <label for="conceptoFin3'.$numItem.'" class="control-label">Concepto Final:</label>
                    <select class="form-control" id="conceptoFin3'.$numItem.'" name="conceptoFin3[]">
                        <option value="">Seleccione uno</option>';
                        foreach($conceptos as $concepto){
                            $html.='<option value="'.$concepto->idconcepto.'">'.$concepto->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html.='<div class="form-group conceptoFin3 oculto" data-id="'.$numItem.'">
                <label for="conceptoFin3'.$numItem.'" class="control-label">Concepto Final:</label>
                <select class="form-control" id="conceptoFin3'.$numItem.'" name="conceptoFin3[]">
                    <option value="">Seleccione uno</option>';
                    foreach($conceptos as $concepto){
                        $html.='<option value="'.$concepto->idconcepto.'">'.$concepto->nombre.'</option>';
                    }
                $html.='</select>
            </div>';
        $html.='<div class="form-group grupoFin3 oculto" data-id="'.$numItem.'">
                    <label for="grupoFin3'.$numItem.'" class="control-label">Grupo concepto Final:</label>
                    <select class="form-control" id="grupoFin3'.$numItem.'" name="grupoFin3[]">
                        <option value="">Seleccione uno</option>';
                        foreach($grupoConceptos as $grupoConcepto){
                            $html.='<option value="'.$grupoConcepto->idgrupoConcepto.'">'.$grupoConcepto->nombre.'</option>';
                        }
                    $html.='</select>
                </div>';
        $html.='<div class="form-group valorFin3 oculto" data-id="'.$numItem.'">
                    <label for="valorFin3'.$numItem.'" class="control-label">Valor Final:</label>
                    <input type="text" class="form-control cambiarValorFinal" id="valorFin3'.$numItem.'" name="valorFin3[]" />
                </div>';

        $html.='<div class="form-group multiplicadorFin3 oculto" data-id="'.$numItem.'">
                <label for="multiplicadorFin3'.$numItem.'" class="control-label">Multiplicado por:</label>
                <input type="text" class="form-control" id="multiplicadorFin3'.$numItem.'" name="multiplicadorFin3[]" />
            </div>'; 
        return response()->json([
            "success" => true,
            "html" => $html
        ]);
    }
    public function insert(Request $req){

        $idcondicion = DB::table('condicion')->insertGetId([
            "mensajeMostrar" => $req->mensajeCondicion, 
            "fkTipoCondicion"=> $req->tipoCondicion, 
            "fkTipoResultado"=> $req->tipoResultado,
            "descripcion"=> $req->descripcionCondicion,
            "fkConcepto" => $req->idConcepto
        ], 'idcondicion');
        










        
        $insertItem = array('fkCondicion' => $idcondicion, 'fkTipoCondicion' => '1');
        if($req->tipoInicio=="concepto"){
            $insertItem['fkConceptoInicial'] = $req->conceptoInicial;
        }
        else if($req->tipoInicio=="grupo"){
            
            $insertItem['fkGrupoConceptoInicial'] = $req->grupoInicial;
        }
        
        foreach($req->operador as $key => $operador){
            $insertItem['fkOperadorComparacion'] = $operador;
            $insertItem['multiplicador1'] = $req->multiplicadorFin1[$key];
            $insertItem['multiplicador2'] = $req->multiplicadorFin2[$key];
        
            
            if($req->tipoFin1[$key] == "concepto"){
                $insertItem['fkConceptoFinal1'] = $req->conceptoFin1[$key];                
            }
            else if($req->tipoFin1[$key] == "grupo"){
                $insertItem['fkGrupoConceptoFinal1'] = $req->grupoFin1[$key];                
            }
            else if($req->tipoFin1[$key] == "variable"){
                $insertItem['fkVariableFinal1'] = $req->variableFin1[$key];                
            } 
            else if($req->tipoFin1[$key] == "valor"){
                $insertItem['valorCampo1'] = $req->valorFin1[$key];                
            }


            if($req->tipoFin2[$key] == "concepto"){
                $insertItem['fkConceptoFinal2'] = $req->conceptoFin2[$key];                
            }
            else if($req->tipoFin2[$key] == "grupo"){
                $insertItem['fkGrupoConceptoFinal2'] = $req->grupoFin2[$key];                
            }
            else if($req->tipoFin2[$key] == "variable"){
                $insertItem['fkVariableFinal2'] = $req->variableFin2[$key];                
            } 
            else if($req->tipoFin2[$key] == "valor"){
                $insertItem['valorCampo2'] = $req->valorFin2[$key];                
            }

            $prevFormula = DB::table('itemCondicion')->insertGetId($insertItem, "iditemCondicion");
                
            $insertItem = array('fkCondicion' => $idcondicion, 'fkItemCondicionInicial' => $prevFormula);
            if(isset($req->tipocondicion[$key])){
                $insertItem['fkTipoCondicion'] = $req->tipocondicion[$key];
            }
        }

        return response()->json([
			"success" => true
        ]);

    }

    
}
