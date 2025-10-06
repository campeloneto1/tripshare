<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:post_comments,id'],
            'content' => ['required', 'string', 'max:1000'],
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
            'parent_id.exists' => 'O comentário pai informado não existe.',
            'content.required' => 'O conteúdo do comentário é obrigatório.',
            'content.max' => 'O comentário não pode ter mais de 1000 caracteres.',
        ];
    }
}
