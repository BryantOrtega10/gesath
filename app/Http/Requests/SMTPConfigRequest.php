<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SMTPConfigRequest extends FormRequest
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
            'smtp_host' => 'required',
            'smtp_port' => 'required|min:2',
            'smtp_username' => 'required|email',
            'smtp_password' => 'required',
            'smtp_encrypt' => 'required|min:3',
            'smtp_mail_envia' => 'required|email',
            'smtp_nombre_envia' => 'required|min:3'
        ];
    }

    public function messages()
    {
        return [
            'smtp_host.required' => 'Host SMTP requerido',
            'smtp_port.required' => 'Puerto de host SMTP requerido',
            'smtp_port.min' => 'Mínimo 2 números en el puerto SMTP',
            'smtp_username.required' => 'Usuario SMTP requerido',
            'smtp_username.email' => 'Debe ingresar un correo valido',
            'smtp_password.required' => 'Contraseña SMTP requerida',
            'smtp_encrypt.required' => 'Método de encriptación requerido',
            'smtp_encrypt.min' => 'Mínimo 3 caracteres en el método de encriptación',
            'smtp_mail_envia.required' => 'Correo de quien envía es requerido',
            'smtp_mail_envia.email' => 'Ingrese un correo valido',
            'smtp_nombre_envia.required' => 'Nombre de quien envía requerido',
            'smtp_nombre_envia.min' => 'Mínimo 3 caracteres en el nombre de quien envía',
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