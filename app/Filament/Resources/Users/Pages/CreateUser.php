<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect ke list setelah create
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        // Pastikan user hanya memiliki 1 role (ambil role terakhir jika ada multiple)
        if ($user->roles()->count() > 1) {
            $latestRole = $user->roles()->latest()->first();
            $user->syncRoles([$latestRole->name]);
        }

        // Kirim email verifikasi otomatis
        if (!$user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();

                // Notifikasi sukses ke admin
                Notification::make()
                    ->success()
                    ->title('User Berhasil Dibuat')
                    ->body("Email verifikasi telah dikirim ke {$user->email}")
                    ->duration(5000)
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->send();
            } catch (\Exception $e) {
                // Notifikasi jika email gagal terkirim
                Notification::make()
                    ->warning()
                    ->title('User Dibuat, Email Gagal Terkirim')
                    ->body("User berhasil dibuat, namun email verifikasi gagal terkirim. Error: {$e->getMessage()}")
                    ->duration(8000)
                    ->icon('heroicon-o-exclamation-triangle')
                    ->actions([
                        Action::make('retry')
                            ->button()
                            ->label('Kirim Ulang')
                            ->url(UserResource::getUrl('edit', ['record' => $user])),
                    ])
                    ->send();
            }
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'User berhasil dibuat!';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values jika belum diisi
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $data;
    }
}