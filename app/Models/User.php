<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'employee_id',
        'department',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    /**
     * Avatar URL accessor menggunakan Laravel 11 style
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolveAvatarUrl($this->avatar),
        );
    }

    /**
     * Resolve avatar URL from various input formats
     * 
     * @suppress PhanUndeclaredVariable
     */
    protected function resolveAvatarUrl(mixed $avatar): string
    {
        // Early return jika avatar kosong atau bukan string
        if (empty($avatar) || !is_string($avatar)) {
            return $this->getDefaultAvatarUrl();
        }
        
        // Assign ke variabel dan trim
        $path = trim($avatar);
        
        // Return default jika kosong setelah trim
        if ($path === '') {
            return $this->getDefaultAvatarUrl();
        }
        
        // Hilangkan leading slash
        $path = ltrim($path, '/');
        
        // Jika sudah URL lengkap (http, https, atau data URI)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        // Jika path mengandung 'public/', hilangkan prefix tersebut
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7); // strlen('public/') = 7
        }

        // Jika path mengandung 'storage/', gunakan asset helper
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        // Default: gunakan Storage URL dengan disk public
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        
        return $disk->url($path);
    }

    protected function getDefaultAvatarUrl(): string
    {
        $name = trim((string) ($this->name ?? ''));
        $name = $name !== '' ? $name : 'User';

        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = '';
        
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $initials .= mb_substr($part, 0, 1);
            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        $initials = mb_strtoupper($initials !== '' ? $initials : 'U');

        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='128' height='128' viewBox='0 0 128 128'>".
            "<rect width='128' height='128' rx='64' fill='#0D8ABC'/>".
            "<text x='50%' y='52%' text-anchor='middle' dominant-baseline='middle' font-family='ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial' font-size='52' fill='#fff'>".
            htmlspecialchars($initials, ENT_XML1 | ENT_QUOTES, 'UTF-8').
            "</text>".
            "</svg>";

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasVerifiedEmail();
    }

    /**
     * Get user's full name with employee ID
     */
    public function getFullIdentifierAttribute(): string
    {
        return $this->employee_id 
            ? "{$this->name} ({$this->employee_id})"
            : $this->name;
    }

    /**
     * Check if user email is verified
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }
}