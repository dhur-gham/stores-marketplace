<?php

return [
    'title' => 'Payment',
    'subtitle' => 'Enter your card details to complete the payment',
    'loading' => 'Loading payment gateway...',
    'processing' => 'Processing Payment...',
    'submit' => 'Pay Now',
    'total' => 'Total Amount',
    'security_notice' => 'Your payment information is securely processed by PayTabs. We do not store your card details.',
    'fields' => [
        'card_number' => 'Card Number',
        'expiry_date' => 'Expiry Date',
        'month' => 'Month',
        'year' => 'Year',
        'cvv' => 'CVV',
        'cardholder_name' => 'Cardholder Name',
    ],
    'errors' => [
        'client_key_failed' => 'Failed to load payment gateway. Please refresh the page.',
        'client_key_missing' => 'Payment gateway not configured. Please contact support.',
        'script_load_failed' => 'Failed to load payment script. Please check your internet connection.',
        'script_not_loaded' => 'Payment script not loaded. Please refresh the page.',
        'token_missing' => 'Payment token not received. Please try again.',
        'payment_failed' => 'Payment processing failed. Please try again or use a different card.',
        'api_error' => 'An error occurred while processing your payment. Please try again.',
        'form_error' => 'An error occurred with the payment form. Please try again.',
    ],
    'success' => [
        'payment_completed' => 'Payment completed successfully!',
        'redirecting' => 'Redirecting...',
    ],
    'status' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],
    'methods' => [
        'cod' => 'Cash on Delivery',
        'online' => 'Online Payment',
    ],
];

