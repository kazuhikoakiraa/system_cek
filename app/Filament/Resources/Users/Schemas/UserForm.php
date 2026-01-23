<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Avatar Section - Full Width
                Section::make()
                    ->components([
                        Grid::make(1)
                            ->components([
                                FileUpload::make('avatar')
                                    ->label('Foto Profil')
                                    ->image()
                                    ->avatar()
                                    ->directory('avatars')
                                    ->maxSize(2048) // 2MB
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->helperText('Upload foto profil (maks. 2MB). Format: JPG, PNG, WEBP')
                                    ->alignCenter(),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Personal Information Section
                Section::make('Informasi Pribadi')
                    ->description('Data pribadi pengguna')
                    ->icon('heroicon-o-user')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('contoh: John Doe')
                                    ->autocomplete('name')
                                    ->prefixIcon('heroicon-o-user')
                                    ->columnSpan(2),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('contoh: john.doe@company.com')
                                    ->autocomplete('email')
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->suffixIcon('heroicon-o-at-symbol'),

                                TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('contoh: +62 812-3456-7890')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->telRegex('/^[+]?[0-9\s\-\(\)]+$/'),
                            ]),
                    ])
                    ->collapsible(),

                // Work Information Section
                Section::make('Informasi Pekerjaan')
                    ->description('Data kepegawaian dan jabatan')
                    ->icon('heroicon-o-briefcase')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('employee_id')
                                    ->label('ID Karyawan')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->placeholder('contoh: EMP-001')
                                    ->prefixIcon('heroicon-o-identification')
                                    ->helperText('ID unik untuk setiap karyawan')
                                    ->alphaDash(),

                                TextInput::make('department')
                                    ->label('Departemen')
                                    ->maxLength(100)
                                    ->placeholder('contoh: IT, Production, Quality Control')
                                    ->prefixIcon('heroicon-o-building-office')
                                    ->datalist([
                                        'IT',
                                        'Production',
                                        'Quality Control',
                                        'Maintenance',
                                        'Warehouse',
                                        'HR',
                                        'Finance',
                                    ]),

                                Select::make('shift')
                                    ->label('Shift Kerja')
                                    ->options([
                                        'pagi' => 'Shift Pagi (07:00 - 15:00)',
                                        'siang' => 'Shift Siang (15:00 - 23:00)',
                                        'malam' => 'Shift Malam (23:00 - 07:00)',
                                    ])
                                    ->native(false)
                                    ->placeholder('Pilih shift kerja')
                                    ->prefixIcon('heroicon-o-clock')
                                    ->searchable(),

                                Select::make('roles')
                                    ->label('Role / Jabatan')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->prefixIcon('heroicon-o-shield-check')
                                    ->helperText('Pilih satu atau lebih role untuk pengguna')
                                    ->placeholder('Pilih role'),
                            ]),
                    ])
                    ->collapsible(),

                // Security Section
                Section::make('Keamanan & Akses')
                    ->description('Pengaturan password dan verifikasi email')
                    ->icon('heroicon-o-lock-closed')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->placeholder('Minimal 8 karakter')
                                    ->prefixIcon('heroicon-o-key')
                                    ->helperText('Password minimal 8 karakter. Kosongkan jika tidak ingin mengubah.')
                                    ->confirmed()
                                    ->validationAttribute('password'),

                                TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->placeholder('Ulangi password')
                                    ->prefixIcon('heroicon-o-key')
                                    ->visible(fn ($context) => $context === 'create'),

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->helperText('Nonaktifkan untuk melarang akses pengguna ke sistem')
                                    ->default(true)
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->columnSpan(1),

                                Placeholder::make('email_verification_status')
                                    ->label('Status Verifikasi Email')
                                    ->content(function ($record) {
                                        if (!$record) {
                                            return 'Email verifikasi akan dikirim setelah user dibuat';
                                        }
                                        
                                        if ($record->hasVerifiedEmail()) {
                                            return '✅ Email terverifikasi pada ' . 
                                                   $record->email_verified_at->format('d M Y H:i');
                                        }
                                        
                                        return '⚠️ Email belum diverifikasi. Link verifikasi telah dikirim.';
                                    })
                                    ->columnSpan(1)
                                    ->visible(fn ($context) => $context === 'edit'),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(2),

                // Metadata Section (Only visible on edit)
                Section::make('Informasi Sistem')
                    ->description('Data sistem dan riwayat akun')
                    ->icon('heroicon-o-information-circle')
                    ->components([
                        Grid::make(3)
                            ->components([
                                Placeholder::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->content(fn ($record): string => $record?->created_at?->format('d M Y H:i') ?? '-'),

                                Placeholder::make('updated_at')
                                    ->label('Terakhir Diupdate')
                                    ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                                Placeholder::make('email_verified_at')
                                    ->label('Email Diverifikasi')
                                    ->content(fn ($record): string => 
                                        $record?->email_verified_at 
                                            ? $record->email_verified_at->format('d M Y H:i')
                                            : 'Belum diverifikasi'
                                    ),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($context) => $context === 'edit'),
            ]);
    }
}