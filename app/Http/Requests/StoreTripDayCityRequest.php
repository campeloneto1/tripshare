<?php

namespace App\Http\Requests;

class StoreTripDayCityRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_day_id' => 'required|exists:trips_days,id',
            'city_name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'osm_id' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|size:2',
            'order' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'trip_day_id.required' => 'O ID do dia da viagem é obrigatório.',
            'trip_day_id.exists' => 'O dia da viagem informado não existe.',
            'city_name.required' => 'O nome da cidade é obrigatório.',
            'city_name.max' => 'O nome da cidade não pode ter mais de 255 caracteres.',
            'lat.required' => 'A latitude é obrigatória.',
            'lat.numeric' => 'A latitude deve ser um número.',
            'lat.between' => 'A latitude deve estar entre -90 e 90.',
            'lon.required' => 'A longitude é obrigatória.',
            'lon.numeric' => 'A longitude deve ser um número.',
            'lon.between' => 'A longitude deve estar entre -180 e 180.',
            'country_code.size' => 'O código do país deve ter exatamente 2 caracteres.',
            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem mínima é 1.',
        ];
    }
}
