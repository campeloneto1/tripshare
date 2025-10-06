<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_id' => ['required', 'exists:posts,id'],
            'parent_id' => ['nullable', 'exists:post_comments,id'],
            'content' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'post_id.required' => 'O ID do post é obrigatório.',
            'post_id.exists' => 'O post informado não existe.',
            'parent_id.exists' => 'O comentário pai informado não existe.',
            'content.required' => 'O conteúdo do comentário é obrigatório.',
            'content.max' => 'O comentário não pode ter mais de 1000 caracteres.',
        ];
    }
}
