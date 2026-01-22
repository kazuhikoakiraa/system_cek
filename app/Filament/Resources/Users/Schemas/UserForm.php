<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengguna')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('Foto Profile')
                            ->image()
                            ->avatar()
                            ->directory('avatars')
                            ->maxSize(1024)
                            ->imageEditor()
                            ->circleCropper()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('employee_id')
                            ->label('ID Karyawan')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Section::make('Informasi Pekerjaan')
                    ->schema([
                        TextInput::make('department')
                            ->label('Departemen')
                            ->maxLength(100),
                        Select::make('shift')
                            ->label('Shift')
                            ->options([
                                'pagi' => 'Pagi',
                                'siang' => 'Siang',
                                'malam' => 'Malam',
                            ])
                            ->native(false),
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Keamanan')
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Terverifikasi Pada')
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
