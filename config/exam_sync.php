<?php

return [
    'image_disk' => env('EXAM_SYNC_IMAGE_DISK', 's3'),
    'image_directory' => env('EXAM_SYNC_IMAGE_DIRECTORY', 'exam-questions'),
    'timeout_seconds' => (int) env('EXAM_SYNC_TIMEOUT_SECONDS', 20),
];
