<?php

namespace App\Filament\Resources\MRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        // Tab 1: Informasi Request
                        Tabs\Tab::make('Informasi Request')
                            ->schema([
                                Section::make('Detail Request')
                                    ->description('Informasi permintaan maintenance')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('request_number')
                                                    ->label('Nomor Request')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->default(fn () => 'REQ-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                                                    ->disabled()
                                                    ->dehydrated(),
                                                
                                                DateTimePicker::make('requested_at')
                                                    ->label('Tanggal Request')
                                                    ->default(now())
                                                    ->required()
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->seconds(false),
                                                
                                                Select::make('mesin_id')
                                                    ->label('Mesin')
                                                    ->relationship('mesin', 'nama_mesin')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        TextInput::make('kode_mesin')->required(),
                                                        TextInput::make('nama_mesin')->required(),
                                                    ]),
                                                
                                                Select::make('komponen_id')
                                                    ->label('Komponen (Opsional)')
                                                    ->relationship('komponen', 'nama_komponen')
                                                    ->searchable()
                                                    ->preload()
                                                    ->helperText('Pilih komponen spesifik jika ada'),
                                                
                                                Select::make('urgency_level')
                                                    ->label('Tingkat Urgensi')
                                                    ->options([
                                                        'low' => '🟢 Low - Tidak Mendesak',
                                                        'medium' => '🟡 Medium - Normal',
                                                        'high' => '🟠 High - Prioritas Tinggi',
                                                        'critical' => '🔴 Critical - Darurat',
                                                    ])
                                                    ->default('medium')
                                                    ->required()
                                                    ->native(false)
                                                    ->columnSpan(2),
                                                
                                                Hidden::make('created_by')
                                                    ->default(fn () => Auth::user()?->id),
                                                
                                                Textarea::make('problema_deskripsi')
                                                    ->label('Deskripsi Masalah')
                                                    ->required()
                                                    ->rows(5)
                                                    ->placeholder('Jelaskan masalah atau kebutuhan maintenance secara detail...')
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ]),

                        // Tab 2: Status & Approval
                        Tabs\Tab::make('Status & Approval')
                            ->schema([
                                Section::make('Status Permintaan')
                                    ->description('Status dan approval dari admin')
                                    ->icon('heroicon-o-check-circle')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('status')
                                                    ->label('Status')
                                                    ->options([
                                                        'pending' => '⏳ Pending',
                                                        'approved' => '✅ Approved',
                                                        'rejected' => '❌ Rejected',
                                                        'in_progress' => '🔧 In Progress',
                                                        'completed' => '✓ Completed',
                                                    ])
                                                    ->default('pending')
                                                    ->required()
                                                    ->native(false)
                                                    ->disabled(fn ($record) => $record === null)
                                                    ->columnSpan(2),
                                            ]),
                                    ]),

                                Section::make('Approval')
                                    ->description('Detail persetujuan request')
                                    ->icon('heroicon-o-document-check')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('approved_by')
                                                    ->label('Disetujui Oleh')
                                                    ->relationship('approver', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->disabled(fn ($record) => $record === null)
                                                    ->visible(fn ($get) => in_array($get('status'), ['approved', 'rejected'])),
                                                
                                                DateTimePicker::make('approved_at')
                                                    ->label('Tanggal Approval')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->seconds(false)
                                                    ->disabled(fn ($record) => $record === null)
                                                    ->visible(fn ($get) => $get('status') === 'approved'),
                                                
                                                Textarea::make('approval_notes')
                                                    ->label('Catatan Approval')
                                                    ->rows(3)
                                                    ->placeholder('Catatan dari admin/approver...')
                                                    ->visible(fn ($get) => $get('status') === 'approved')
                                                    ->columnSpan(2),
                                            ]),
                                    ])
                                    ->visible(fn ($get) => $get('status') === 'approved'),

                                Section::make('Penolakan')
                                    ->description('Detail penolakan request')
                                    ->icon('heroicon-o-x-circle')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                DateTimePicker::make('rejected_at')
                                                    ->label('Tanggal Rejection')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->seconds(false)
                                                    ->visible(fn ($get) => $get('status') === 'rejected'),
                                                
                                                Textarea::make('rejection_reason')
                                                    ->label('Alasan Penolakan')
                                                    ->rows(3)
                                                    ->placeholder('Alasan penolakan request...')
                                                    ->visible(fn ($get) => $get('status') === 'rejected')
                                                    ->columnSpan(2),
                                            ]),
                                    ])
                                    ->visible(fn ($get) => $get('status') === 'rejected'),
                            ]),
                    ]),
            ]);
    }
}
