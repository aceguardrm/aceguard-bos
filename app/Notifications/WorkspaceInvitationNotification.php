<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationNotification extends Notification
{
    public function __construct(
        private string $workspaceName,
        private string $role,
        private string $acceptUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation to '.$this->workspaceName.' on AceGuard BOS')
            ->greeting('You have been invited to AceGuard BOS')
            ->line('Workspace: '.$this->workspaceName)
            ->line('Your workspace role: '.$this->role)
            ->action('Review invitation', $this->acceptUrl)
            ->line('This invitation expires in seven days.')
            ->line('If you were not expecting this invitation, you can ignore it.');
    }
}
