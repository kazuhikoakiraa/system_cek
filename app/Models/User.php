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
        'shift',
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

    protected function resolveAvatarUrl(mixed $avatar): string
    {
        $avatarPath = is_string($avatar) ? trim($avatar) : '';

        if ($avatarPath === '') {
            return $this->getDefaultAvatarUrl();
        }

        $avatarPath = ltrim($avatarPath, '/');

        if (Str::startsWith($avatarPath, ['http://', 'https://', 'data:'])) {
            return $avatarPath;
        }

        if (Str::startsWith($avatarPath, 'public/')) {
            $avatarPath = Str::after($avatarPath, 'public/');
        }

        if (Str::startsWith($avatarPath, 'storage/')) {
            return asset($avatarPath);
        }

        return url('storage/' . $avatarPath);
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
            e($initials).
            "</text>".
            "</svg>";

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
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