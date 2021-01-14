<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\CalendarioModel;
use App\Http\Requests\CalendarioRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CalendarioController extends Controller
{
    public function index() {
        $calendario = CalendarioModel::all();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/calendario.calendario', [
            "calendarios" => $calendario,
            'dataUsu' => $usu
        ]);
    }

    public function getFormAdd() {
        return view('/calendario/addCalendario');
    }

    public function create(CalendarioRequest $request) {
        $calendario = new CalendarioModel();
        $calendario->fecha = $request->fecha;
        $calendario->fechaInicioSemana = $request->fechaInicioSemana;
        $calendario->fechaFinSemana = $request->fechaFinSemana;
        $save = $calendario->save();
        if ($save) {
            $success = true;
            $mensaje = "Calendario agregado correctamente";
        } else {
            $success = true;
            $mensaje = "Error al agregar calendario";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function edit($id) {
        try {
            $calendario = CalendarioModel::findOrFail($id);
            return view('/calendario.editCalendario', [
                'calendario' => $calendario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $calendario = CalendarioModel::findOrFail($id);
            return view('/calendario.detailCalendario', [
                'calendario' => $calendario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $calendario = CalendarioModel::findOrFail($id);
            $calendario->fecha = $request->fecha;
            $calendario->fechaInicioSemana = $request->fechaInicioSemana;
            $calendario->fechaFinSemana = $request->fechaFinSemana;
            $save = $calendario->save();
            if ($save) {
                $success = true;
                $mensaje = "Calendario actualizado correctamente";
            } else {
                $success = true;
                $mensaje = "Error al actualizar calendario";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
		}
    }

    public function delete($id) {
        try{
			$calendario = CalendarioModel::findOrFail($id);
			if($calendario->delete()){
				$success = true;
				$mensaje = "Calendario eliminado con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar calendario";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
		}
    }
}
