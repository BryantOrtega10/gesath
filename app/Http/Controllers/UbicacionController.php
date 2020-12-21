<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Ubicacion;
class UbicacionController extends Controller
{
    public function index(){
		$ubicaciones = DB::table('ubicacion')
    							->join('tipoubicacion AS tpu', 'tpu.idtipoUbicacion', '=', 'ubicacion.fkTipoUbicacion')
    							->join('ubicacion AS u2','ubicacion.idUbicacion', '=', 'u2.idUbicacion')
								->select('ubicacion.*', 'tpu.nombre AS tpu_nombre', 'u2.nombre AS u2_nombre')
								->orderBy('ubicacion.fkTipoUbicacion', 'asc')
								->orderBy('ubicacion.nombre', 'asc')->get();
   
    	return view('/ubicacion.ubicacion', ['ubicaciones' => $ubicaciones]);
    }
    public function getFormAdd(){
		$tUbicacion = DB::table('tipoubicacion')->get();
    	return view('/ubicacion.addUbicacion', ['tUbicacion' => $tUbicacion]);
	}
	public function cambioTUbicacion($idTipoUbicacion){
		

		$tpUbicacion = DB::table('tipoubicacion')->where("idtipoUbicacion", "=", $idTipoUbicacion)->first();
		$html="";
		switch ($tpUbicacion->nivel) {
			case 2:

				$paises = Ubicacion::where("fkTipoUbicacion", "=", "1")->get();
				$html.='
					<div class="form-group">
					<label for="pais" class="control-label">Pais:</label>
					<select class="form-control" id="pais" name="pais" required="">
						<option value=""></option>';
						foreach($paises as $pais){
							$html.="<option value='".$pais->idubicacion."'>".$pais->nombre."</option>";
						}
					$html.='</select>
				</div>';
				break;
			case 3:
				$paises = Ubicacion::where("fkTipoUbicacion", "=", "1")->get();
				$html.='
					<div class="form-group">
					<label for="pais" class="control-label">Pais:</label>
					<select class="form-control" id="pais" name="pais" required="">
						<option value=""></option>';
						foreach($paises as $pais){
							$html.="<option value='".$pais->idubicacion."'>".$pais->nombre."</option>";
						}
					$html.='</select>
					</div>
					<div class="form-group">
					<label for="depto" class="control-label">Departamento:</label>
					<select class="form-control" id="depto" name="depto" required="">
						<option value=""></option>
					</select>
					</div>';
				
				break;
			case 4:
				$paises = Ubicacion::where("fkTipoUbicacion", "=", "1")->get();
				$html.='
					<div class="form-group">
					<label for="pais" class="control-label">Pais:</label>
					<select class="form-control" id="pais" name="pais" required="">
						<option value=""></option>';
						foreach($paises as $pais){
							$html.="<option value='".$pais->idubicacion."'>".$pais->nombre."</option>";
						}
					$html.='</select>
					</div>
					<div class="form-group">
					<label for="depto" class="control-label">Departamento:</label>
					<select class="form-control" id="depto" name="depto" required="">
						<option value=""></option>
					</select>
					</div>
					<div class="form-group">
					<label for="ciudad" class="control-label">Ciudad:</label>
					<select class="form-control" id="ciudad" name="ciudad" required="">
						<option value=""></option>
					</select>
					</div>';
				

				break;
		}
		return response()->json([
			"success" => true,
			"html" => $html
        ]);

	}
	public function obtenerSubUbicaciones($idUbicacion){
		$ubicaciones = Ubicacion::where("fkUbicacion", "=", $idUbicacion)->get();
		$opciones = "<option value=''></option>";
		foreach($ubicaciones as $ubicacion){
			$opciones.= '<option value="'.$ubicacion->idubicacion.'">'.$ubicacion->nombre.'</option>';
		}
		return response()->json([
			"success" => true,
			"opciones" => $opciones
        ]);

	}


    public function getFormEdit($idUbicacion){
    	$ubicacion = Ubicacion::where('idUbicacion', $idUbicacion)->first();
    	return view('/ubicacion.edit',['ubicacion' => $ubicacion]);
    }
    
    public function insert(Request $req){
    	
    	$ubicacion = new Ubicacion;
		$ubicacion->idubicacion = $req->codigo; 
		$ubicacion->nombre = $req->nombre; 
		$ubicacion->nombre2 = $req->nombre; 
		$tpUbicacion = DB::table('tipoubicacion')->where("idtipoUbicacion", "=", $req->tUbicacion)->first();
		
		switch ($tpUbicacion->nivel) {
			case 2:
				$ubicacion->fkUbicacion = $req->pais;
				break;
			case 3:
				$ubicacion->fkUbicacion = $req->depto;
				break;
			case 3:
				$ubicacion->fkUbicacion = $req->ciudad;
				break;
		}
		$ubicacion->fkTipoUbicacion = $req->tUbicacion; 
		
		
    	
    	return response()->json([
			"success" => $ubicacion->save()
        ]);


    }
    public function update(Request $req){
    	
    	/*$variable = Variable::find($req->idVariable);
    	$variable->nombre = $req->nombre; 
    	$variable->descripcion = $req->descripcion; 
    	$variable->tipoGeneracion = $req->tipoGeneracion; 
    	$variable->valor = $req->valor; 
    	return response()->json([
			"success" => $variable->save(),
        ]);*/

    }
}
