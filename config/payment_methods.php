<?php
return [
    'mpesa'=>[
        'mpesa_env' => env('MPESA_ENV'),
        'business_short_code' => env('BUSINESS_SHORT_CODE'),
        'pass_key' => env('PASS_KEY'),
        'consumer_key' => env('CONSUMER_KEY'),
        'consumer_secret' => env('CONSUMER_SECRET'),
        'call_back_url' => env('CALLBACK_URL'),
        'party_b' => env('PARTY_B'),
        'transaction_type' => env('TRANSACTION_TYPE','CustomerPayBillOnline')
    ]
];