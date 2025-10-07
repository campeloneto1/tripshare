<?php

namespace App\Http\Requests;

class UpdateEventReviewRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'min:0', 'max:5'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'rating.integer' => 'A avaliação deve ser um número inteiro.',
            'rating.min' => 'A avaliação deve ser no mínimo 0.',
            'rating.max' => 'A avaliação deve ser no máximo 5.',
            'comment.max' => 'O comentário não pode ter mais de 5000 caracteres.',
        ];
    }
}
