<?php

return [
    'temporary_disk' => env('UPLOADS_TEMPORARY_DISK', 'public'),
    'temporary_directory' => env('UPLOADS_TEMPORARY_DIRECTORY', 'tmp/uploads'),
    'temporary_ttl_hours' => (int) env('UPLOADS_TEMPORARY_TTL_HOURS', 24),
    'max_image_size' => env('UPLOADS_MAX_IMAGE_SIZE', '5mb'),
];
