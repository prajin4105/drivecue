<?php

return [
    'test' => [
        // The fallback names keep existing test integrations working during migration.
        'key_id' => env('RAZORPAY_TEST_KEY_ID', env('RAZORPAY_KEY_ID')),
        'key_secret' => env('RAZORPAY_TEST_KEY_SECRET', env('RAZORPAY_KEY_SECRET')),
        'webhook_secret' => env('RAZORPAY_TEST_WEBHOOK_SECRET', env('RAZORPAY_WEBHOOK_SECRET')),
    ],
    'live' => [
        'key_id' => env('RAZORPAY_LIVE_KEY_ID'),
        'key_secret' => env('RAZORPAY_LIVE_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_LIVE_WEBHOOK_SECRET'),
    ],
];
