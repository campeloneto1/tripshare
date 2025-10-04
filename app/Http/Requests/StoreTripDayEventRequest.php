<?php

namespace App\Http\Requests;

class StoreTripDayEventRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_day_city_id' => 'required|exists:trip_day_cities,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:hotel,restaurant,attraction,transport,other',
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'xid' => 'nullable|string|max:255',
            'source_api' => 'nullable|string|max:255',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'order' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'trip_day_city_id.required' => 'O ID da cidade do dia é obrigatório.',
            'trip_day_city_id.exists' => 'A cidade do dia informada não existe.',
            'name.required' => 'O nome do evento é obrigatório.',
            'name.max' => 'O nome do evento não pode ter mais de 255 caracteres.',
            'type.required' => 'O tipo do evento é obrigatório.',
            'type.in' => 'O tipo do evento deve ser: hotel, restaurant, attraction, transport ou other.',
            'lat.numeric' => 'A latitude deve ser um número.',
            'lat.between' => 'A latitude deve estar entre -90 e 90.',
            'lon.numeric' => 'A longitude deve ser um número.',
            'lon.between' => 'A longitude deve estar entre -180 e 180.',
            'xid.max' => 'O XID não pode ter mais de 255 caracteres.',
            'source_api.max' => 'A fonte da API não pode ter mais de 255 caracteres.',
            'start_time.date_format' => 'O horário de início deve estar no formato HH:MM.',
            'end_time.date_format' => 'O horário de fim deve estar no formato HH:MM.',
            'end_time.after_or_equal' => 'O horário de fim deve ser igual ou posterior ao horário de início.',
            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem mínima é 1.',
        ];
    }
}
