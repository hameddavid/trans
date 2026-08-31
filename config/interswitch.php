<?php

return [
    'merchant_code' => env('INTERSWITCH_MERCHANT_CODE', ''),
    'pay_item_id' => env('INTERSWITCH_PAY_ITEM_ID', ''),
    'mac_key' => env('INTERSWITCH_MAC_KEY', ''),

    'base_url' => env('INTERSWITCH_BASE_URL', 'https://webpay.interswitchng.com'),
    'payment_url' => env('INTERSWITCH_PAYMENT_URL', 'https://webpay.interswitchng.com/collections/w/pay'),
    'query_url' => env('INTERSWITCH_QUERY_URL', 'https://webpay.interswitchng.com/collections/api/v1/gettransaction.json'),

    'redirect_url' => env('INTERSWITCH_REDIRECT_URL', ''),
    'webhook_secret' => env('INTERSWITCH_WEBHOOK_SECRET', ''),

    'currency_code' => env('INTERSWITCH_CURRENCY_CODE', '566'), // NGN
    'mode' => env('INTERSWITCH_MODE', 'LIVE'), // TEST or LIVE

    'pay_item_ids' => [
        'soft' => env('INTERSWITCH_ITEM_SOFT', ''),
        'wes' => env('INTERSWITCH_ITEM_WES', ''),
        'nigeria' => env('INTERSWITCH_ITEM_NIGERIA', ''),
        'africa' => env('INTERSWITCH_ITEM_AFRICA', ''),
        'america' => env('INTERSWITCH_ITEM_AMERICA', ''),
        'asia' => env('INTERSWITCH_ITEM_ASIA', ''),
        'australia' => env('INTERSWITCH_ITEM_AUSTRALIA', ''),
        'europe' => env('INTERSWITCH_ITEM_EUROPE', ''),
        'canada' => env('INTERSWITCH_ITEM_CANADA', ''),
        'degree' => env('INTERSWITCH_ITEM_DEGREE', ''),
    ],
];
