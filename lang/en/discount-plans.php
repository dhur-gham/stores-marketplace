<?php

return [
    'label' => 'Discount Plan',
    'plural_label' => 'Discount Plans',
    'navigation_label' => 'Discount Plans',
    'create' => 'Create Discount Plan',
    'edit' => 'Edit Discount Plan',
    'view' => 'View Discount Plan',
    'delete' => 'Delete Discount Plan',
    'delete_any' => 'Delete Discount Plans',
    'export' => 'Export Discount Plans',
    'import' => 'Import Discount Plans',
    'fields' => [
        'name' => 'Plan Name',
        'store' => 'Store',
        'store_id' => 'Store',
        'discount_type' => 'Discount Type',
        'discount_value' => 'Discount Value',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'status' => 'Status',
        'created_by_user_id' => 'Created By',
    ],
    'sections' => [
        'plan_information' => 'Plan Information',
        'discount_details' => 'Discount Details',
        'schedule' => 'Schedule',
    ],
    'status' => [
        'scheduled' => 'Scheduled',
        'active' => 'Active',
        'expired' => 'Expired',
    ],
    'discount_type' => [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed',
    ],
    'filters' => [
        'status' => 'Status',
        'discount_type' => 'Discount Type',
        'store' => 'Store',
    ],
    'actions' => [
        'activate' => 'Activate',
        'expire' => 'Expire',
    ],
    'notifications' => [
        'plan_activated' => 'Discount plan activated',
        'plan_activated_body' => 'The discount plan has been activated successfully.',
        'plan_expired' => 'Discount plan expired',
        'plan_expired_body' => 'The discount plan has been expired successfully.',
    ],
    'relation_managers' => [
        'products' => [
            'title' => 'Products',
            'columns' => [
                'image' => 'Image',
                'name' => 'Product Name',
                'sku' => 'SKU',
                'price' => 'Price',
                'discounted_price' => 'Discounted Price',
                'stock' => 'Stock',
            ],
            'form' => [
                'products' => 'Products',
            ],
            'actions' => [
                'add_products' => 'Add Products',
                'remove' => 'Remove',
                'remove_selected' => 'Remove selected',
            ],
            'notifications' => [
                'products_added' => 'Products added successfully',
                'product_removed' => 'Product removed successfully',
                'products_removed' => 'Products removed successfully',
            ],
        ],
    ],
];
