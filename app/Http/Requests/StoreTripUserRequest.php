<?php

namespace App\Http\Requests;

class StoreTripUserRequest extends BaseRequest
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
            'trip_id' => 'required|exists:trips,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,participant',
        ];
    }

     public function messages(): array
    {
        return [
            'trip_id.required' => 'O ID da viagem é obrigatório.',
            'trip_id.exists' => 'A viagem informada não existe.',
            'user_id.required' => 'O ID do usuário é obrigatório.',
            'user_id.exists' => 'O usuário informado não existe.',
            'role.required' => 'O papel do usuário é obrigatório.',
            'role.in' => 'O papel do usuário deve ser admin ou participant.',
        ];
    }
}
