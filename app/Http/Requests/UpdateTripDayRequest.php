<?php

namespace App\Http\Requests;

class UpdateTripDayRequest extends BaseRequest
{
     public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'exists:trips,id'],
            'date' => ['sometimes', 'date'],
        ];
    }

     public function messages(): array
    {
        return [
            'trip_id.exists' => 'A viagem selecionada não existe.',
            'date.date' => 'Informe uma data válida.',
        ];
    }
}
