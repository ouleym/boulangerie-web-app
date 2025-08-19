<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
<<<<<<< HEAD
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
=======
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)

class CommandesNotification extends Notification
{
    use Queueable;

<<<<<<< HEAD
    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
=======
    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Nouvelle commande reçue')
                    ->greeting('Bonjour '.$notifiable->name)
                    ->line('Vous avez une nouvelle commande.')
                    ->line('Commande n°: '.$this->order->id)
                    ->action('Voir la commande', url('/admin/commandes/'.$this->order->id))
                    ->line('Merci d’utiliser notre application de boulangerie !');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'Nouvelle commande reçue.',
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
        ];
    }
}
