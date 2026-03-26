<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meta (Facebook) Pixel Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Meta Pixel tracking and Conversion API (CAPI).
    | Set FACEBOOK_PIXEL_ID in your .env to enable client-side tracking.
    | Set FACEBOOK_CAPI_ACCESS_TOKEN to enable server-side event tracking.
    |
    */

    'pixel_id' => env('FACEBOOK_PIXEL_ID', ''),

    'access_token' => env('FACEBOOK_CAPI_ACCESS_TOKEN', ''),

    // Automatically enabled when pixel_id is set
    'enabled' => !empty(env('FACEBOOK_PIXEL_ID', '')),

    // CAPI requires both pixel_id and access_token
    'capi_enabled' => !empty(env('FACEBOOK_PIXEL_ID', '')) && !empty(env('FACEBOOK_CAPI_ACCESS_TOKEN', '')),

    // Graph API version for CAPI
    'graph_api_version' => 'v21.0',

];
