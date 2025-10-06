<?php

namespace App\Http\Requests;

class UpdatePostCommentRequest extends BaseRequest
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
            'content' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'O conteúdo do comentário é obrigatório.',
            'content.max' => 'O comentário não pode ter mais de 1000 caracteres.',
        ];
    }
}
