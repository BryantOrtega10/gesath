<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CodDiagnosticoModel;
use App\Http\Requests\CodDiagnosticoRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CodDiagnosticoController extends Controller
{
    public function index() {
        $usu = UsuarioController::dataAdminLogueado();
        return view('/codDiagnostico.codDiagnostico', [
            'dataUsu' => $usu
        ]);
    }

    public function getAll() {
        return CodDiagnosticoModel::all();
    }

    public function getFormAdd() {
        return view('/codDiagnostico.addCod');
    }

    public function create(CodDiagnosticoRequest $request) {
        $codigo = new CodDiagnosticoModel();
        $codigo->idCodDiagnostico = $request->idCodDiagnostico;
        $codigo->nombre = $request->nombre;
        $save = $codigo->save();
        if ($save) {
            $success = true;
            $mensaje = "Código de diagnóstico agregada correctamente";
        } else {
            $success = true;
            $mensaje = "Error al agregar código de diagnóstico";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function edit($id) {
        try {
            $codigo = CodDiagnosticoModel::findOrFail($id);
            return view('/codDiagnostico.editCod', [
                'codigos' => $codigo
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un código de diagnóstico con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $codigo = CodDiagnosticoModel::findOrFail($id);
            return view('/codDiagnostico.detailCod', [
                'codigos' => $codigo
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un código de diagnóstico con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $codigo = CodDiagnosticoModel::findOrFail($id);
            $codigo->idCodDiagnostico = $request->idCodDiagnostico;
            $codigo->nombre = $request->nombre;
            $save = $codigo->save();
            if ($save) {
                $success = true;
                $mensaje = "Código de diagnóstico actualizada correctamente";
            } else {
                $success = true;
                $mensaje = "Error al actualizar código de diagnóstico";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un código de diagnóstico con este ID"]);
		}
    }

    public function delete($id) {
        try{
            $codigo = CodDiagnosticoModel::findOrFail($id);
            if($codigo->delete()){
                $success = true;
                $mensaje = "Código de diagnóstico eliminada con exito";
            } else {
                $success = false;
                $mensaje = "Error al eliminar código de diagnóstico";
            }
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un código de diagnóstico con este ID"]);
        }
    }
}
