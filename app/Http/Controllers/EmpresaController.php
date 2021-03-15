<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\EmpresaRequest;
use App\EmpresaModel;
use App\ActividadEconomicaModel;
use App\TipoAportanteModel;
use App\TipoCompaniaModel;
use App\TipoIdentificacionModel;
use App\Ubicacion;
use App\CentroCostoEmpresaModel;
use App\TercerosModel;
use App\NominaEmpresaModel;
class EmpresaController extends Controller
{
    public function index() {
        $empresas = EmpresaModel::orderBy("razonSocial")->get();
        $usu = UsuarioController::dataAdminLogueado();
    	return view('/empresas.empresas', [
            'empresas' => $empresas,
            'dataUsu' => $usu
        ]);
    }
    public function indexPermisos($idEmpresa) {

        $empresas = DB::table("empresa")->where("idEmpresa","=",$idEmpresa)->first();
        
        $usu = UsuarioController::dataAdminLogueado();
        $permisos = explode(",",$empresas->permisosGenerales);
    	return view('/empresas.permisos', [
            'empresas' => $empresas,
            'dataUsu' => $usu,
            "permisos" => $permisos,
            "idEmpresa" => $idEmpresa
        ]);
    } 
    public function updatePermisos(Request $req){

            
        
       
        $permisosGenerales = "";
        if(isset($req->permiso1)){
            $permisosGenerales.="1,";
        }
        else{
            $permisosGenerales.="0,";
        }

        if(isset($req->permiso2)){
            $permisosGenerales.="1,";
        }
        else{
            $permisosGenerales.="0,";
        }

        if(isset($req->permiso3)){
            $permisosGenerales.="1,";
        }
        else{
            $permisosGenerales.="0,";
        }

        if(isset($req->permiso4)){
            $permisosGenerales.="1,";
        }
        else{
            $permisosGenerales.="0,";
        }

        if(isset($req->permiso5)){
            $permisosGenerales.="1,";
        }
        else{
            $permisosGenerales.="0,";
        }

        if(isset($req->permiso6)){
            $permisosGenerales.="1,";
        }
        else{
            $permisosGenerales.="0,";
        }

        if(isset($req->permiso7)){
            $permisosGenerales.="1";
        }
        else{
            $permisosGenerales.="0";
        }

        $actualizar = DB::table("empresa")->where("idEmpresa","=",$req->idEmpresa)->update([
            "permisosGenerales" => $permisosGenerales
        ]);
        
        
        

        if ($actualizar) {
            $success = true;
            $mensaje = "Empresa actualizada correctamente";
        } else {
            $success = false;
            $mensaje = "Error al actualizar empresa";
        }
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }
    


    function getFormAdd() {
        $actEconomicas = ActividadEconomicaModel::all();
        $tipoIdent = TipoIdentificacionModel::all();
        $tipoApor = TipoAportanteModel::all();
        $tipoComp = TipoCompaniaModel::all();
        $ubicaciones = Ubicacion::all();
        $terceroArl = TercerosModel::where('fk_actividad_economica', '=', 1)->get();
        $paises = Ubicacion::where('fkTipoUbicacion', '=', 1)->get();
        $periocidad = DB::table("periocidad")->whereIn("per_id",[4,3])->get();

    	return view('/empresas.addEmpresa', [
            'actEconomicas' => $actEconomicas,
            'paises' => $paises,
            'terceroArl' => $terceroArl,
            'tipoIdent' => $tipoIdent,
            'tipoApor' => $tipoApor,
            'tipoComp' => $tipoComp,
            'ubicaciones' => $ubicaciones
        ]);
    }

    public function create(EmpresaRequest $request) {
        $empresas = new EmpresaModel();

        // Validar subida de logo de empresa

        if ($request->hasFile("logoEmpresa")) {
            $image = $request->file('logoEmpresa');
            $name = "imagen_" . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = storage_path('app/public/logosEmpresas');
            $image->move($destinationPath, $name);
            $empresas->logoEmpresa = $name;
        } else {
            $empresas->logoEmpresa = '';
        }

        $empresas->fkTipoCompania = $request->fkTipoCompania;
        $empresas->fkTipoAportante = $request->fkTipoAportante;
        $empresas->razonSocial = $request->razonSocial;
        $empresas->sigla = $request->sigla;
        $empresas->dominio = $request->dominio;
        $empresas->fkTipoIdentificacion = $request->fkTipoIdentificacion;
        
        $empresas->representanteLegal = $request->representanteLegal;
        $empresas->docRepresentante = $request->docRepresentante;
        $empresas->numDocRepresentante = $request->numDocRepresentante;
        
        
        $empresas->fkUbicacion = $request->fkUbicacion;
        $empresas->direccion = $request->direccion;
        $empresas->paginaWeb = $request->paginaWeb;
        $empresas->telefonoFijo = $request->telefonoFijo;
        $empresas->celular = $request->celular;
        $empresas->email1 = $request->email1;
        $empresas->email2 = $request->email2;
        $empresas->documento = $request->documento;
        $empresas->digitoVerificacion = $request->digitoVerificacion;
        $empresas->fkTercero_ARL = $request->fkTercero_ARL;
        $empresas->exento = $request->exento;
        $empresas->vacacionesNegativas = $request->vacacionesNegativas;
        $insert = $empresas->save();
        if ($insert) {
            // Creamos centro de costo
            if(isset($request->nom_cen_cost)){
                $cenCost = new CentroCostoEmpresaModel();
                $cenCost->nombre = $request->nom_cen_cost;
                $cenCost->fkEmpresa = $empresas->idempresa;
                $cenCost->id_uni_centro = $empresas->id_uni_centro;
                $cenCost->save();
            }
            

            //Creamos nomina
            $nomEmpresa = new NominaEmpresaModel();
            $nomEmpresa->nombre = $request->razonSocial;
            $nomEmpresa->tipoPeriodo = 'DIAS';
            $nomEmpresa->periodo = $request->periodo;
            $nomEmpresa->diasCesantias = $request->diasCesantias;
            $nomEmpresa->fkEmpresa = $empresas->idempresa;
            $nomEmpresa->id_uni_nomina = $empresas->id_uni_nomina;
            $nomEmpresa->save();

            $success = true;
            $mensaje = "Empresa creado exitosamente";
        } else {
            $success = false;
            $mensaje = "Error al crear tercero";
        }
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }

