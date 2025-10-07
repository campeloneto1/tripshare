<?php

namespace App\Http\Requests;


class UpdateVoteAnswerRequest extends BaseRequest
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
            'vote_option_id' => 'sometimes|required|integer|exists:vote_options,id',
        ];
    }

    public function messages(): array
    {
        return [
            'vote_option_id.required' => 'A opção de voto é obrigatória.',
            'vote_option_id.exists' => 'A opção selecionada não existe.',
        ];
    }
}
