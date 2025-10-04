<?php

namespace App\Http\Requests;

class UpdateTripUserRequest extends BaseRequest
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
            'role' => 'sometimes|required|in:admin,participant',
        ];
    }

     public function messages(): array
    {
        return [
            'role.required' => 'O papel do usuário é obrigatório.',
            'role.in' => 'O papel do usuário deve ser admin ou participant.',
        ];
    }
}
