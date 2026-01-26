<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPasswordBase
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset Password - ' . config('app.name'))
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Kami menerima permintaan untuk mereset password akun Anda.')
            ->line('')
            ->line('**Detail Akun:**')
            ->line('Email: ' . $notifiable->email)
            ->line('ID Karyawan: ' . ($notifiable->employee_id ?? '-'))
            ->line('')
            ->line('Silakan klik tombol di bawah ini untuk membuat password baru:')
            ->action('Reset Password', $resetUrl)
            ->line('')
            ->line('Link reset password ini akan kedaluwarsa dalam **60 menit**.')
            ->line('')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.')
            ->line('')
            ->salutation('Salam,  ' . "\n" . 'Tim ' . config('app.name'));
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        return url(config('app.url').route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
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
            'user_id' => $notifiable->id,
            'user_name' => $notifiable->name,
            'user_email' => $notifiable->email,
            'reset_sent_at' => now()->toDateTimeString(),
        ];
    }
}
