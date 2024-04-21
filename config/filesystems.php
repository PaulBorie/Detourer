<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID', 'e4d98a7a6409001dbd91'),
            'secret' => env('AWS_SECRET_ACCESS_KEY', 'c66c14fdd02b986073f4'),
            'region' => env('AWS_DEFAULT_REGION', 'us-west-2'),
            'bucket' => env('AWS_BUCKET', 'images'),
            'url' => env('AWS_URL','http://localhost:9000'),
            'endpoint' => env('AWS_ENDPOINT', 'http://localhost:9000'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'throw' => true,
        ],

        'minio-temporaryurls' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID', 'e4d98a7a6409001dbd91'),
            'secret' => env('AWS_SECRET_ACCESS_KEY', 'c66c14fdd02b986073f4'),
            'region' => env('AWS_DEFAULT_REGION', 'us-west-2'),
            'bucket' => env('AWS_BUCKET', 'images'),
            'url' => env('AWS_URL','http://localhost:9000'),
            'endpoint' => env('MINIO_ENDPOINT_TEMPORARYURLS', 'http://localhost:9000'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'throw' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        //
    ],

];
