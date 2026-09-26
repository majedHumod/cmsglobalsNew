<?php

/**
 * قنوات دفع المدرب أو النادي (اشتراكات العملاء).
 * دفع اشتراك المنصة الرئيسية يبقى في config/services.php ولا يُدار من هنا.
 *
 * كل قناة تحمل شعارها الرسمي المُجهّز مسبقاً داخل public/images/payment-logos
 * فيُعرض تلقائياً للمدرب/النادي والعميل دون الحاجة لرفعه يدوياً.
 */
return [
    'channels' => [
        'paylink' => [
            'label' => 'بيلينك',
            'region' => 'saudi',
            'settlement' => 'instant',
            'logo' => '/images/payment-logos/paylink.webp',
            'description' => 'Paylink بحساب النادي أو المدرب الخاص (منفصل عن Paylink الخاص بالمنصة). مدى، فيزا، ماستركارد، Apple Pay، STC Pay وتمارا. التفعيل فوري بعد تأكيد الدفع.',
            'fields' => [
                'mode' => [
                    'label' => 'البيئة',
                    'type' => 'select',
                    'options' => ['live' => 'فعلي', 'test' => 'تجريبي (Sandbox)'],
                    'default' => 'live',
                    'required' => true,
                ],
                'api_id' => ['label' => 'API ID', 'secret' => false, 'required' => true],
                'secret_key' => ['label' => 'المفتاح السري (Secret Key)', 'secret' => true, 'required' => true],
            ],
        ],
        'moyasar' => [
            'label' => 'ميسر',
            'region' => 'saudi',
            'settlement' => 'instant',
            'logo' => '/images/payment-logos/moyasar.png',
            'description' => 'مدى، فيزا، ماستركارد، Apple Pay و STC Pay. يُفعَّل الاشتراك فور تأكيد الدفع.',
            'fields' => [
                'secret_key' => ['label' => 'المفتاح السري', 'secret' => true, 'required' => true],
                'publishable_key' => ['label' => 'المفتاح العام', 'secret' => false, 'required' => false],
            ],
        ],
        'tap' => [
            'label' => 'تاب',
            'region' => 'saudi',
            'settlement' => 'instant',
            'logo' => '/images/payment-logos/tap.svg',
            'description' => 'Tap Payments داخل السعودية. التفعيل يتم بعد اكتمال الدفع مباشرة.',
            'fields' => [
                'secret_key' => ['label' => 'المفتاح السري', 'secret' => true, 'required' => true],
            ],
        ],
        'paytabs' => [
            'label' => 'باي تابس',
            'region' => 'saudi',
            'settlement' => 'instant',
            'logo' => '/images/payment-logos/paytabs.svg',
            'description' => 'PayTabs السعودية. العميل يُحوَّل لصفحة الدفع ثم يُفعَّل الاشتراك بعد النجاح.',
            'fields' => [
                'profile_id' => ['label' => 'معرّف الملف (Profile ID)', 'secret' => false, 'required' => true],
                'server_key' => ['label' => 'مفتاح الخادم (Server Key)', 'secret' => true, 'required' => true],
            ],
        ],
        'hyperpay' => [
            'label' => 'هايبر باي',
            'region' => 'saudi',
            'settlement' => 'instant',
            'logo' => '/images/payment-logos/hyperpay.svg',
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
            'logo' => '/images/payment-logos/myfatoorah.png',
            'description' => 'MyFatoorah في السعودية. الاشتراك يُفعَّل مباشرة بعد الدفع.',
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
            'logo' => '/images/payment-logos/stripe.svg',
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
            'logo' => '/images/payment-logos/bank-transfer.svg',
            'description' => 'يختار العميل الحساب البنكي المناسب من قائمة حسابات النادي، ويبقى الطلب معلقاً حتى يؤكد النادي استلام المبلغ.',
            // هذه القناة تخزن عدة حسابات بنكية بدل حقول ثابتة؛ كل عنصر يتبع مخطط 'fields' أدناه.
            'repeatable' => 'accounts',
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
