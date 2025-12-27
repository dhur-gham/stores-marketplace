<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\CustomerMessageService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\RichEditor;
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
            Action::make('send_customer_message')
                ->label(__('orders.pages.edit.send_message_to_customer'))
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->modalHeading(__('orders.pages.edit.send_message_modal_heading'))
                ->modalDescription(__('orders.pages.edit.send_message_modal_description'))
                ->form([
                    RichEditor::make('message')
                        ->label(__('orders.pages.edit.message_label'))
                        ->placeholder(__('orders.pages.edit.message_placeholder'))
                        ->required()
                        ->helperText(__('orders.pages.edit.message_helper'))
                        ->default(fn () => $this->data['customer_message'] ?? '')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'h2',
                            'h3',
                            'bulletList',
                            'orderedList',
                            'blockquote',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->disableToolbarButtons([
                            'attachFiles',
                            'codeBlock',
                        ]),
                ])
                ->action(function (array $data) {
                    $message = $data['message'] ?? '';

                    if (empty($message)) {
                        Notification::make()
                            ->title(__('orders.pages.edit.message_required'))
                            ->body(__('orders.pages.edit.message_required_body'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $message_service = app(CustomerMessageService::class);
                    $success = $message_service->sendOrderMessage($this->record, $message);

                    if ($success) {
                        Notification::make()
                            ->title(__('orders.pages.edit.message_sent_successfully'))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title(__('orders.pages.edit.failed_to_send_message'))
                            ->body(__('orders.pages.edit.failed_to_send_message_body'))
                            ->danger()
                            ->send();
                    }
                }),
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
            $product = $item->product;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $product->name ?? 'N/A',
                'product_sku' => $product->sku ?? null,
                'product_image' => $product->image ? asset('storage/'.$product->image) : null,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price,
            ];
        })->toArray();
    }

    public function updateStatus(OrderStatus $status): void
    {
        if ($this->record->status !== $status) {
            $old_status = $this->record->status;
            $this->record->recordStatusChange($status, auth()->user());
            $this->record->update(['status' => $status]);
            $this->record->refresh();

            // Refresh form data to update the status dropdown
            // Reload form data from the refreshed record, ensuring status is converted to its value
            $form_data = $this->record->attributesToArray();
            $form_data['status'] = $status->value;
            $this->form->fill($form_data);

            // Notify store owners about status change
            $store_owner_notification_service = app(\App\Services\StoreOwnerNotificationService::class);
            $store_owner_notification_service->notifyOrderStatusChange($this->record, $old_status, $status);

            // Notify customer about status change
            $customer_message_service = app(CustomerMessageService::class);
            $customer_message_service->sendOrderStatusChangeNotification($this->record, $old_status, $status);

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
        // Calculate total from actual order items in database
        $subtotal = $this->record->order_items()->sum(\Illuminate\Support\Facades\DB::raw('quantity * price'));
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
                $old_status = $record->status;
                $record->recordStatusChange($new_status, auth()->user());

                // Notify store owners about status change
                $store_owner_notification_service = app(\App\Services\StoreOwnerNotificationService::class);
                $store_owner_notification_service->notifyOrderStatusChange($record, $old_status, $new_status);

                // Notify customer about status change
                $customer_message_service = app(CustomerMessageService::class);
                $customer_message_service->sendOrderStatusChangeNotification($record, $old_status, $new_status);
            }
        }

        $record->update($data);

        // Order items are read-only, no updates needed

        return $record;
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
            'label' => __('orders.pages.edit.order_created'),
            'changed_by' => null,
        ];

        // Get all status history records from database, ordered chronologically (oldest first)
        $status_history_records = $this->record->status_history()->with('changed_by')->oldest()->get();

        foreach ($status_history_records as $record) {
            $history[] = [
                'status' => $record->status,
                'created_at' => $record->created_at,
                'label' => __('orders.status.'.$record->status->value),
                'changed_by' => $record->changed_by,
            ];
        }

        return $history;
    }
}
