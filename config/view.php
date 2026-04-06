<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Use a dedicated writable cache directory instead of the tracked
    | `storage/framework/views` folder, which avoids Windows rename conflicts
    | with compiled Blade files.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'speeda-framework-views'
    ),

];
