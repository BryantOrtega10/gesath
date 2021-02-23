<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundHttpException;
use App\Http\Requests\TercerosRequest;
use App\TercerosModel;
use App\ActividadEconomicaModel;
use App\TipoIdentificacionModel;
use App\TipoAfiliacionModel;
use App\TerceroUbicacionModel;
use App\EstadoModel;
use App\Ubicacion;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use League\Csv\Reader;
use SplTempFileObject;          


class TercerosController extends Controller
{
    public function index() {
        $terceros = TercerosModel::all();
        $usu = UsuarioController::dataAdminLogueado();
    	return view('/terceros.terceros', [
            'terceros' => $terceros,
            'dataUsu' => $usu
        ]);
    }

    function getFormAdd() {
        $actEconomicas = ActividadEconomicaModel::all();
        $tipoIdent = TipoIdentificacionModel::all();
        $tipoAfl = TipoAfiliacionModel::all();
        $estados = EstadoModel::where("estadoActivo", 1)->get();
        $ubicaciones = Ubicacion::where('fkTipoUbicacion', 2)->get();
    	return view('/terceros.addTercero', [
            'actEconomicas' => $actEconomicas,
            'tipoIdent' => $tipoIdent,
            'tipoAfl' => $tipoAfl,
            'estados' => $estados,
            'ubicaciones' => $ubicaciones
        ]);
    }

    public function create(TercerosRequest $request) {
        $terceros = new TercerosModel();
        $terceros->privado = $request->privado;
        $terceros->fk_actividad_economica = $request->fk_actividad_economica;
        $terceros->naturalezaTributaria = $request->naturalezaTributaria;
        if ($request->naturalezaTributaria === 'Juridico') { // Juridica
            $terceros->razonSocial = $request->razonSocial;
        } else { // Natural
            $terceros->primerNombre = $request->primerNombre;
            $terceros->segundoNombre = $request->segundoNombre;
            $terceros->primerApellido = $request->primerApellido;
            $terceros->segundoApellido = $request->segundoApellido;
        }
        $terceros->fkTipoIdentificacion = $request->fkTipoIdentificacion;
        $terceros->numeroIdentificacion = $request->numeroIdentificacion;
        $terceros->digitoVer = $request->digitoVer;
        $terceros->fkEstado = $request->fkEstado;
        $terceros->direccion = $request->direccion;
        $terceros->telefono = $request->telefono;
        $terceros->fax = $request->fax;
        $terceros->correo = $request->correo;
        $terceros->codigoTercero = $request->codigoTercero;
        $terceros->fkTipoAporteSeguridadSocial = $request->fkTipoAporteSeguridadSocial;
        $terceros->codigoSuperIntendencia = $request->codigoSuperIntendencia;
        $insert = $terceros->save();
        if ($insert) {
            $this->agregarUbicacionesTercero($terceros->idTercero, $request->fkUbicacion);
            $success = true;
            $mensaje = "Tercero creado exitosamente";
        } else {
            $success = false;
            $mensaje = "Error al crear tercero";
        }
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }

