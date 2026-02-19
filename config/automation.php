<?php

return [
    'ai_caption' => [
        'endpoint' => env('AI_CAPTION_API_URL'),
        'key' => env('AI_CAPTION_API_KEY'),
        'model' => env('AI_CAPTION_MODEL', 'gpt-4o-mini'),
    ],

    'ai_image' => [
        'endpoint' => env('AI_IMAGE_API_URL'),
        'key' => env('AI_IMAGE_API_KEY'),
        'size' => env('AI_IMAGE_SIZE', '1024x1024'),
    ],

    'facebook' => [
        'page_id' => env('FACEBOOK_PAGE_ID'),
        'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    ],
];
