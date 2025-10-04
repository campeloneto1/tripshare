<?php

namespace App\Http\Requests;


class UpdatePermissionRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')->id;

        return [
            'name' => 'required|string|max:100|unique:permissions,nome,' . $permissionId,
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da permissão é obrigatório.',
            'name.unique' => 'Esta permissão já existe.',
            'name.max' => 'O nome da permissão deve ter no máximo 100 caracteres.',
            'description.max' => 'O nome da permissão deve ter no máximo 255 caracteres.',
        ];
    }
}
