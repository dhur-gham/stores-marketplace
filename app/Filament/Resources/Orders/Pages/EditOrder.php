<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.edit-order';

    public function getTitle(): string
    {
        return __('orders.edit');
    }

    public array $order_items = [];

    public ?int $new_item_product_id = null;

    public int $new_item_quantity = 1;

    public int $new_item_price = 0;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_complete')
                ->label(__('orders.actions.mark_complete'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== OrderStatus::Complete)
                ->requiresConfirmation()
                ->action(fn () => $this->updateStatus(OrderStatus::Complete)),
            Action::make('mark_dispatched')
                ->label(__('orders.actions.mark_dispatched'))
                ->icon('heroicon-o-truck')
                ->color('info')
                ->visible(fn () => $this->record->status === OrderStatus::Processing)
                ->requiresConfirmation()
                ->action(fn () => $this->updateStatus(OrderStatus::Dispatched)),
            Action::make('mark_processing')
                ->label(__('orders.actions.mark_processing'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning')
                ->visible(fn () => $this->record->status === OrderStatus::New)
                ->action(fn () => $this->updateStatus(OrderStatus::Processing)),
            Action::make('cancel_order')
                ->label(__('orders.actions.cancel_order'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status !== OrderStatus::Cancelled && $this->record->status !== OrderStatus::Complete)
                ->requiresConfirmation()
                ->action(fn () => $this->updateStatus(OrderStatus::Cancelled)),
            DeleteAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->loadOrderItems();
    }

    protected function loadOrderItems(): void
    {
        $this->order_items = $this->record->order_items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'N/A',
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price,
            ];
        })->toArray();
    }

    public function addOrderItem(): void
    {
        if (! $this->new_item_product_id || $this->new_item_quantity <= 0 || $this->new_item_price < 0) {
            Notification::make()
                ->title('Invalid Item')
                ->body('Please fill all fields with valid values.')
                ->danger()
                ->send();

            return;
        }

        $product = Product::find($this->new_item_product_id);

        $this->order_items[] = [
            'id' => null,
            'product_id' => $this->new_item_product_id,
            'product_name' => $product->name ?? 'N/A',
            'quantity' => $this->new_item_quantity,
            'price' => $this->new_item_price,
            'subtotal' => $this->new_item_quantity * $this->new_item_price,
        ];

        $this->new_item_product_id = null;
        $this->new_item_quantity = 1;
        $this->new_item_price = 0;

        $this->calculateTotal();
    }

    public function removeOrderItem(int $index): void
    {
        unset($this->order_items[$index]);
        $this->order_items = array_values($this->order_items);
        $this->calculateTotal();
    }

    public function updateItemQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $this->order_items[$index]['quantity'] = $quantity;
        $this->order_items[$index]['subtotal'] = $quantity * $this->order_items[$index]['price'];
        $this->calculateTotal();
    }

    public function updateItemPrice(int $index, int $price): void
    {
        if ($price < 0) {
            return;
        }

        $this->order_items[$index]['price'] = $price;
        $this->order_items[$index]['subtotal'] = $this->order_items[$index]['quantity'] * $price;
        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $subtotal = collect($this->order_items)->sum('subtotal');
        $delivery_price = $this->data['delivery_price'] ?? 0;
        $this->data['total'] = $subtotal + $delivery_price;
    }

    public function updateStatus(OrderStatus $status): void
    {
        if ($this->record->status !== $status) {
            $this->record->recordStatusChange($status, auth()->user());
            $this->record->update(['status' => $status]);

            $status_label = __('orders.status.'.$status->value);

            Notification::make()
                ->title(__('orders.notifications.status_updated'))
                ->body(__('orders.notifications.status_updated_body', ['status' => $status_label]))
                ->success()
                ->send();
        }
    }

    public function onStatusChanged(string $status_value): void
    {
        $status = OrderStatus::from($status_value);
        $this->updateStatus($status);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate total from order items
        $subtotal = collect($this->order_items)->sum('subtotal');
        $data['total'] = $subtotal + ($data['delivery_price'] ?? 0);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Detect status change and record history before update
        if (isset($data['status'])) {
            $new_status = $data['status'] instanceof OrderStatus
                ? $data['status']
                : OrderStatus::from($data['status']);

            if ($new_status !== $record->status) {
                $record->recordStatusChange($new_status, auth()->user());
            }
        }

        $record->update($data);

        // Sync order items with stock management
        $existing_ids = collect($this->order_items)->pluck('id')->filter()->toArray();
        $existing_items = $record->order_items()->get()->keyBy('id');

        // Handle deleted items - restore stock
        $deleted_items = $record->order_items()->whereNotIn('id', $existing_ids)->get();
        foreach ($deleted_items as $deleted_item) {
            $product = Product::find($deleted_item->product_id);
            if ($product) {
                $product->increment('stock', $deleted_item->quantity);
            }
        }
        $record->order_items()->whereNotIn('id', $existing_ids)->delete();

        // Update or create items with stock management
        foreach ($this->order_items as $item) {
            if ($item['id']) {
                // Update existing item
                $existing_item = $existing_items->get($item['id']);
                if ($existing_item) {
                    $old_product_id = $existing_item->product_id;
                    $new_product_id = $item['product_id'];
                    $old_quantity = $existing_item->quantity;
                    $new_quantity = $item['quantity'];

                    // If product changed, restore stock for old product and decrement for new product
                    if ($old_product_id !== $new_product_id) {
                        $old_product = Product::find($old_product_id);
                        if ($old_product) {
                            $old_product->increment('stock', $old_quantity);
                        }
                        $new_product = Product::find($new_product_id);
                        if ($new_product) {
                            $new_product->decrement('stock', $new_quantity);
                        }
                    } else {
                        // Same product, adjust stock based on quantity change
                        $quantity_diff = $new_quantity - $old_quantity;
                        if ($quantity_diff !== 0) {
                            $product = Product::find($item['product_id']);
                            if ($product) {
                                // If quantity increased, decrement stock
                                // If quantity decreased, increment stock
                                if ($quantity_diff > 0) {
                                    $product->decrement('stock', $quantity_diff);
                                } else {
                                    $product->increment('stock', abs($quantity_diff));
                                }
                            }
                        }
                    }

                    // Update the order item
                    OrderItem::where('id', $item['id'])->update([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            } else {
                // Create new item - decrement stock
                OrderItem::create([
                    'order_id' => $record->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock', $item['quantity']);
                }
            }
        }

        return $record;
    }

    public function getProducts()
    {
        $user = auth()->user();
        $query = Product::query()->orderBy('name');

        if ($user && ! $user->hasRole('super_admin')) {
            $store_ids = $user->stores()->pluck('stores.id')->toArray();
            if (! empty($store_ids)) {
                $query->whereIn('store_id', $store_ids);
            }
        }

        return $query->get();
    }

    public function getOrderTotal(): int
    {
        $subtotal = collect($this->order_items)->sum('subtotal');
        $delivery_price = $this->data['delivery_price'] ?? 0;

        return $subtotal + $delivery_price;
    }

    public function getStatusHistory()
    {
        $history = [];

        // Add "Order Created" entry at the beginning
        $history[] = [
            'status' => null,
            'created_at' => $this->record->created_at,
            'label' => 'Order Created',
            'changed_by' => null,
        ];

        // Get all status history records from database, ordered chronologically (oldest first)
        $status_history_records = $this->record->status_history()->with('changed_by')->oldest()->get();

        foreach ($status_history_records as $record) {
            $history[] = [
                'status' => $record->status,
                'created_at' => $record->created_at,
                'label' => ucfirst($record->status->value),
                'changed_by' => $record->changed_by,
            ];
        }

        return $history;
    }
}
