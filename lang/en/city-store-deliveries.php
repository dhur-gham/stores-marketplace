<?php

return [
    'label' => 'Delivery Price',
    'plural_label' => 'Delivery Prices',
    'navigation_label' => 'Delivery Prices',
    'create' => 'Create Delivery Price',
    'edit' => 'Edit Delivery Price',
    'view' => 'View Delivery Price',
    'delete' => 'Delete Delivery Price',
    'delete_any' => 'Delete Delivery Prices',
    'fields' => [
        'store' => 'Store',
        'store_id' => 'Store',
        'city' => 'City',
        'city_id' => 'City',
        'price' => 'Delivery Price',
    ],
    'sections' => [
        'delivery_price' => 'Delivery Price',
        'delivery_price_description' => 'Set delivery price for a city',
    ],
    'actions' => [
        'initialize' => 'Initialize Store',
        'update_price' => 'Update Price',
    ],
    'notifications' => [
        'prices_updated' => 'Prices updated',
        'prices_updated_body' => ':count records updated successfully.',
        'delivery_prices_initialized' => 'Delivery prices initialized',
        'delivery_prices_initialized_body' => ':count cities added with $0.00 delivery price',
        'all_cities_have_prices' => 'All cities already have delivery prices for this store',
    ],
];
