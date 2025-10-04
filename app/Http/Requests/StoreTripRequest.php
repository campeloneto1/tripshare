<?php

namespace App\Http\Requests;


class StoreTripRequest extends BaseRequest
{
     public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_public' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O título da trip é obrigatório.',
            'name.max' => 'O título deve ter no máximo 150 caracteres.',
            'description.max' => 'A descrição deve ter no máximo 255 caracteres.',
            'start_date.required' => 'A data de início é obrigatória.',
            'start_date.date' => 'Informe uma data válida para início.',
            'start_date.after_or_equal' => 'A data de início deve ser hoje ou futura.',
            'end_date.date' => 'Informe uma data válida para fim.',
            'end_date.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
        ];
    }
}
