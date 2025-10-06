<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $commenter,
        public Post $post,
        public PostComment $comment
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
            'type' => 'post_comment',
            'commenter_id' => $this->commenter->id,
            'commenter_name' => $this->commenter->name,
            'commenter_username' => $this->commenter->username,
            'commenter_avatar' => $this->commenter->getAvatar(),
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
            'comment_content' => substr($this->comment->content, 0, 100),
            'message' => "{$this->commenter->name} comentou no seu post"
        ];
    }
}
