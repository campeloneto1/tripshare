<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoteOptionRequest extends BaseRequest
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
            'title' => 'required|string|max:255',
            'json_data' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da opção é obrigatório.',
            'json_data.array' => 'O campo json_data deve ser um array.',
        ];
    }
}
