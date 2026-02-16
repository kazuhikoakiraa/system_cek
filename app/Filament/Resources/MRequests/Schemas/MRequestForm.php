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
                                                    ->reactive()
                                                    ->afterStateUpdated(fn (callable $set) => $set('komponen_id', null))
                                                    ->createOptionForm([
                                                        TextInput::make('kode_mesin')->required(),
                                                        TextInput::make('nama_mesin')->required(),
                                                    ]),
                                                
                                                Select::make('komponen_id')
                                                    ->label('Komponen (Opsional)')
                                                    ->relationship('komponen', 'nama_komponen', fn ($query, $get) => $query->where('mesin_id', $get('mesin_id')))
                                                    ->searchable()
                                                    ->preload()
                                                    ->disabled(fn ($get) => ! $get('mesin_id'))
                                                    ->helperText('Pilih mesin terlebih dahulu untuk melihat komponen yang tersedia'),
                                                
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
                                                
                                                Select::make('status')
                                                    ->label('Status')
                                                    ->options([
                                                        'pending' => '⏳ Pending',
                                                        'in_progress' => '🔧 In Progress',
                                                        'completed' => '✓ Completed',
                                                    ])
                                                    ->default('pending')
                                                    ->required()
                                                    ->native(false)
                                                    ->disabled(fn ($record) => $record === null)
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
                    ]),
            ]);
    }
}
