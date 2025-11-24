<?php

return [

    /*
     * The disk on which to store added files and derived images. Choose
     * one or more of the disks you've configured in config/filesystems.php.
     */
    'disk_name' => env('MEDIA_DISK', 'public'),

    /*
     * The maximum file size of an item in bytes.
     * Adding a larger file will result in an exception.
     */
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 1024 * 1024 * 100), // 100MB

    /*
     * This queue will be used to generate derived and responsive images.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /*
     * The fully qualified class name of the media model.
     */
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,

    'url_generator' => App\Support\MediaUrlGenerator::class,

    /*
     * Whether the media library should try to optimize all handled uploads (remove EXIF data etc)
     */
    'enable_for_max_width_optimization' => true,

    /*
     * The path where to store temporary files while processing variants.
     * If set to null, storage_path('media-library/temp') will be used.
     */
    'temp_media_path' => null,

    /*
     * Whether to use common media conversions
     */
    'use_default_conversions' => true,

    'conversions' => [
        /*
         * Create this conversion for every image
         */
        'default' => [
            'perform_on_collections' => ['default'],
            'nonQueued' => false,
            'nonDeletePreserving' => false,
        ],
        'thumb' => [
            'perform_on_collections' => ['avatars'],
            'nonQueued' => false,
            'nonDeletePreserving' => false,
        ],
    ],

    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '--max=85',
            '--force',
            '--progressive',
            '--strip-all',
            '--all-progressive',
        ],
        Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--force',
        ],
        Spatie\ImageOptimizer\Optimizers\Optipng::class => [
            '-i0',
            '-o2',
            '-zc9',
            '-zm8',
            '-zs0',
            '-f0-5',
        ],
        Spatie\ImageOptimizer\Optimizers\Svgo::class => [
            '--disable=cleanupEnableBackground',
        ],
        Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
            '-b',
            '-O3',
        ],
        Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
            '-m 6',
        ],
    ],

    /*
     * These generators will be called for every file addition & every file delete.
     */
    'image_generators' => [
        // Spatie\MediaLibrary\ImageGenerators\ImageGenerator::class,
    ],

    /*
     * Versions files. Size keys must be `int`, other keys must be `string`.
     */
    'versions' => [
        // 'thumb' => [
        //     'size' => [100, 100],
        // ],
    ],

    /*
     * By default all 'png', 'jpg', 'jpeg', 'gif', 'webp' files are considered as image and jpg as video.
     * Spatie\MediaLibrary\Conversions\ImageGenerator recognizes image media types,
     * Spatie\MediaLibrary\Conversions\VideoGenerator recognizes video media types.
     */
    'image_drivers' => [
        'gd' => Spatie\MediaLibrary\ImageGenerators\ImageGenerator::class,
        'imagick' => Spatie\MediaLibrary\ImageGenerators\ImageGenerator::class,
    ],

    'video_drivers' => [
        'ffmpeg' => Spatie\MediaLibrary\Conversions\VideoGenerator::class,
    ],

    'custom_headers' => [
        // 'CacheControl' => 'max-age:604800',
    ],

    'response_headers' => [
        'download' => [
            'Content-Disposition' => 'attachment; filename=":filename"',
        ],
    ],

    'path_generator' => \Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    /*
     * Whether to activate versioning of media files.
     */
    'enable_versioning' => false,

    /*
     * The path where to store log files for media generator jobs.
     * Leave empty to disable logging.
     */
    'log_channel_name' => env('MEDIA_LOG_CHANNEL', false),

    'temporary_directory_path' => null,

];
