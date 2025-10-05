<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $acceptedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'follow_accepted',
            'user_id' => $this->acceptedBy->id,
            'user_name' => $this->acceptedBy->name,
            'user_username' => $this->acceptedBy->username,
            'user_avatar' => $this->acceptedBy->getAvatar(),
            'message' => "{$this->acceptedBy->name} aceitou seu pedido de seguidor"
        ];
    }
}
