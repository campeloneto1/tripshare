<?php

namespace App\Services;

use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use App\Repositories\VoteAnswerRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoteAnswerService
{
    public function __construct(private VoteAnswerRepository $repository) {}

    public function list(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?VoteAnswer
    {
        return $this->repository->find($id);
    }

    public function store(array $data): VoteAnswer
    {
        return DB::transaction(function () use ($data) {
            $option = VoteOption::with('question')->find($data['vote_option_id']);

            if (!$option) {
                throw ValidationException::withMessages(['vote_option_id' => 'Opção não encontrada.']);
            }

            $question = $option->question;

            if (!$question) {
                throw ValidationException::withMessages(['vote_option_id' => 'Pergunta relacionada não encontrada.']);
            }

            // Verifica se a votação está aberta
            if ($question->is_closed) {
                throw ValidationException::withMessages(['vote_question_id' => 'Esta votação já está fechada.']);
            }

            // Verifica se a votação já começou
            if (now()->isBefore($question->start_at)) {
                throw ValidationException::withMessages(['vote_question_id' => 'Esta votação ainda não começou.']);
            }

            // Verifica se a votação já terminou
            if (now()->isAfter($question->end_at)) {
                throw ValidationException::withMessages(['vote_question_id' => 'Esta votação já terminou.']);
            }

            // Verifica se o usuário já votou nesta pergunta
            $existingVote = VoteAnswer::where('vote_question_id', $question->id)
                ->where('user_id', $data['user_id'])
                ->first();

            if ($existingVote) {
                throw ValidationException::withMessages(['vote_question_id' => 'Você já votou nesta pergunta.']);
            }

            // Adiciona vote_question_id aos dados
            $data['vote_question_id'] = $question->id;

            return $this->repository->create($data);
        });
    }

    public function update(VoteAnswer $voteAnswer, array $data): VoteAnswer
    {
        return DB::transaction(function () use ($voteAnswer, $data) {
            // Verifica se a votação ainda está aberta
            if ($voteAnswer->question->is_closed) {
                throw ValidationException::withMessages(['vote_question_id' => 'Não é possível alterar voto em votação fechada.']);
            }

            // Se está mudando a opção, verifica se pertence à mesma pergunta
            if (isset($data['vote_option_id']) && $data['vote_option_id'] !== $voteAnswer->vote_option_id) {
                $newOption = VoteOption::find($data['vote_option_id']);

                if (!$newOption || $newOption->vote_question_id !== $voteAnswer->vote_question_id) {
                    throw ValidationException::withMessages(['vote_option_id' => 'A nova opção deve pertencer à mesma pergunta.']);
                }
            }

            return $this->repository->update($voteAnswer, $data);
        });
    }

    public function delete(VoteAnswer $voteAnswer): bool
    {
        return DB::transaction(function () use ($voteAnswer) {
            // Verifica se a votação ainda está aberta
            if ($voteAnswer->question->is_closed) {
                throw ValidationException::withMessages(['vote_question_id' => 'Não é possível remover voto de votação fechada.']);
            }

            return $this->repository->delete($voteAnswer);
        });
    }
}
