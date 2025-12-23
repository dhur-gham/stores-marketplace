<?php

namespace App\Filament\Resources\ApiRequests;

use App\Filament\Resources\ApiRequests\Pages\ListApiRequests;
use App\Filament\Resources\ApiRequests\Pages\ViewApiRequest;
use App\Filament\Resources\ApiRequests\Tables\ApiRequestsTable;
use App\Models\ApiRequest;
use BackedEnum;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApiRequestResource extends Resource
{
    protected static ?string $model = ApiRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static ?string $modelLabel = 'API Request';

    protected static ?string $pluralModelLabel = 'API Metrics';

    protected static ?string $navigationLabel = 'API Metrics';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Overview')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('method')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'GET' => 'info',
                                        'POST' => 'success',
                                        'PUT', 'PATCH' => 'warning',
                                        'DELETE' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('status_code')
                                    ->badge()
                                    ->color(fn (int $state): string => match (true) {
                                        $state >= 200 && $state < 300 => 'success',
                                        $state >= 300 && $state < 400 => 'info',
                                        $state >= 400 && $state < 500 => 'warning',
                                        $state >= 500 => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('duration_ms')
                                    ->label('Duration')
                                    ->suffix(' ms')
                                    ->color(fn (int $state): string => match (true) {
                                        $state < 100 => 'success',
                                        $state < 500 => 'warning',
                                        default => 'danger',
                                    }),
                                TextEntry::make('created_at')
                                    ->label('Timestamp')
                                    ->dateTime('M j, Y H:i:s'),
                            ]),
                    ]),

                Section::make('Request Details')
                    ->schema([
                        TextEntry::make('path')
                            ->label('Path')
                            ->copyable(),
                        TextEntry::make('full_url')
                            ->label('Full URL')
                            ->copyable()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('request_size')
                                    ->label('Request Size')
                                    ->formatStateUsing(fn ($state) => self::formatBytes($state)),
                                TextEntry::make('response_size')
                                    ->label('Response Size')
                                    ->formatStateUsing(fn ($state) => self::formatBytes($state)),
                            ]),
                    ]),

                Section::make('Client Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('ip_address')
                                    ->label('IP Address')
                                    ->copyable(),
                                TextEntry::make('user.name')
                                    ->label('User')
                                    ->default('Guest'),
                                TextEntry::make('user.email')
                                    ->label('User Email')
                                    ->default('-'),
                            ]),
                        TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->columnSpanFull(),
                    ]),

                Section::make('Timing')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('request_started_at')
                                    ->label('Started At')
                                    ->dateTime('M j, Y H:i:s'),
                                TextEntry::make('request_ended_at')
                                    ->label('Ended At')
                                    ->dateTime('M j, Y H:i:s'),
                                TextEntry::make('duration_ms')
                                    ->label('Duration')
                                    ->suffix(' ms'),
                            ]),
                    ]),

                Section::make('Error Information')
                    ->schema([
                        TextEntry::make('exception')
                            ->label('Exception/Error Message')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->status_code >= 400),
            ]);
    }

    public static function table(Table $table): Table
    {
        return ApiRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiRequests::route('/'),
            'view' => ViewApiRequest::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return false;
    }

    public static function canDelete(mixed $record): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['path', 'method', 'ip_address'];
    }

    protected static function formatBytes(?int $bytes): string
    {
        if ($bytes === null || $bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
