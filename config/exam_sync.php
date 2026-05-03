<?php

return [
    'image_disk' => env('EXAM_SYNC_IMAGE_DISK', 'public'),
    'image_directory' => env('EXAM_SYNC_IMAGE_DIRECTORY', 'exam-questions'),
    'timeout_seconds' => (int) env('EXAM_SYNC_TIMEOUT_SECONDS', 20),
];
