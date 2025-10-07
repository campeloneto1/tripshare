<?php

namespace App\Http\Requests;


class StoreVoteQuestionRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        return [
            'votable_type' => 'required|string|in:App\Models\TripDay,App\Models\TripDayCity',
            'votable_id' => 'required|integer|exists:' . $this->input('votable_type') . ',id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:city,event',
            'start_at' => 'required|date|before:end_at',
            'end_at' => 'required|date|after:start_at',
        ];
    }

    public function messages(): array
    {
        return [
            'votable_type.required' => 'O tipo de entidade da votação é obrigatório.',
            'votable_type.in' => 'O tipo de entidade deve ser TripDay ou TripDayCity.',
            'votable_id.required' => 'O ID da entidade é obrigatório.',
            'votable_id.exists' => 'A entidade selecionada não existe.',
            'title.required' => 'O título da votação é obrigatório.',
            'type.required' => 'O tipo da votação é obrigatório.',
            'type.in' => 'O tipo da votação deve ser "city" ou "event".',
            'start_at.required' => 'A data de início é obrigatória.',
            'start_at.before' => 'A data de início deve ser antes da data de término.',
            'end_at.required' => 'A data de término é obrigatória.',
            'end_at.after' => 'A data de término deve ser depois da data de início.',
        ];
    }
}
