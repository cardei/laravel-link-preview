<?php

return [
    'enable_logging' => env('LINK_PREVIEW_ENABLE_LOGGING', true),
    'cache_duration' => env('LINK_PREVIEW_CACHE_DURATION', 10), // Cache duration in minutes
    'youtube_api_key' => env('LINK_PREVIEW_YOUTUBE_API_KEY', ''),

];
