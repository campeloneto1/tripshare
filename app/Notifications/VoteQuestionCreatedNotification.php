<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\VoteQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VoteQuestionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VoteQuestion $question,
        public User $creator
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vote_question_created',
            'vote_question_id' => $this->question->id,
            'vote_question_title' => $this->question->title,
            'vote_question_type' => $this->question->type,
            'creator_id' => $this->creator->id,
            'creator_name' => $this->creator->name,
            'creator_username' => $this->creator->username,
            'creator_avatar' => $this->creator->getAvatar(),
            'start_at' => $this->question->start_at?->toDateTimeString(),
            'end_at' => $this->question->end_at?->toDateTimeString(),
            'message' => "{$this->creator->name} criou a enquete '{$this->question->title}'. Vote até " . $this->question->end_at?->format('d/m/Y H:i') . "!"
        ];
    }
}