    public function edit($id) {
        try {
            $tercero = TercerosModel::findOrFail($id);
            $actEconomicas = ActividadEconomicaModel::all();
            $tipoIdent = TipoIdentificacionModel::all();
            $tipoAfl = TipoAfiliacionModel::all();
            $estados = EstadoModel::where("estadoActivo", 1)->get();
            $ubicaciones = Ubicacion::where('fkTipoUbicacion', 2)->get();
            $ubicacionesTercero = TerceroUbicacionModel::where('id_ter', $id)->get();
            $cantUbis = $ubicacionesTercero->count();
            $vistaUbis = view('/terceros/ubicaciones.ubiTerceroEdit', [
                'ubicaciones' => $ubicaciones,
                'tercero' => $tercero,
                'ubisTer' => $ubicacionesTercero
            ]);
            return view('/terceros.editTercero', [
                'tercero' => $tercero,
                'actEconomicas' => $actEconomicas,
                'tipoIdent' => $tipoIdent,
                'tipoAfl' => $tipoAfl,
                'estados' => $estados,
                'ubicaciones' => $ubicaciones,
                'DOMUbis' => $vistaUbis,
                "cantUbis" => $cantUbis
            ]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un tercero con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $tercero = TercerosModel::findOrFail($id);
            $actEconomicas = ActividadEconomicaModel::all();
            $tipoIdent = TipoIdentificacionModel::all();
            $tipoAfl = TipoAfiliacionModel::all();
            $estados = EstadoModel::where("estadoActivo", 1)->get();
            $ubicaciones = Ubicacion::where('fkTipoUbicacion', 2)->get();
            $ubicacionesTercero = TerceroUbicacionModel::where('id_ter', $id)->get();
            $cantUbis = $ubicacionesTercero->count();
            $vistaUbis = view('/terceros/ubicaciones.ubiTerceroEdit', [
                'ubicaciones' => $ubicaciones,
                'tercero' => $tercero,
                'ubisTer' => $ubicacionesTercero
            ]);
            return view('/terceros.detalleTercero', [
                'tercero' => $tercero,
                'actEconomicas' => $actEconomicas,
                'tipoIdent' => $tipoIdent,
                'tipoAfl' => $tipoAfl,
                'estados' => $estados,
                'ubicaciones' => $ubicaciones,
                'DOMUbis' => $vistaUbis,
                "cantUbis" => $cantUbis
            ]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un tercero con este ID"]);
		}
    }

    public function update(Request  $request, $id) {
        try {
		    $terceros = TercerosModel::findOrFail($id);
            $terceros->privado = $request->privado;
            $terceros->fk_actividad_economica = $request->fk_actividad_economica;
            if ($request->naturalezaTributaria === 'Juridico') { // Juridica
                $terceros->razonSocial = $request->razonSocial;
            } else { // Natural
                $terceros->primerNombre = $request->primerNombre;
                $terceros->segundoNombre = $request->segundoNombre;
                $terceros->primerApellido = $request->primerApellido;
                $terceros->segundoApellido = $request->segundoApellido;
            }
            $terceros->naturalezaTributaria = $request->naturalezaTributaria;
            $terceros->fkTipoIdentificacion = $request->fkTipoIdentificacion;
            $terceros->numeroIdentificacion = $request->numeroIdentificacion;
            $terceros->digitoVer = $request->digitoVer;
            $terceros->fkEstado = $request->fkEstado;
            $terceros->direccion = $request->direccion;
            $terceros->telefono = $request->telefono;
            $terceros->fax = $request->fax;
            $terceros->correo = $request->correo;
            $terceros->codigoTercero = $request->codigoTercero;
            $terceros->fkTipoAporteSeguridadSocial = $request->fkTipoAporteSeguridadSocial;
            $terceros->codigoSuperIntendencia = $request->codigoSuperIntendencia;
            $actualizar = $terceros->save();
            if ($actualizar) {
                $this->agregarUbicacionesTercero($id, $request->fkUbicacion);
                $success = true;
                $mensaje = "Tercero actualizado correctamente";
            } else {
                $success = false;
                $mensaje = "Error al actualizar tercero";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un tercero con este ID"]);
		}
    }

    public function delete($id) {
        try{
			$tercero = TercerosModel::findOrFail($id);
			if($tercero->delete()){
				$success = true;
				$mensaje = "Tercero eliminado con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar tercero";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un descuento con este ID"]);
		}
    }

    public function ubiTerceroDOM($idDom) {
        $ubicacion = Ubicacion::where('fkTipoUbicacion', 2)->get();
        return view('/terceros/ubicaciones.ubiTerceroAdd', [
            'ubicaciones' => $ubicacion,
            'idDom' => $idDom
        ]);
    }

    public function selectUbicacionesTerceros($act, $tipoUbi, $idTercero, $idDom) {
        switch($act) {
            case "1":
                
            break;
            case "2":
                $tercero = TercerosModel::where('idTercero', $idTercero)->first();
                $ubicacion = Ubicacion::where('fkTipoUbicacion', $tipoUbi)->get();
                return view('/terceros/ubicaciones.ubiTerceroEdit', [
                    'ubicaciones' => $ubicacion,
                    'tercero' => $tercero,
                    'idDom' => $idDom
                ]);
            break;
            case "3":
                $tercero = TercerosModel::where('idTercero', $idTercero)->first();
                $ubicacion = Ubicacion::where('fkTipoUbicacion', $tipoUbi)->get();
                return view('/terceros/ubicaciones.ubiTerceroDetail', [
                    'ubicaciones' => $ubicacion,
                    'tercero' => $tercero,
                    'idDom' => $idDom
                ]);
            break;
            default:
            break;
        }
		
    }
    
    public function agregarUbicacionesTercero($idTer, $arrUbis) {
        TerceroUbicacionModel::where('id_ter', $idTer)->delete();
        if(isset($arrUbis)){
            foreach( $arrUbis as $ubis) {
                TerceroUbicacionModel::insert([
                    'id_ter' => $idTer,
                    'id_ubi' => $ubis                
                ]);
            }
        }        
    }

    public function actualizarUbicacionesTerceros($idTer, $arrUbis) {
        TerceroUbicacionModel::where('id_ter', $idTer)->delete();
        $this->agregarUbicacionesTercero($idTer, $arrUbis);
    }
    public function exportar(){

        $terceros = DB::table("tercero","t")
        ->select("t.*","ae.nombre as actividadEco", "ti.nombre as tipoidentificacion", "tas.nombre as tipoAporteSS")
        ->join("actividadeconomica as ae","ae.idactividadEconomica","=","t.fk_actividad_economica")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=","t.fkTipoIdentificacion")
        ->join("tipoaporteseguridadsocial as tas","tas.idTipoAporteSeguridadSocial", "=","t.fkTipoAporteSeguridadSocial")
        ->get();
        $arrDef = array([
            "idTercero",
            "Tipo Identificación",
            "Numero Identificación",
            "Digito de Verificación",
            "Razon Social",
            "Primer Nombre",
            "Segundo Nombre",
            "Primer Apellido",
            "Segundo Apellido",
            "Naturaleza Tributaria",
            "Actividad Economica",
            "Dirección",
            "Teléfono",
            "Fax",
            "Correo",
            "Codigo Tercero",
            "Aporte seguridad social"

        ]);
        foreach ($terceros as $tercero){
            array_push($arrDef, [
                $tercero->idTercero,
                $tercero->tipoidentificacion,
                $tercero->numeroIdentificacion,
                $tercero->digitoVer,
                $tercero->razonSocial,
                $tercero->primerNombre,
                $tercero->segundoNombre,
                $tercero->primerApellido,
                $tercero->segundoApellido,
                $tercero->naturalezaTributaria,
                $tercero->actividadEco,
                $tercero->direccion,
                $tercero->telefono,
                $tercero->fax,
                $tercero->correo,
                $tercero->codigoTercero,
                $tercero->tipoAporteSS
            ]);
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=conceptos.csv');

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertAll($arrDef);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->output('terceros.csv');
    }   
}
