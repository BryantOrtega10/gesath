<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmpresaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fkTipoCompania' => 'required',
            'fkTipoAportante' => 'required',
            'razonSocial' => 'required',
            'sigla' => 'required',
            'dominio' => 'required',
            'fkTipoIdentificacion' => 'required',
            // 'fkActividadEconomica' => 'required',
            'fkUbicacion' => 'required',
            'direccion' => 'required',
            'paginaWeb' => 'required',
            /* 'telefonoFijo' => 'required',
            'celular' => 'required',
            'email1' => 'required', */
            'nom_cen_cost' => 'required',
            'documento' => 'required',
            'digitoVerificacion' => 'required',
            'fkTercero_ARL' => 'required',
            'periodo' => 'required',
            'diasCesantias' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'fkTipoCompania.required' => 'Tipo de compañia requerido',
            'fkTipoAportante.required' => 'Tipo de aportante requerido',
            'razonSocial.required' => 'Razón social requerida',
            'sigla.required' => 'Sigla requerida',
            'dominio.required' => 'Dominio requerido',
            'fkTipoIdentificacion.required' => 'Tipo de identificación requerido',
            // 'fkActividadEconomica.required' => 'Actividad económica requerida',
            'fkUbicacion.required' => 'Ubicación requerida',
            'direccion.required' => 'Dirección requerida',
            'paginaWeb.required' => 'Página web requerida',
            /* 'telefonoFijo.required' => 'Teléfono fijo requerido',
            'celular.required' => 'Número de celular requerido',
            'email1.required' => 'Correo requerido', */
            'nom_cen_cost.required' => 'Centro de costo requerido',
            'documento.required' => 'NIT requerido',
            'digitoVerificacion.required' => 'Dígito de verificación requerido',
            'fkTercero_ARL.required' => 'Tercero ARL requerido',
            'periodo.required' => 'Periodo de nómina requerido',
            'diasCesantias.required' => 'Días de cesantías requerido'
        ];
    }

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json([
            'error_code'=> 'VALIDATION_ERROR', 
            'message'   => 'Asegurese de enviar los datos correctos', 
            'errors'    => $validator->errors()
        ], 422));
    }
}
