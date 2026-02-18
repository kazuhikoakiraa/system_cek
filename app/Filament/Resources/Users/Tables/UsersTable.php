<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Foto')
                    ->circular()
                    ->checkFileExistence(false),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('employee_id')
                    ->label('ID Karyawan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department')
                    ->label('Departemen')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->label('Departemen')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
                Action::make('reset_password')
                    ->label('Reset Kata Sandi')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Link Reset Kata Sandi')
                    ->modalDescription(fn ($record) => "Link reset kata sandi akan dikirim ke email: {$record->email}")
                    ->modalSubmitActionLabel('Kirim Email')
                    ->action(function ($record) {
                        $status = Password::sendResetLink(
                            ['email' => $record->email]
                        );

                        if ($status === Password::RESET_LINK_SENT) {
                            Notification::make()
                                ->title('Email Terkirim')
                                ->body("Link reset kata sandi berhasil dikirim ke {$record->email}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal Mengirim Email')
                                ->body('Terjadi kesalahan saat mengirim email. Silakan coba lagi.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Pengguna Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
