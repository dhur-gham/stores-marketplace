<?php

namespace App\Filament\Widgets;

use App\Models\ApiRequest;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ApiErrorsTable extends BaseWidget
{
    protected static ?int $sort = 13;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', ApiRequest::class) ?? false;
    }

    public function table(Table $table): Table
    {
        $table->heading('Recent API Errors');

        return $table
            ->query(
                ApiRequest::query()
                    ->where('status_code', '>=', 400)
                    ->with('user')
                    ->latest()
            )
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('method')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'GET' => 'info',
                                'POST' => 'success',
                                'PUT', 'PATCH' => 'warning',
                                'DELETE' => 'danger',
                                default => 'gray',
                            }),
                        TextColumn::make('path')
                            ->limit(30)
                            ->tooltip(fn ($record) => $record->path)
                            ->color('gray'),
                    ]),
                    Stack::make([
                        TextColumn::make('status_code')
                            ->badge()
                            ->color(fn (int $state): string => $state >= 500 ? 'danger' : 'warning')
                            ->weight(FontWeight::Bold),
                        TextColumn::make('created_at')
                            ->since()
                            ->color('gray'),
                    ]),
                ])->from('md'),

                Stack::make([
                    Split::make([
                        TextColumn::make('method')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'GET' => 'info',
                                'POST' => 'success',
                                'PUT', 'PATCH' => 'warning',
                                'DELETE' => 'danger',
                                default => 'gray',
                            }),
                        TextColumn::make('status_code')
                            ->badge()
                            ->color(fn (int $state): string => $state >= 500 ? 'danger' : 'warning'),
                    ]),
                    TextColumn::make('path')
                        ->limit(30)
                        ->color('gray'),
                    TextColumn::make('created_at')
                        ->since()
                        ->color('gray'),
                ])->hiddenFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5])
            ->poll('30s');
    }
}
