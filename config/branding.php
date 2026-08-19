<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brand fonts (limited Arabic set)
    |--------------------------------------------------------------------------
    |
    | Top Arabic UI fonts widely used on modern websites:
    | Cairo, Tajawal, IBM Plex Sans Arabic.
    |
    */
    'default_font' => 'cairo',

    'fonts' => [
        'cairo' => [
            'label' => 'Cairo',
            'label_ar' => 'القاهرة',
            'description' => 'الأكثر استخداماً في المواقع العربية — مناسب للعناوين والواجهات',
            'family' => "'Cairo', 'Segoe UI', Tahoma, sans-serif",
            'bunny' => 'cairo:400,500,700',
        ],
        'tajawal' => [
            'label' => 'Tajawal',
            'label_ar' => 'تجوال',
            'description' => 'واضح جداً في النماذج ولوحات التحكم',
            'family' => "'Tajawal', 'Segoe UI', Tahoma, sans-serif",
            'bunny' => 'tajawal:400,500,700',
        ],
        'ibm-plex-sans-arabic' => [
            'label' => 'IBM Plex Sans Arabic',
            'label_ar' => 'آي بي إم بليكس',
            'description' => 'مظهر تقني واحترافي مناسب للمنتجات الرقمية',
            'family' => "'IBM Plex Sans Arabic', 'Segoe UI', Tahoma, sans-serif",
            'bunny' => 'ibm-plex-sans-arabic:400,500,700',
        ],
    ],
];
