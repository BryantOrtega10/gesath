<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CargosModel;
use App\Http\Requests\CargosRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CargosController extends Controller
{
    public function index() {
        $cargos = CargosModel::all();
        return view('/cargos/cargos', [
            'cargos' => $cargos
        ]);
    }

    public function getFormAdd() {
        return view('/cargos/addCargo');
    }

    public function create(CargosRequest $request) {
        $cargo = new CargosModel();
        $cargo->nombreCargo = $request->nombreCargo;
        $save = $cargo->save();
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
            $cargo = CargosModel::findOrFail($id);
            return view('/cargos/editCargo', [
                'cargos' => $cargo
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un nomina de empresa con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $cargo = CargosModel::findOrFail($id);
            return view('/cargos/detailCargo', [
                'cargos' => $cargo
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $cargo = CargosModel::findOrFail($id);
            $cargo->nombreCargo = $request->nombreCargo;
            $save = $cargo->save();
            if ($save) {
                $success = true;
                $mensaje = "Nomina de empresa actualizada correctamente";
            } else {
                $success = true;
                $mensaje = "Error al actualizar nomina de empresa";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
		}
    }

    public function delete($id) {
        try{
            $cargo = CargosModel::findOrFail($id);
            if($cargo->delete()){
                $success = true;
                $mensaje = "Nomina de empresa eliminada con exito";
            } else {
                $success = false;
                $mensaje = "Error al eliminar nomina de empresa";
            }
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
        }
    }
}
