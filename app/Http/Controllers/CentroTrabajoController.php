<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\CentroTrabajoModel;
use App\Http\Requests\CentroTrabajoRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CentroTrabajoController extends Controller
{
    public function index($id) {
        $centroTrabajo = CentroTrabajoModel::where('fkEmpresa', $id)->get();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/empresas/centroTrabajo.centroTrabajo', [
            "centroTrabajos" => $centroTrabajo,
            'dataUsu' => $usu,
            'idEmpre' => $id
        ]);
    }

    public function getFormAdd($id) {
        $nivelArl = DB::table('nivel_arl')->select('*')->get();
        return view('/empresas/centroTrabajo/addCentroTrabajo', [
            'nivelArl' => $nivelArl,
            'idEmpresa' => $id
        ]);
    }

    public function create(CentroTrabajoRequest $request, $fkEmpresa) {
        $centroTrabajo = new CentroTrabajoModel();
        $centroTrabajo->codigo = $request->codigo;
        $centroTrabajo->nombre = $request->nombre;
        $centroTrabajo->fkNivelArl = $request->fkNivelArl;
        $centroTrabajo->fkEmpresa = $fkEmpresa;
        $save = $centroTrabajo->save();
        if ($save) {
            $success = true;
            $mensaje = "Centro trabajo agregado correctamente";
        } else {
            $success = true;
            $mensaje = "Error al agregar centro trabajo";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function edit($id) {
        try {
            $centroTrabajo = CentroTrabajoModel::findOrFail($id);
            $nivelArl = DB::table('nivel_arl')->select('*')->get();
            return view('/empresas/centroTrabajo.editCentroTrabajo', [
                'centro' => $centroTrabajo,
                'nivelArl' => $nivelArl
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un centro trabajo con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $centroTrabajo = CentroTrabajoModel::findOrFail($id);
            $nivelArl = DB::table('nivel_arl')->select('*')->get();
            return view('/empresas/centroTrabajo.detailCentroTrabajo', [
                'centro' => $centroTrabajo,
                'nivelArl' => $nivelArl
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un centro trabajo con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $centroTrabajo = CentroTrabajoModel::findOrFail($id);
            $centroTrabajo->codigo = $request->codigo;
            $centroTrabajo->nombre = $request->nombre;
            $centroTrabajo->fkNivelArl = $request->fkNivelArl;
            $save = $centroTrabajo->save();
            if ($save) {
                $success = true;
                $mensaje = "Centro trabajo actualizado correctamente";
            } else {
                $success = true;
                $mensaje = "Error al actualizar centro trabajo";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un centro trabajo con este ID"]);
		}
    }

    public function delete($id) {
        try{
			$centroTrabajo = CentroTrabajoModel::findOrFail($id);
			if($centroTrabajo->delete()){
				$success = true;
				$mensaje = "Centro trabajo eliminado con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar centro trabajo";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un centro trabajo con este ID"]);
		}
    }
}
