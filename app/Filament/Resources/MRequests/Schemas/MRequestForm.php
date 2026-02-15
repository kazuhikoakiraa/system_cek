<?php

namespace App\Filament\Resources\MRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Informasi Request')
                    ->columns(2)
                    ->schema([
                        TextInput::make('request_number')
                            ->label('Nomor Request')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'REQ-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated(),
                        
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
                            ->native(false),
                        
                        Hidden::make('created_by')
                            ->default(fn () => Auth::user()?->id),
                        
                        DateTimePicker::make('requested_at')
                            ->label('Tanggal Request')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false),
                        
                        Textarea::make('problema_deskripsi')
                            ->label('Deskripsi Masalah')
                            ->required()
                            ->rows(5)
                            ->placeholder('Jelaskan masalah atau kebutuhan maintenance secara detail...')
                            ->columnSpanFull(),
                    ])->columnSpan(2),

                Section::make('Status & Approval')
                    ->columns(1)
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
                            ->disabled(fn ($record) => $record === null),
                        
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
                            ->visible(fn ($get) => $get('status') === 'approved'),
                        
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
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ])->columnSpan(1),
            ]);
    }
}
