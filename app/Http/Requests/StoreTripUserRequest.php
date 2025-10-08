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
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,participant',
            'transport_type' => 'nullable|in:car,plane,bus,train,other',
            'transport_datetime' => 'nullable|date',
        ];
    }

     public function messages(): array
    {
        return [
            'user_id.required' => 'O ID do usuário é obrigatório.',
            'user_id.exists' => 'O usuário informado não existe.',
            'role.required' => 'O papel do usuário é obrigatório.',
            'role.in' => 'O papel do usuário deve ser admin ou participant.',
            'transport_type.in' => 'O tipo de transporte deve ser car, plane, bus, train ou other.',
            'transport_datetime.date' => 'A data e hora do transporte deve ser uma data válida.',
        ];
    }
}
