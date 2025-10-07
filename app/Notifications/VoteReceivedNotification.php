<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\VoteAnswer;
use App\Models\VoteQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VoteReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VoteQuestion $question,
        public User $voter,
        public VoteAnswer $answer
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
            'type' => 'vote_received',
            'vote_question_id' => $this->question->id,
            'vote_question_title' => $this->question->title,
            'voter_id' => $this->voter->id,
            'voter_name' => $this->voter->name,
            'voter_username' => $this->voter->username,
            'voter_avatar' => $this->voter->getAvatar(),
            'vote_option_title' => $this->answer->option->title,
            'message' => "{$this->voter->name} votou em '{$this->answer->option->title}' na enquete '{$this->question->title}'"
        ];
    }
}
