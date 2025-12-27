<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class CustomerMessageService
{
    public function __construct(public TelegramService $telegram_service) {}

    /**
     * Send a message to a customer via Telegram.
     *
     * @param  Customer  $customer  The customer to send message to
     * @param  string  $message  The message text
     * @return bool Success status
     */
    public function sendMessage(Customer $customer, string $message): bool
    {
        if (! $customer->hasTelegramActivated()) {
            Log::warning('Attempted to send Telegram message to customer without chat_id', [
                'customer_id' => $customer->id,
            ]);

            return false;
        }

        $result = $this->telegram_service->sendMessageToCustomer($customer, $message);

        return $result !== null;
    }

    /**
     * Send an order-related message to a customer.
     *
     * @param  Order  $order  The order
     * @param  string  $message  The message text
     * @return bool Success status
     */
    public function sendOrderMessage(Order $order, string $message): bool
    {
        $order_link = $this->getOrderLink($order);
        $formatted_message = "📦 <b>Order <a href=\"{$order_link}\">#{$order->id}</a> Update</b>\n\n";
        $formatted_message .= $this->convertToTelegramHtml($message);

        return $this->sendMessage($order->customer, $formatted_message);
    }

    /**
     * Send order status change notification to customer.
     *
     * @param  Order  $order  The order
     * @param  \App\Enums\OrderStatus  $old_status  Previous status
     * @param  \App\Enums\OrderStatus  $new_status  New status
     * @return bool Success status
     */
    public function sendOrderStatusChangeNotification(Order $order, \App\Enums\OrderStatus $old_status, \App\Enums\OrderStatus $new_status): bool
    {
        $order_link = $this->getOrderLink($order);
        $status_label = __('orders.status.'.$new_status->value);

        $formatted_message = "📦 <b>Order <a href=\"{$order_link}\">#{$order->id}</a> Status Updated</b>\n\n";
        $formatted_message .= __('orders.notifications.customer_status_changed', ['status' => $status_label]);

        return $this->sendMessage($order->customer, $formatted_message);
    }

    /**
     * Send delivery price change notification to customer.
     *
     * @param  Order  $order  The order
     * @param  int  $old_price  Previous delivery price
     * @param  int  $new_price  New delivery price
     * @return bool Success status
     */
    public function sendDeliveryPriceChangeNotification(Order $order, int $old_price, int $new_price): bool
    {
        $order_link = $this->getOrderLink($order);

        $formatted_message = "📦 <b>Order <a href=\"{$order_link}\">#{$order->id}</a> Delivery Price Updated</b>\n\n";
        $formatted_message .= __('orders.notifications.customer_delivery_price_changed', [
            'old_price' => number_format($old_price),
            'new_price' => number_format($new_price),
        ]);

        return $this->sendMessage($order->customer, $formatted_message);
    }

    /**
     * Get the frontend URL for an order.
     *
     * @param  Order  $order  The order
     * @return string Order URL
     */
    private function getOrderLink(Order $order): string
    {
        $frontend_url = config('app.frontend_url');
        // Remove trailing slash if present
        $frontend_url = rtrim($frontend_url, '/');

        return "{$frontend_url}/orders/{$order->id}";
    }

    /**
     * Convert RichEditor HTML to Telegram-compatible HTML.
     * Telegram only supports: <b>, <i>, <u>, <s>, <u>, <a>, <code>, <pre>
     *
     * @param  string  $html  HTML from RichEditor
     * @return string Telegram-compatible HTML
     */
    private function convertToTelegramHtml(string $html): string
    {
        if (empty(trim(strip_tags($html)))) {
            return '';
        }

        // First, preserve paragraph structure by converting <p></p> to <p><br></p>
        // This prevents DOMDocument from removing empty paragraphs
        $html = preg_replace('/<p\s*>\s*<\/p>/i', '<p><br></p>', $html);

        // Load HTML into DOMDocument for parsing
        $dom = new \DOMDocument;
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Get the body content
        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            // Fallback: strip unsupported tags but keep supported ones
            return strip_tags($html, '<b><i><u><s><a><code><pre><strong><em><ins><strike><del>');
        }

        $result = '';
        foreach ($body->childNodes as $node) {
            $node_result = $this->nodeToTelegramHtml($node);
            $result .= $node_result;
        }

        // Clean up excessive consecutive newlines (more than 2), but preserve intentional line breaks
        $result = preg_replace('/\n{3,}/', "\n\n", $result);
        // Don't trim - preserve leading/trailing newlines that might be intentional
        // Only trim if the entire result is just whitespace
        if (trim($result) === '') {
            return '';
        }

        return $result;
    }

    /**
     * Convert DOM node to Telegram-compatible HTML.
     */
    private function nodeToTelegramHtml(\DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            // Preserve newlines in text content
            $text = $node->textContent;
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

            // Preserve newlines
            return $text;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $tag_name = strtolower($node->nodeName);
        $content = '';

        foreach ($node->childNodes as $child) {
            $content .= $this->nodeToTelegramHtml($child);
        }

        // Handle supported tags
        switch ($tag_name) {
            case 'b':
            case 'strong':
                return "<b>{$content}</b>";

            case 'i':
            case 'em':
                return "<i>{$content}</i>";

            case 'u':
            case 'ins':
                return "<u>{$content}</u>";

            case 's':
            case 'strike':
            case 'del':
                return "<s>{$content}</s>";

            case 'a':
                $href = $node->getAttribute('href');
                if ($href) {
                    return "<a href=\"{$href}\">{$content}</a>";
                }

                return $content;

            case 'code':
                return "<code>{$content}</code>";

            case 'pre':
                return "<pre>{$content}</pre>";

                // Convert unsupported block elements - preserve newlines
            case 'p':
                // Always add newline after paragraph to preserve line breaks
                // Even if paragraph is empty, it represents a line break
                $trimmed = trim($content);
                if ($trimmed === '') {
                    // Empty paragraph = line break
                    return "\n";
                }

                return $trimmed."\n";

            case 'div':
                // Only add newline if div has content
                if (trim($content) !== '') {
                    $trimmed = rtrim($content);

                    return $trimmed."\n";
                }

                return $content;

            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $trimmed = rtrim($content);

                return "<b>{$trimmed}</b>\n";

            case 'ul':
            case 'ol':
                // Lists already have newlines from <li> elements
                return $content;

            case 'li':
                $trimmed = rtrim($content);

                return "• {$trimmed}\n";

            case 'blockquote':
                $trimmed = rtrim($content);

                return "> {$trimmed}\n";

            case 'br':
                return "\n";

            default:
                return $content;
        }
    }
}
