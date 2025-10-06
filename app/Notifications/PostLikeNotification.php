<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostLikeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $liker,
        public Post $post
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
            'type' => 'post_like',
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_username' => $this->liker->username,
            'liker_avatar' => $this->liker->getAvatar(),
            'post_id' => $this->post->id,
            'post_content' => substr($this->post->content ?? '', 0, 100),
            'message' => "{$this->liker->name} curtiu seu post"
        ];
    }
}
