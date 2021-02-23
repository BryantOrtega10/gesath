<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TercerosRequest extends FormRequest
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
            'privado' => 'required',
            'fk_actividad_economica' => 'required',
            'naturalezaTributaria' => 'required',
            'fkTipoIdentificacion' => 'required',
            'numeroIdentificacion' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'privado.required' => 'Se debe indicar si el tercero es privado o no',
            'fk_actividad_economica.required' => 'Actividad economica requerida',
            'naturalezaTributaria.required' => 'Naturaleza tributaria requerida',
            'fkTipoIdentificacion.required' => 'Tipo de identificacion requerido',
            'numeroIdentificacion.required' => 'Numero de identificacion requerido',
            'fkEstado.required' => 'Estado requerido',
            'direccion.required' => 'Direccion requerida',
            'fkUbicacion.required' => 'Ubicacion requerida',
            'telefono.required' => 'Telefono requerido',
            'fax.required' => 'Fax requerido',
            'correo.required' => 'Correo requerido',
            'correo.email' => 'Debe ingresar un correo valido',
            'codigoTercero.required' => 'Codigo de tercero requerido',
            'fkTipoAporteSeguridadSocial.required' => 'Tipo de aporte de seguridad social requerido',
            'codigoSuperIntendencia.required' => 'Codigo de superintendencia requerido'
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