    public function edit($id) {
        try {
            $empresa = EmpresaModel::select(
                '*',
                'empresa.fkUbicacion as ubi',
                DB::raw('(select fkUbicacion from ubicacion where idubicacion = ubi) as ubi_dos'),
                DB::raw('(select fkUbicacion from ubicacion where idubicacion = ubi_dos) as ubi_tres')
            )
            ->where('empresa.idempresa', $id)
            ->first();

            $actEconomicas = ActividadEconomicaModel::all();
            $tipoIdent = TipoIdentificacionModel::all();
            $tipoApor = TipoAportanteModel::all();
            $tipoComp = TipoCompaniaModel::all();
            $terceroArl = TercerosModel::where('fk_actividad_economica', '=', 1)->get();
            $paises = Ubicacion::where('fkTipoUbicacion', '=', 1)->get();
            $deptos = Ubicacion::where('fkTipoUbicacion', '=', 2)->get();
            $ciudades = Ubicacion::where('fkTipoUbicacion', '=', 3)->get();
            return view('/empresas.editEmpresa', [
                'empresa' => $empresa,
                'actEconomicas' => $actEconomicas,
                'paises' => $paises,
                'deptos' => $deptos,
                'ciudades' => $ciudades,
                'terceroArl' => $terceroArl,
                'tipoIdent' => $tipoIdent,
                'tipoApor' => $tipoApor,
                'tipoComp' => $tipoComp
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una empresa con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $empresa = EmpresaModel::select(
                '*',
                'empresa.fkUbicacion as ubi',
                DB::raw('(select fkUbicacion from ubicacion where idubicacion = ubi) as ubi_dos'),
                DB::raw('(select fkUbicacion from ubicacion where idubicacion = ubi_dos) as ubi_tres')
            )
            ->where('empresa.idempresa', $id)
            ->first();
            $actEconomicas = ActividadEconomicaModel::all();
            $tipoIdent = TipoIdentificacionModel::all();
            $tipoApor = TipoAportanteModel::all();
            $tipoComp = TipoCompaniaModel::all();
            $paises = Ubicacion::where('fkTipoUbicacion', '=', 1)->get();
            $deptos = Ubicacion::where('fkTipoUbicacion', '=', 2)->get();
            $ciudades = Ubicacion::where('fkTipoUbicacion', '=', 3)->get();
            $terceroArl = TercerosModel::where('fk_actividad_economica', '=', 1)->get();
            return view('/empresas.detalleEmpresa', [
                'empresa' => $empresa,
                'actEconomicas' => $actEconomicas,
                'terceroArl' => $terceroArl,
                'tipoIdent' => $tipoIdent,
                'tipoApor' => $tipoApor,
                'tipoComp' => $tipoComp,
                'paises' => $paises,
                'deptos' => $deptos,
                'ciudades' => $ciudades,
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una empresa con este ID"]);
		}
    }

    public function update(Request  $request, $id) {
        try {
            $empresas = EmpresaModel::findOrFail($id);
            
            if ($request->hasFile("logoEmpresa")) {
                $image = $request->file('logoEmpresa');
                $name = "imagen_" . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = storage_path('app/public/logosEmpresas');
                $image->move($destinationPath, $name);
                $empresas->logoEmpresa = $name;
            } else {
                $empresas->logoEmpresa = $empresas->logoEmpresa;
            }

            $empresas->fkTipoCompania = $request->fkTipoCompania;
            $empresas->fkTipoAportante = $request->fkTipoAportante;
            $empresas->razonSocial = $request->razonSocial;
            $empresas->sigla = $request->sigla;
            $empresas->dominio = $request->dominio;
            $empresas->fkTipoIdentificacion = $request->fkTipoIdentificacion;

            $empresas->representanteLegal = $request->representanteLegal;
            $empresas->docRepresentante = $request->docRepresentante;
            $empresas->numDocRepresentante = $request->numDocRepresentante;
            
            $empresas->fkUbicacion = $request->fkUbicacion;
            $empresas->direccion = $request->direccion;
            $empresas->paginaWeb = $request->paginaWeb;
            $empresas->telefonoFijo = $request->telefonoFijo;
            $empresas->celular = $request->celular;
            $empresas->email1 = $request->email1;
            $empresas->email2 = $request->email2;
            $empresas->documento = $request->documento;
            $empresas->digitoVerificacion = $request->digitoVerificacion;
            $empresas->fkTercero_ARL = $request->fkTercero_ARL;
            $empresas->exento = $request->exento;
            $empresas->vacacionesNegativas = $request->vacacionesNegativas;
            $actualizar = $empresas->save();
            

            if ($actualizar) {
                $success = true;
                $mensaje = "Empresa actualizada correctamente";
            } else {
                $success = false;
                $mensaje = "Error al actualizar empresa";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una empresa con este ID"]);
		}
    }

    public function delete($id) {
        try{
            $empresa = EmpresaModel::findOrFail($id);
            NominaEmpresaModel::where('fkEmpresa', $id)->delete();
			if($empresa->delete()){
				$success = true;
				$mensaje = "Empresa eliminada con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar empresa";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una empresa con este ID"]);
		}
    }
}