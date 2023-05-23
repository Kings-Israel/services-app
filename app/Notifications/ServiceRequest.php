<?php

namespace App\Notifications;

use App\Models\Service;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequest extends Notification
{
    use Queueable;

    public $user;
    public $service;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Service $service)
    {
        $this->user = $user;
        $this->service = $service;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->service->title.' Service Request.')
                    ->greeting('Greetings,')
                    ->line('A new service delivery request has been made for the service, '.$this->service->title. ', by '.$this->user->first_name.' '.$this->user->last_name.'.')
                    ->line('Thank you for using our application');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user' => $this->user->first_name.' '.$this->user->last_name,
            'service' => $this->service->title
        ];
    }
}
