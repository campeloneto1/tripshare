<?php

namespace App\Http\Requests;


class SyncPermissoesRequest extends BaseRequest
{
   public function authorize(): bool
    {
        // Altere conforme sua lógica de autorização (por ex: usar Gate/Policies)
        return true;
    }

    public function rules(): array
    {
        return [
            'permissao_ids' => 'required|array',
            'permissao_ids.*' => 'integer|exists:permissoes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'permissao_ids.required' => 'O campo permissao_ids é obrigatório.',
            'permissao_ids.array' => 'O campo permissao_ids deve ser um array.',
            'permissao_ids.*.integer' => 'Cada ID de permissão deve ser um número inteiro.',
            'permissao_ids.*.exists' => 'Algum ID de permissão informado não existe na base de dados.',
        ];
    }
}
