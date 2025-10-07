<?php

namespace App\Http\Requests;


class StoreEventReviewRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trip_day_event_id' => ['nullable', 'exists:trips_days_events,id'],
            'xid' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'trip_day_event_id.exists' => 'O evento selecionado não existe.',
            'rating.required' => 'A avaliação é obrigatória.',
            'rating.integer' => 'A avaliação deve ser um número inteiro.',
            'rating.min' => 'A avaliação deve ser no mínimo 0.',
            'rating.max' => 'A avaliação deve ser no máximo 5.',
            'comment.max' => 'O comentário não pode ter mais de 5000 caracteres.',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}
