<?php

return [
    'session' => [
        'question_limit' => (int) env('EXAM_SESSION_QUESTION_LIMIT', 20),
        'passing_threshold' => (int) env('EXAM_SESSION_PASSING_THRESHOLD', 16),
    ],
];
