<?php

namespace App\Notifications;

use App\Models\VoteOption;
use App\Models\VoteQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VoteEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VoteQuestion $question,
        public ?VoteOption $winningOption = null,
        public int $totalVotes = 0
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->winningOption
            ? "A enquete '{$this->question->title}' terminou! Venceu: '{$this->winningOption->title}' com {$this->winningOption->votes_count} votos."
            : "A enquete '{$this->question->title}' terminou sem votos.";

        return [
            'type' => 'vote_ended',
            'vote_question_id' => $this->question->id,
            'vote_question_title' => $this->question->title,
            'winning_option_id' => $this->winningOption?->id,
            'winning_option_title' => $this->winningOption?->title,
            'winning_votes_count' => $this->winningOption?->votes_count ?? 0,
            'total_votes' => $this->totalVotes,
            'closed_at' => $this->question->closed_at?->toDateTimeString(),
            'message' => $message
        ];
    }
}
