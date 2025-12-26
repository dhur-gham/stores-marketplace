<?php

namespace App\Filament\Resources\ApiRequests\Tables;

use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApiRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                            })
                            ->weight(FontWeight::Bold),
                        TextColumn::make('path')
                            ->searchable()
                            ->limit(40)
                            ->tooltip(fn ($record) => $record->path)
                            ->color('gray'),
                    ]),
                    Stack::make([
                        TextColumn::make('status_code')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state >= 200 && $state < 300 => 'success',
                                $state >= 300 && $state < 400 => 'info',
                                $state >= 400 && $state < 500 => 'warning',
                                $state >= 500 => 'danger',
                                default => 'gray',
                            }),
                        TextColumn::make('duration_ms')
                            ->suffix(' ms')
                            ->color(fn (int $state): string => match (true) {
                                $state < 100 => 'success',
                                $state < 500 => 'warning',
                                default => 'danger',
                            })
                            ->weight(FontWeight::SemiBold),
                    ])->visibleFrom('sm'),
                    Stack::make([
                        TextColumn::make('ip_address')
                            ->label('IP')
                            ->icon('heroicon-m-globe-alt')
                            ->color('gray'),
                        TextColumn::make('user.name')
                            ->label('User')
                            ->default('Guest')
                            ->icon('heroicon-m-user')
                            ->color('gray'),
                    ])->visibleFrom('md'),
                    Stack::make([
                        TextColumn::make('request_size')
                            ->formatStateUsing(fn ($state) => self::formatBytes($state))
                            ->label('Request')
                            ->prefix('Req: ')
                            ->color('gray'),
                        TextColumn::make('response_size')
                            ->formatStateUsing(fn ($state) => self::formatBytes($state))
                            ->label('Response')
                            ->prefix('Res: ')
                            ->color('gray'),
                    ])->visibleFrom('lg'),
                    TextColumn::make('created_at')
                        ->label('Time')
                        ->dateTime('M j, Y H:i:s')
                        ->sortable()
                        ->visibleFrom('xl'),
                ])->from('md'),

                // Mobile-only: show essential info below main content
                Stack::make([
                    Split::make([
                        TextColumn::make('status_code')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state >= 200 && $state < 300 => 'success',
                                $state >= 300 && $state < 400 => 'info',
                                $state >= 400 && $state < 500 => 'warning',
                                $state >= 500 => 'danger',
                                default => 'gray',
                            }),
                        TextColumn::make('duration_ms')
                            ->suffix(' ms')
                            ->color(fn (int $state): string => match (true) {
                                $state < 100 => 'success',
                                $state < 500 => 'warning',
                                default => 'danger',
                            })
                            ->weight(FontWeight::SemiBold),
                    ]),
                    Split::make([
                        TextColumn::make('ip_address')
                            ->label('IP')
                            ->icon('heroicon-m-globe-alt')
                            ->color('gray'),
                        TextColumn::make('created_at')
                            ->label('Time')
                            ->dateTime('H:i:s')
                            ->color('gray'),
                    ]),
                ])->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('method')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'PATCH' => 'PATCH',
                        'DELETE' => 'DELETE',
                    ])
                    ->native(false),
                SelectFilter::make('status_code')
                    ->options([
                        '2xx' => '2xx Success',
                        '3xx' => '3xx Redirect',
                        '4xx' => '4xx Client Error',
                        '5xx' => '5xx Server Error',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function (Builder $query, string $value) {
                            $ranges = [
                                '2xx' => [200, 299],
                                '3xx' => [300, 399],
                                '4xx' => [400, 499],
                                '5xx' => [500, 599],
                            ];
                            if (isset($ranges[$value])) {
                                return $query->whereBetween('status_code', $ranges[$value]);
                            }

                            return $query;
                        });
                    })
                    ->native(false),
                Filter::make('slow_requests')
                    ->label('Slow Requests (>500ms)')
                    ->query(fn (Builder $query): Builder => $query->where('duration_ms', '>', 500)),
                Filter::make('errors')
                    ->label('Errors Only')
                    ->query(fn (Builder $query): Builder => $query->where('status_code', '>=', 400)),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
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

