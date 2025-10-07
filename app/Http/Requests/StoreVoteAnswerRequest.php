<?php

namespace App\Http\Requests;

use App\Models\VoteOption;
use Illuminate\Foundation\Http\FormRequest;

class StoreVoteAnswerRequest extends BaseRequest
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
            'vote_option_id' => [
                'required',
                'integer',
                'exists:votes_options,id',
                function ($attribute, $value, $fail) {
                    // Verifica se a opção pertence à pergunta da rota (parent binding)
                    $voteQuestionId = $this->route('vote');

                    if ($voteQuestionId) {
                        $option = VoteOption::find($value);

                        if ($option && $option->vote_question_id != $voteQuestionId) {
                            $fail('A opção selecionada não pertence a esta votação.');
                            return;
                        }
                    }

                    // Validações de negócio
                    $option = VoteOption::with('question')->find($value);

                    if (!$option) {
                        return;
                    }

                    $question = $option->question;

                    if (!$question) {
                        $fail('A pergunta relacionada à opção não foi encontrada.');
                        return;
                    }

                    if ($question->is_closed) {
                        $fail('Esta votação já está fechada.');
                        return;
                    }

                    if (now()->isBefore($question->start_at)) {
                        $fail('Esta votação ainda não começou.');
                        return;
                    }

                    if (now()->isAfter($question->end_at)) {
                        $fail('Esta votação já terminou.');
                        return;
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vote_option_id.required' => 'A opção de voto é obrigatória.',
            'vote_option_id.exists' => 'A opção selecionada não existe.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}
