<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostLikeRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'post_id.required' => 'O ID do post é obrigatório.',
            'post_id.exists' => 'O post informado não existe.',
        ];
    }
}
