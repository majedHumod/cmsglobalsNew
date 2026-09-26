<?php

/**
 * قنوات دفع المدرب أو النادي (اشتراكات العملاء).
 * دفع اشتراك المنصة الرئيسية يبقى في config/services.php ولا يُدار من هنا.
 */
return [
    'channels' => [
        'moyasar' => [
            'label' => 'ميسر',
            'region' => 'saudi',
            'settlement' => 'instant',
            'description' => 'مدى، فيزا، ماستركارد، Apple Pay و STC Pay. يُفعَّل الاشتراك فور تأكيد الدفع.',
            'fields' => [
                'secret_key' => ['label' => 'المفتاح السري', 'secret' => true, 'required' => true],
                'publishable_key' => ['label' => 'المفتاح العام', 'secret' => false, 'required' => false],
            ],
        ],
        'tap' => [
            'label' => 'تاب',
            'region' => 'saudi',
            'settlement' => 'instant',
            'description' => 'Tap Payments داخل السعودية. التفعيل يتم بعد اكتمال الدفع مباشرة.',
            'fields' => [
                'secret_key' => ['label' => 'المفتاح السري', 'secret' => true, 'required' => true],
            ],
        ],
        'paytabs' => [
            'label' => 'باي تابس',
            'region' => 'saudi',
            'settlement' => 'instant',
            'description' => 'PayTabs السعودية. العميل يُحوَّل لصفحة الدفع ثم يُفعَّل الاشتراك بعد النجاح.',
            'fields' => [
                'profile_id' => ['label' => 'معرّف الملف (Profile ID)', 'secret' => false, 'required' => true],
                'server_key' => ['label' => 'مفتاح الخادم (Server Key)', 'secret' => true, 'required' => true],
            ],
        ],
        'hyperpay' => [
            'label' => 'هايبر باي',
            'region' => 'saudi',
            'settlement' => 'instant',
            'description' => 'HyperPay لبطاقات مدى والائتمان. التفعيل فوري بعد نجاح العملية.',
            'fields' => [
                'mode' => [
                    'label' => 'البيئة',
                    'type' => 'select',
                    'options' => ['test' => 'تجريبي', 'live' => 'فعلي'],
                    'default' => 'test',
                    'required' => true,
                ],
                'entity_id' => ['label' => 'Entity ID', 'secret' => false, 'required' => true],
                'access_token' => ['label' => 'Access Token', 'secret' => true, 'required' => true],
            ],
        ],
        'myfatoorah' => [
            'label' => 'ماي فاتورة',
            'region' => 'saudi',
            'settlement' => 'instant',
            'description' => 'MyFatoorah في السعودية. الاشتراك يُفعَّل مباشرة بعد الدفع.',
            'fields' => [
                'mode' => [
                    'label' => 'البيئة',
                    'type' => 'select',
                    'options' => ['test' => 'تجريبي', 'live' => 'فعلي'],
                    'default' => 'live',
                    'required' => true,
                ],
                'api_key' => ['label' => 'مفتاح API', 'secret' => true, 'required' => true],
            ],
        ],
        'stripe' => [
            'label' => 'سترايب',
            'region' => 'global',
            'settlement' => 'instant',
            'description' => 'دفع عالمي عبر Stripe. مناسب للعملاء خارج قنوات السعودية، والتفعيل فوري بعد الدفع.',
            'fields' => [
                'secret_key' => ['label' => 'المفتاح السري', 'secret' => true, 'required' => true],
                'publishable_key' => ['label' => 'المفتاح العام', 'secret' => false, 'required' => false],
            ],
        ],
        'bank_transfer' => [
            'label' => 'تحويل بنكي',
            'region' => 'manual',
            'settlement' => 'manual',
            'description' => 'يختار العميل التحويل ويعرض له الحساب. يبقى الطلب معلقاً حتى يؤكد النادي استلام المبلغ.',
            'fields' => [
                'bank_name' => ['label' => 'اسم البنك', 'required' => true],
                'account_name' => ['label' => 'اسم صاحب الحساب', 'required' => true],
                'iban' => ['label' => 'الآيبان', 'required' => true],
                'account_number' => ['label' => 'رقم الحساب', 'required' => false],
                'instructions' => ['label' => 'تعليمات للعميل', 'type' => 'textarea', 'required' => false],
            ],
        ],
    ],
];
