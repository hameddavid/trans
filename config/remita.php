<?php

return [
    'merchant_id' => env('REMITA_MERCHANT_ID', '4161150426'),
    'api_key' => env('REMITA_API_KEY', '258341'),
    'base_url' => env('REMITA_BASE_URL', 'https://login.remita.net/remita/ecomm'),
    'init_url' => env('REMITA_INIT_URL', 'https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit'),
    'inline_script' => env('REMITA_INLINE_SCRIPT', 'https://remitademo.net/payment/v1/remita-pay-inline.bundle.js'),
    'inline_key' => env('REMITA_INLINE_KEY', ''),

    'service_types' => [
        'soft' => env('REMITA_ST_SOFT', '9928147511'),
        'wes' => env('REMITA_ST_WES', '9928138149'),
        'nigeria' => env('REMITA_ST_NIGERIA', '8201452263'),
        'africa' => env('REMITA_ST_AFRICA', '9928159113'),
        'america' => env('REMITA_ST_AMERICA', '9928130748'),
        'asia' => env('REMITA_ST_ASIA', '8201462144'),
        'australia' => env('REMITA_ST_AUSTRALIA', '9927961794'),
        'europe' => env('REMITA_ST_EUROPE', '8201376113'),
        'canada' => env('REMITA_ST_CANADA', '8201449890'),
        'degree' => env('REMITA_ST_DEGREE', '9928095215'),
    ],

    'flutterwave' => [
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY', ''),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY', ''),
    ],
];
