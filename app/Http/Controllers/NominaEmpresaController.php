<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\NominaEmpresaRequest;
use App\NominaEmpresaModel;
use Illuminate\Database\Eloquent\ModelNotFoundHttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NominaEmpresaController extends Controller
{
    public function index($id) {
        $nominaEmpresa = NominaEmpresaModel::where('fkEmpresa', $id)->get();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/empresas/nominaEmpresa.nominaEmpresa', [
            "nominaEmpresa" => $nominaEmpresa,
            'dataUsu' => $usu
        ]);
    }

    public function getFormAdd($id) {
        return view('/empresas/nominaEmpresa/addNominaEmpresa', [
            'idNomina' => $id
        ]);
    }

    public function create(NominaEmpresaRequest $request) {
        $nominaEmpresa = new NominaEmpresaModel();
        $nominaEmpresa->nombre = $request->nombre;
        $nominaEmpresa->tipoPeriodo = $request->tipoPeriodo;
        $nominaEmpresa->periodo = $request->periodo;
        $nominaEmpresa->fkEmpresa = $request->fkEmpresa;
        $nominaEmpresa->diasCesantias = $request->dias_cesantias;
        $nominaEmpresa->id_uni_nomina = $request->id_uni_nomina;
        $save = $nominaEmpresa->save();
        if ($save) {
            $success = true;
            $mensaje = "Nomina de empresa agregada correctamente";
        } else {
            $success = true;
            $mensaje = "Error al agregar nomina de empresa";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function edit($id) {
        try {
            $nominaEmpresa = NominaEmpresaModel::findOrFail($id);
            return view('/empresas/nominaEmpresa.editNominaEmpresa', [
                'nominaEmpresa' => $nominaEmpresa
            ]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un nomina de empresa con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $nominaEmpresa = NominaEmpresaModel::findOrFail($id);
            return view('/empresas/nominaEmpresa.detalleNominaEmpresa', [
                'nominaEmpresa' => $nominaEmpresa
            ]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $nominaEmpresa = NominaEmpresaModel::findOrFail($id);
            $nominaEmpresa->nombre = $request->nombre;
            $nominaEmpresa->tipoPeriodo = $request->tipoPeriodo;
            $nominaEmpresa->periodo = $request->periodo;
            $nominaEmpresa->diasCesantias = $request->dias_cesantias;
            $nominaEmpresa->fkEmpresa = $request->fkEmpresa;
            $nominaEmpresa->id_uni_nomina = $request->id_uni_nomina;
            $save = $nominaEmpresa->save();
            if ($save) {
                $success = true;
                $mensaje = "Nomina de empresa actualizada correctamente";
            } else {
                $success = true;
                $mensaje = "Error al actualizar nomina de empresa";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
		}
    }

    public function delete($id) {
        try{
            $empleado = DB::table('empleado')->where('fkNomina', $id)->first();
            if (!$empleado) {
                $nominaEmpresa = NominaEmpresaModel::findOrFail($id);
                if($nominaEmpresa->delete()){
                    $success = true;
                    $mensaje = "Nomina de empresa eliminada con exito";
                } else {
                    $success = false;
                    $mensaje = "Error al eliminar nomina de empresa";
                }
            } else {
                $success = false;
				$mensaje = "Hay un empleado asociado a esta empresa, no puedes eliminar esta nómina";
            }
			
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
		}
    }
}
