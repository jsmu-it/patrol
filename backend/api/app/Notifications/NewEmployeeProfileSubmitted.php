<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewEmployeeProfileSubmitted extends Notification
{
    use Queueable;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'username' => $this->user->username,
            'project_id' => $this->user->active_project_id,
            'project_name' => $this->user->activeProject ? $this->user->activeProject->name : null,
            'message' => "Karyawan baru {$this->user->name} ({$this->user->username}) menunggu persetujuan.",
            'action_url' => route('admin.users.edit', $this->user->id),
        ];
    }
}
