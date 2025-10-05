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
            'date' => ['required', 'date'],
        ];
    }

     public function messages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.date' => 'Informe uma data válida.',
        ];
    }
}
