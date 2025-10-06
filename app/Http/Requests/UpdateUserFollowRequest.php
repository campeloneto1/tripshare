<?php

namespace App\Http\Requests;

class UpdateUserFollowRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status informado é inválido. Somente "accepted" é permitido.',
        ];
    }
}
