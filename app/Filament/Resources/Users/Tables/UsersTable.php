<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('id')
                        ->label('#')
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::SemiBold),
                        TextColumn::make('email')
                            ->searchable()
                            ->sortable()
                            ->copyable()
                            ->color('gray'),
                    ]),
                    TextColumn::make('roles.name')
                        ->badge()
                        ->color('primary')
                        ->visibleFrom('md'),
                    TextColumn::make('stores_count')
                        ->label('Stores')
                        ->counts('stores')
                        ->badge()
                        ->color('gray')
                        ->sortable()
                        ->visibleFrom('lg'),
                    TextColumn::make('products_count')
                        ->label('Products')
                        ->counts('products')
                        ->badge()
                        ->color('gray')
                        ->sortable()
                        ->visibleFrom('lg'),
                    Stack::make([
                        TextColumn::make('created_at')
                            ->label('Joined')
                            ->dateTime()
                            ->sortable(),
                        TextColumn::make('email_verified_at')
                            ->label('Verified')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->visibleFrom('xl'),
                ])->from('md'),

                // Mobile-only: show roles and counts below main content
                Stack::make([
                    Split::make([
                        TextColumn::make('roles.name')
                            ->badge()
                            ->color('primary'),
                    ]),
                    Split::make([
                        TextColumn::make('stores_count')
                            ->label('Stores')
                            ->counts('stores')
                            ->badge()
                            ->color('gray'),
                        TextColumn::make('created_at')
                            ->label('Joined')
                            ->date()
                            ->color('gray'),
                    ]),
                ])->hiddenFrom('md'),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
