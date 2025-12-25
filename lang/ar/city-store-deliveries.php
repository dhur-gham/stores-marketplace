<?php

return [
    'label' => 'سعر التوصيل',
    'plural_label' => 'أسعار التوصيل',
    'navigation_label' => 'أسعار التوصيل',
    'create' => 'إنشاء سعر توصيل',
    'edit' => 'تعديل سعر التوصيل',
    'view' => 'عرض سعر التوصيل',
    'delete' => 'حذف سعر التوصيل',
    'delete_any' => 'حذف أسعار التوصيل',
    'fields' => [
        'store' => 'المتجر',
        'store_id' => 'المتجر',
        'city' => 'المدينة',
        'city_id' => 'المدينة',
        'price' => 'سعر التوصيل',
    ],
    'sections' => [
        'delivery_price' => 'سعر التوصيل',
        'delivery_price_description' => 'تعيين سعر التوصيل لمدينة',
    ],
    'actions' => [
        'initialize' => 'تهيئة المتجر',
        'update_price' => 'تحديث السعر',
    ],
    'notifications' => [
        'prices_updated' => 'تم تحديث الأسعار',
        'prices_updated_body' => 'تم تحديث :count سجل بنجاح.',
        'delivery_prices_initialized' => 'تم تهيئة أسعار التوصيل',
        'delivery_prices_initialized_body' => 'تم إضافة :count مدينة بسعر توصيل 0.00 دولار',
        'all_cities_have_prices' => 'جميع المدن لديها بالفعل أسعار توصيل لهذا المتجر',
    ],
];

