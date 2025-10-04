<?php

namespace App\Http\Requests;

class UpdateTripDayCityRequest extends BaseRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        return [
            'city_name' => 'sometimes|required|string|max:255',
            'lat' => 'sometimes|required|numeric|between:-90,90',
            'lon' => 'sometimes|required|numeric|between:-180,180',
            'osm_id' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|size:2',
            'order' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'city_name.required' => 'O nome da cidade é obrigatório.',
            'city_name.max' => 'O nome da cidade não pode ter mais de 255 caracteres.',
            'lat.numeric' => 'A latitude deve ser um número.',
            'lat.between' => 'A latitude deve estar entre -90 e 90.',
            'lon.numeric' => 'A longitude deve ser um número.',
            'lon.between' => 'A longitude deve estar entre -180 e 180.',
            'country_code.size' => 'O código do país deve ter exatamente 2 caracteres.',
            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem mínima é 1.',
        ];
    }
}
