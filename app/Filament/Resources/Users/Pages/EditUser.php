<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Resend verification email action
            Action::make('resend_verification')
                ->label('Kirim Ulang Verifikasi')
                ->icon('heroicon-o-envelope')
                ->color('warning')
                ->visible(fn () => !$this->record->hasVerifiedEmail())
                ->requiresConfirmation()
                ->modalHeading('Kirim Ulang Email Verifikasi?')
                ->modalDescription(fn () => "Email verifikasi akan dikirim ke: {$this->record->email}")
                ->modalSubmitActionLabel('Kirim')
                ->action(function () {
                    try {
                        $this->record->sendEmailVerificationNotification();

                        Notification::make()
                            ->success()
                            ->title('Email Terkirim')
                            ->body("Email verifikasi telah dikirim ke {$this->record->email}")
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Mengirim Email')
                            ->body("Error: {$e->getMessage()}")
                            ->send();
                    }
                }),

            // Delete action
            DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus Pengguna?')
                ->modalDescription(fn () => "Pengguna {$this->record->name} dan semua data terkait akan dihapus permanen.")
                ->successNotificationTitle('Pengguna berhasil dihapus'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengguna berhasil diperbarui!';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jangan simpan password jika kosong
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Pastikan user hanya memiliki 1 role (ambil role terakhir jika ada multiple)
        if ($this->record->roles()->count() > 1) {
            $latestRole = $this->record->roles()->latest()->first();
            $this->record->syncRoles([$latestRole->name]);
        }

        // Kirim notifikasi jika email berubah
        if ($this->record->wasChanged('email')) {
            // Reset email verification jika email berubah
            $this->record->update(['email_verified_at' => null]);
            
            // Kirim email verifikasi baru
            $this->record->sendEmailVerificationNotification();

            Notification::make()
                ->info()
                ->title('Email Berubah')
                ->body("Email verifikasi baru telah dikirim ke {$this->record->email}")
                ->send();
        }
    }
}