<?php

namespace App\Filament\Resources\CityStoreDeliveries\Pages;

use App\Filament\Resources\CityStoreDeliveries\CityStoreDeliveryResource;
use App\Models\City;
use App\Models\CityStoreDelivery;
use App\Models\Store;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Throwable;

class InitializeStoreDelivery extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CityStoreDeliveryResource::class;

    protected string $view = 'filament.resources.city-store-deliveries.pages.initialize-store-delivery';

    protected static ?string $title = 'Initialize Store Delivery';

    public ?int $store_id = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Select Store')
                    ->description('Choose a store to initialize delivery prices for all cities')
                    ->schema([
                        Select::make('store_id')
                            ->label('Store')
                            ->options(function () {
                                $user = auth()->user();
                                $query = Store::query();
                                if ($user && ! $user->hasRole('super_admin')) {
                                    $user_store_ids = $user->stores()->pluck('stores.id')->toArray();
                                    $query->whereIn('stores.id', $user_store_ids);
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn () => $this->store_id = $this->store_id),
                    ]),
            ]);
    }

    /**
     * @throws Throwable
     */
    public function initialize(): void
    {
        $this->validate();

        $store_id = $this->store_id;

        if (! $store_id) {
            Notification::make()
                ->title('Please select a store')
                ->danger()
                ->send();

            return;
        }

        $existing_cities = CityStoreDelivery::query()
            ->where('store_id', $store_id)
            ->pluck('city_id')
            ->toArray();

        $cities_to_add = City::query()
            ->whereNotIn('id', $existing_cities)
            ->pluck('id')
            ->toArray();

        if (empty($cities_to_add)) {
            Notification::make()
                ->title('All cities already have delivery prices for this store')
                ->warning()
                ->send();

            return;
        }

        DB::transaction(function () use ($store_id, $cities_to_add) {
            $records = [];
            $now = now();

            foreach ($cities_to_add as $city_id) {
                $records[] = [
                    'store_id' => $store_id,
                    'city_id' => $city_id,
                    'price' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            CityStoreDelivery::query()->insert($records);
        });

        Notification::make()
            ->title('Delivery prices initialized')
            ->body(count($cities_to_add).' cities added with $0.00 delivery price')
            ->success()
            ->send();

        $this->redirect(CityStoreDeliveryResource::getUrl('index', ['tableFilters[store][value]' => $store_id]));
    }
}
