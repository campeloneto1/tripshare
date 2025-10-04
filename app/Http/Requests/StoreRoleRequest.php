<?php

namespace App\Http\Requests;


class StoreRoleRequest extends BaseRequest
{
   
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:roles,nome',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do role é obrigatório.',
            'name.unique' => 'Este role já existe.',
            'name.max' => 'O nome do role deve ter no máximo 100 caracteres.',

            'description.max' => 'O nome do role deve ter no máximo 255 caracteres.',
        ];
    }
}
