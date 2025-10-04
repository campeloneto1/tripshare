<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:150',
            'description' => 'sometimes|nullable|string|max:255',
            'initial_date' => 'sometimes|required|date|after_or_equal:today',
            'final_date' => 'sometimes|nullable|date|after_or_equal:initial_date',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O título da trip é obrigatório.',
            'name.max' => 'O título deve ter no máximo 150 caracteres.',
            'description.max' => 'A descrição deve ter no máximo 255 caracteres.',
            'initial_date.required' => 'A data de início é obrigatória.',
            'initial_date.date' => 'Informe uma data válida para início.',
            'initial_date.after_or_equal' => 'A data de início deve ser hoje ou futura.',
            'final_date.date' => 'Informe uma data válida para fim.',
            'final_date.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
        ];
    }
}
