<?php

namespace App\Http\Requests;


class UpdatePermissaoRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissaoId = $this->route('permissao')->id;

        return [
            'nome' => 'required|string|max:100|unique:permissoes,nome,' . $permissaoId,
            'descricao' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da permissão é obrigatório.',
            'nome.unique' => 'Esta permissão já existe.',
            'nome.max' => 'O nome da permissão deve ter no máximo 100 caracteres.',
        ];
    }
}
