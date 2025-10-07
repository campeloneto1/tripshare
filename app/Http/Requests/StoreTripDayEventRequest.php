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
            // Campos do Place (serão processados pelo repository)
            'xid' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'type' => 'required|in:hotel,restaurant,attraction,transport,other',
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'source_api' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',

            // Campos do TripDayEvent (específicos da visita)
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'order' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ];
    }

    public function messages(): array
    {
        return [
            'xid.required' => 'O XID do local é obrigatório.',
            'xid.max' => 'O XID não pode ter mais de 255 caracteres.',
            'name.required' => 'O nome do local é obrigatório.',
            'name.max' => 'O nome do local não pode ter mais de 255 caracteres.',
            'type.required' => 'O tipo do local é obrigatório.',
            'type.in' => 'O tipo do local deve ser: hotel, restaurant, attraction, transport ou other.',
            'lat.numeric' => 'A latitude deve ser um número.',
            'lat.between' => 'A latitude deve estar entre -90 e 90.',
            'lon.numeric' => 'A longitude deve ser um número.',
            'lon.between' => 'A longitude deve estar entre -180 e 180.',
            'source_api.max' => 'A fonte da API não pode ter mais de 255 caracteres.',
            'start_time.date_format' => 'O horário de início deve estar no formato HH:MM.',
            'end_time.date_format' => 'O horário de fim deve estar no formato HH:MM.',
            'end_time.after_or_equal' => 'O horário de fim deve ser igual ou posterior ao horário de início.',
            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem mínima é 1.',
            'notes.string' => 'As notas devem ser uma string.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço mínimo é 0.',
            'currency.size' => 'A moeda deve ter exatamente 3 caracteres.',
        ];
    }
}
