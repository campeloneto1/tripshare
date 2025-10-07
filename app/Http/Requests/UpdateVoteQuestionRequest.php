<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVoteQuestionRequest extends BaseRequest
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
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:city,event',
            'start_at' => 'sometimes|required|date|before:end_at',
            'end_at' => 'sometimes|required|date|after:start_at',
            'is_closed' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da votação é obrigatório.',
            'type.in' => 'O tipo da votação deve ser "city" ou "event".',
            'start_at.before' => 'A data de início deve ser antes da data de término.',
            'end_at.after' => 'A data de término deve ser depois da data de início.',
            'is_closed.boolean' => 'O status de fechamento deve ser verdadeiro ou falso.',
        ];
    }
}
