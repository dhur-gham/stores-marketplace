<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.list-orders';

    public ?string $status_filter = null;

    public ?string $store_filter = null;

    public ?string $customer_filter = null;

    public ?string $date_from = null;

    public ?string $date_to = null;

    public string $search = '';

    public bool $show_filters = false;

    protected function getTableQuery(): Builder
    {
        $query = Order::query()
            ->with(['customer', 'store', 'city', 'order_items.product'])
            ->latest();

        // Apply filters
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        if ($this->store_filter) {
            $query->where('store_id', $this->store_filter);
        }

        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('store', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
            });
        }

        // Customer filter only for super_admin
        if ($this->customer_filter) {
            $user = auth()->user();
            if ($user && $user->hasRole('super_admin')) {
                $query->where('customer_id', $this->customer_filter);
            }
        }

        // Filter by user's stores if not super_admin
        $user = auth()->user();
        if ($user && ! $user->hasRole('super_admin')) {
            $store_ids = $user->stores()->pluck('stores.id')->toArray();
            if (! empty($store_ids)) {
                $query->whereIn('store_id', $store_ids);
            } else {
                // User has no stores, return empty result
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    public function getOrders()
    {
        return $this->getTableQuery()->paginate(12);
    }

    public function getStores()
    {
        $user = auth()->user();
        $query = Store::query()->orderBy('name');

        if ($user && ! $user->hasRole('super_admin')) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query->get();
    }

    public function getCustomers()
    {
        return Customer::query()->orderBy('name')->get();
    }

    public function getStatusOptions(): array
    {
        return collect(OrderStatus::cases())
            ->mapWithKeys(fn ($status) => [$status->value => __('orders.status.'.$status->value)])
            ->toArray();
    }

    public function resetFilters(): void
    {
        $this->status_filter = null;
        $this->store_filter = null;
        $this->customer_filter = null;
        $this->date_from = null;
        $this->date_to = null;
        $this->search = '';
    }

    public function toggleFilters(): void
    {
        $this->show_filters = ! $this->show_filters;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                Action::make('update_status')
                    ->label(__('orders.actions.update_status'))
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->label(__('orders.fields.status'))
                            ->options(OrderStatus::class)
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $user = auth()->user();
                        $records->each(function (Order $order) use ($data, $user) {
                            $order->recordStatusChange($data['status'], $user);
                            $order->update(['status' => $data['status']]);
                        });

                        Notification::make()
                            ->title(__('orders.notifications.status_updated'))
                            ->body(__('orders.notifications.status_updated_bulk'))
                            ->success()
                            ->send();
                    }),
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
