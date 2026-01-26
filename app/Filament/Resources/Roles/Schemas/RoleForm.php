<?php

namespace App\Filament\Resources\Roles\Schemas;

use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    use HasShieldFormComponents;
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Informasi Role - Left Column
                Section::make('Informasi Role')
                    ->description('Detail dan identitas role')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nama Role')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Contoh: operator, supervisor, admin')
                            ->prefixIcon('heroicon-o-tag')
                            ->helperText('Nama role harus unik dan tidak boleh sama dengan role lain')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('name', str($state)->slug('_')->toString());
                            }),

                        Select::make('guard_name')
                            ->label('Guard Name')
                            ->default('web')
                            ->required()
                            ->options([
                                'web' => 'Web',
                                'api' => 'API',
                            ])
                            ->prefixIcon('heroicon-o-shield-check')
                            ->helperText('Guard yang akan digunakan untuk autentikasi')
                            ->native(false),
                    ])
                    ->columnSpanFull(),     

                // Permissions Panel - Full Width (Shield Component)
                static::getShieldFormComponents(),

                // Metadata Section (Only visible on edit) - Full Width
                Section::make('Informasi Sistem')
                    ->description('Riwayat dan metadata role')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->components([
                        Placeholder::make('created_at')
                            ->label('Dibuat Pada')
                            ->content(fn ($record): string => 
                                $record?->created_at 
                                    ? $record->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB'
                                    : '-'
                            )
                            ->columnSpan(1),

                        Placeholder::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->content(fn ($record): string => 
                                $record?->updated_at 
                                    ? $record->updated_at->timezone('Asia/Jakarta')->diffForHumans() . 
                                      ' (' . $record->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB)'
                                    : '-'
                            )
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($context) => $context === 'edit'),
            ]);
    }
}
