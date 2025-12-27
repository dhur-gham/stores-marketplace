<?php

return [
    'label' => 'خطة الخصم',
    'plural_label' => 'خطط الخصم',
    'navigation_label' => 'خطط الخصم',
    'create' => 'إنشاء خطة خصم',
    'edit' => 'تعديل خطة خصم',
    'view' => 'عرض خطة خصم',
    'delete' => 'حذف خطة خصم',
    'delete_any' => 'حذف خطط الخصم',
    'export' => 'تصدير خطط الخصم',
    'import' => 'استيراد خطط الخصم',
    'fields' => [
        'name' => 'اسم الخطة',
        'store' => 'المتجر',
        'store_id' => 'المتجر',
        'discount_type' => 'نوع الخصم',
        'discount_value' => 'قيمة الخصم',
        'start_date' => 'تاريخ البدء',
        'end_date' => 'تاريخ الانتهاء',
        'status' => 'الحالة',
        'created_by_user_id' => 'تم الإنشاء بواسطة',
    ],
    'sections' => [
        'plan_information' => 'معلومات الخطة',
        'discount_details' => 'تفاصيل الخصم',
        'schedule' => 'الجدولة',
    ],
    'status' => [
        'scheduled' => 'مجدول',
        'active' => 'نشط',
        'expired' => 'منتهي',
    ],
    'discount_type' => [
        'percentage' => 'نسبة مئوية',
        'fixed' => 'مبلغ ثابت',
    ],
    'filters' => [
        'status' => 'الحالة',
        'discount_type' => 'نوع الخصم',
        'store' => 'المتجر',
    ],
    'actions' => [
        'activate' => 'تفعيل',
        'expire' => 'انتهاء',
    ],
    'notifications' => [
        'plan_activated' => 'تم تفعيل خطة الخصم',
        'plan_activated_body' => 'تم تفعيل خطة الخصم بنجاح.',
        'plan_expired' => 'انتهت خطة الخصم',
        'plan_expired_body' => 'انتهت خطة الخصم بنجاح.',
    ],
    'relation_managers' => [
        'products' => [
            'title' => 'المنتجات',
            'columns' => [
                'image' => 'الصورة',
                'name' => 'اسم المنتج',
                'sku' => 'رمز المنتج',
                'price' => 'السعر',
                'discounted_price' => 'السعر المخفض',
                'stock' => 'المخزون',
            ],
            'form' => [
                'products' => 'المنتجات',
            ],
            'actions' => [
                'add_products' => 'إضافة منتجات',
                'remove' => 'إزالة',
                'remove_selected' => 'إزالة المحدد',
            ],
            'notifications' => [
                'products_added' => 'تمت إضافة المنتجات بنجاح',
                'product_removed' => 'تمت إزالة المنتج بنجاح',
                'products_removed' => 'تمت إزالة المنتجات بنجاح',
            ],
        ],
    ],
];
