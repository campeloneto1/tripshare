<?php

namespace App\Http\Requests;


class SyncPermissionsRequest extends BaseRequest
{
   public function authorize(): bool
    {
        // Altere conforme sua lógica de autorização (por ex: usar Gate/Policies)
        return true;
    }

    public function rules(): array
    {
        return [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'permission_ids.required' => 'O campo permission_ids é obrigatório.',
            'permission_ids.array' => 'O campo permission_ids deve ser um array.',
            'permission_ids.*.integer' => 'Cada ID de permissão deve ser um número inteiro.',
            'permission_ids.*.exists' => 'Algum ID de permissão informado não existe na base de dados.',
        ];
    }
}
