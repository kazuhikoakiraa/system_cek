<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends VerifyEmailBase
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        // Force URL to use APP_URL scheme and host
        URL::forceRootUrl(config('app.url'));
        
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email Anda - ' . config('app.name'))
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Terima kasih telah mendaftar di ' . config('app.name') . '.')
            ->line('Akun Anda telah dibuat dengan detail berikut:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**ID Karyawan:** ' . ($notifiable->employee_id ?? '-'))
            ->line('**Departemen:** ' . ($notifiable->department ?? '-'))
            ->line('')
            ->line('Untuk melengkapi proses pendaftaran, silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda:')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('')
            ->line('Link verifikasi ini akan kedaluwarsa dalam **60 menit**.')
            ->line('')
            ->line('Jika Anda tidak membuat akun ini, abaikan email ini.')
            ->line('')
            ->line('Terima kasih,')
            ->salutation('Tim ' . config('app.name'))
            ->line('')
            ->line('---')
            ->line('**Catatan Keamanan:**')
            ->line('Jangan bagikan link ini kepada siapa pun. Link ini hanya berlaku untuk Anda.')
            ->line('Jika Anda mengalami masalah mengklik tombol "Verifikasi Email", salin dan tempel URL berikut ke browser Anda:')
            ->line($verificationUrl);
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
            'verification_sent_at' => now()->toDateTimeString(),
        ];
    }
}