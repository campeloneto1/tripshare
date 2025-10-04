<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateViagemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|required|string|max:150',
            'descricao' => 'sometimes|nullable|string|max:255',
            'data_inicio' => 'sometimes|required|date|after_or_equal:today',
            'data_fim' => 'sometimes|nullable|date|after_or_equal:data_inicio',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título da viagem é obrigatório.',
            'titulo.max' => 'O título deve ter no máximo 150 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'Informe uma data válida para início.',
            'data_inicio.after_or_equal' => 'A data de início deve ser hoje ou futura.',
            'data_fim.date' => 'Informe uma data válida para fim.',
            'data_fim.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
        ];
    }
}
