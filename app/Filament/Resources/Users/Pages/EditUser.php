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

            // Toggle active status
            Action::make('toggle_status')
                ->label(fn () => $this->record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn () => $this->record->is_active ? 'Nonaktifkan User?' : 'Aktifkan User?')
                ->modalDescription(fn () => 
                    $this->record->is_active 
                        ? "User {$this->record->name} tidak akan bisa login setelah dinonaktifkan."
                        : "User {$this->record->name} akan dapat login setelah diaktifkan."
                )
                ->action(function () {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                    
                    Notification::make()
                        ->success()
                        ->title('Status Diupdate')
                        ->body("User berhasil " . ($this->record->is_active ? 'diaktifkan' : 'dinonaktifkan'))
                        ->send();
                    
                    $this->refreshFormData(['is_active']);
                }),

            // Delete action
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus User?')
                ->modalDescription(fn () => "User {$this->record->name} dan semua data terkait akan dihapus permanen.")
                ->successNotificationTitle('User berhasil dihapus'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'User berhasil diupdate!';
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