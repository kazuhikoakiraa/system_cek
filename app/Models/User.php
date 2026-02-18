<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
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
        return $this->avatar_url;
    }

    /**
     * Avatar URL accessor menggunakan Laravel 11 style
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolveAvatarUrl($this->getRawOriginal('avatar')),
        );
    }

    /**
     * Normalize avatar path before persisting to database.
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value) => $this->normalizeAvatarPath($this->extractAvatarValue($value)),
        );
    }

    /**
     * Resolve avatar URL from various input formats
     * 
     * @suppress PhanUndeclaredVariable
     */
    protected function resolveAvatarUrl(mixed $avatar): string
    {
        $avatar = $this->extractAvatarValue($avatar);

        // Early return jika avatar kosong atau bukan string
        if (empty($avatar) || !is_string($avatar)) {
            return $this->getDefaultAvatarUrl();
        }
        
        $path = $this->normalizeAvatarPath($avatar);

        if (empty($path)) {
            return $this->getDefaultAvatarUrl();
        }

        // Jika sudah URL lengkap (http, https, atau data URI)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                if (filter_var($path, FILTER_VALIDATE_URL) === false) {
                    return $this->getDefaultAvatarUrl();
                }
            }

            return $path;
        }

        $path = ltrim($path, '/');

        if ($path === '') {
            return $this->getDefaultAvatarUrl();
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        try {
            return Storage::disk('public')->url($path);
        } catch (\Throwable $e) {
            return $this->getDefaultAvatarUrl();
        }
    }

    protected function normalizeAvatarPath(mixed $avatar): ?string
    {
        $avatar = $this->extractAvatarValue($avatar);

        if (empty($avatar) || !is_string($avatar)) {
            return null;
        }

        $path = str_replace('\\', '/', trim($avatar));

        if ($path === '') {
            return null;
        }

        // Handle JSON array string such as ["avatars/file.jpg"].
        if (str_starts_with($path, '[')) {
            $decoded = json_decode($path, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $first = Arr::first($decoded);

                if (is_string($first)) {
                    $path = $first;
                }
            }
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $urlHost = parse_url($path, PHP_URL_HOST);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);

            // Keep third-party URLs as-is.
            if (is_string($urlHost) && is_string($appHost) && ! str_contains($urlHost, $appHost)) {
                return $path;
            }

            $urlPath = parse_url($path, PHP_URL_PATH);

            if (is_string($urlPath) && $urlPath !== '') {
                $urlPath = ltrim($urlPath, '/');
                $urlPath = preg_replace('#^public/#', '', $urlPath) ?? $urlPath;
                $urlPath = preg_replace('#^storage/#', '', $urlPath) ?? $urlPath;

                return ltrim($urlPath, '/');
            }

            return $path;
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        $path = preg_replace('#^/?public/#', '', $path) ?? $path;
        $path = preg_replace('#^/?storage/#', '', $path) ?? $path;

        return ltrim($path, '/');
    }

    protected function extractAvatarValue(mixed $avatar): mixed
    {
        if (is_array($avatar)) {
            return Arr::first($avatar);
        }

        return $avatar;
    }

    protected function getDefaultAvatarUrl(): string
    {
        $name = trim((string) ($this->name ?? 'User'));

        if ($name === '') {
            $name = 'User';
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=FFFFFF&background=0D8ABC&size=128&rounded=true';
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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
