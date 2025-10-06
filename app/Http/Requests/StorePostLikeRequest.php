<?php

namespace App\Http\Requests;


class StorePostLikeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
            'post_id' => $this->route('post')->id,  
        ]);
    }

    public function messages(): array
    {
        return [
            'post_id.required' => 'O post é obrigatório.',
            'post_id.exists' => 'Post não encontrado.',
            'post_id.integer' => 'ID do post deve ser um número.',
            'user_id.required' => 'Usuário não autenticado.',
            'user_id.exists' => 'Usuário inválido.',
            'user_id.integer' => 'ID do usuário deve ser um número.',
        ];
    }
}
