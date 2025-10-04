<?php

namespace App\Http\Requests;


class StoreTripDayRequest extends BaseRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        return [
            'trip_id' => ['required', 'exists:trips,id'],
            'date' => ['required', 'date'],
        ];
    }

     public function messages(): array
    {
        return [
            'trip_id.required' => 'A viagem é obrigatória.',
            'trip_id.exists' => 'A viagem selecionada não existe.',
            'date.required' => 'A data é obrigatória.',
            'date.date' => 'Informe uma data válida.',
        ];
    }
}
