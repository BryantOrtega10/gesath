<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearUsuarioAdminRequest extends FormRequest
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
            'username' => 'required|min:8',
            'password' => [
                'sometimes',
                'required', 
                'min:6', 
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/', 
            ],
            'fkRol' => 'required',
            'primerNombre' => 'required|min:3',
            'primerApellido' => 'required|min:3'
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Usuario requerido',
            'username.min' => 'Mínimo 8 caracteres en el nombre de usuario',
            'email.required' => 'Correo electrónico requerido',
            'email.email' => ' Ingrese un correo electrónico valido',
            'password.required' => 'Contraseña requerida',
            'password.min' => 'Mínimo 6 caracteres que contenga letras y números',
            'password.regex' => 'Mínimo 6 caracteres que contenga letras mayúsculas y minúsculas, números y un caracter especial (Por ejemplo $, #, &, %)',
            'fkRol.required' => 'Rol requerido',
            'fkEmpresa.required' => 'Debe asignar una empresa al nuevo usuario',
            'foto.required' => 'Foto requerida',
            'primerNombre.required' => 'Nombre requerido',
            'primerNombre.min' => 'Mínimo 3 caracteres en el nombre',
            'primerNombre.required' => 'Apellido requerido',
            'primerApellido.max' => 'Mínimo 3 caracteres en el apellido',
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
