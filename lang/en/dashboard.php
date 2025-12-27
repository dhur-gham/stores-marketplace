<?php

return [
    'welcome' => 'Welcome, :name!',
    'welcome_admin' => 'Welcome, :name!',
    'welcome_with_store' => 'Welcome, :name! Managing :store 👋',
    'welcome_with_stores' => 'Welcome, :name! Managing :stores 👋',
    'and_more' => 'and :count more',
    'last_updated' => 'Last updated: :time',
    'data_loading_first_time' => 'Data is being loaded for the first time',
    'refresh_data' => 'Refresh Data',
    'refresh_my_stats' => 'Refresh My Stats',
    'dashboard_refreshed' => 'Dashboard Refreshed',
    'dashboard_refreshed_body' => 'All dashboard data has been refreshed.',
    'stats_refreshed' => 'Stats Refreshed',
    'stats_refreshed_body' => 'Your store statistics have been refreshed.',

    // Telegram Activation
    'telegram' => [
        'activate_notifications' => 'Activate Telegram Notifications',
        'activate_description' => 'Link your Telegram account to receive instant notifications about new orders, order status changes, and low stock alerts.',
        'activate_button' => 'Activate on Telegram',
        'status' => 'Status',
        'activated' => '✅ Activated',
        'not_activated' => '❌ Not Activated',
        'chat_id' => 'Telegram Chat ID',
        'no_chat_id' => 'Not linked',
        'activation_link' => 'Activation Link',
    ],

    // Widget Stats
    'widgets' => [
        'my_stores' => 'My Stores',
        'my_stores_description' => 'Stores you manage',
        'total_orders' => 'Total Orders',
        'total_orders_description' => 'All orders from your stores',
        'total_items_sold' => 'Total Items Sold',
        'total_items_sold_description' => 'Items sold across all orders',
        'total_sales' => 'Total Sales',
        'total_sales_description' => 'Revenue from completed orders',
        'order_status' => [
            'new_description' => 'New orders',
            'processing_description' => 'Orders being processed',
            'dispatched_description' => 'Orders dispatched',
            'complete_description' => 'Completed orders',
            'cancelled_description' => 'Cancelled orders',
        ],
    ],
];
