<?php

namespace App\Http\Controllers\ExamFlow;

use App\Http\Controllers\Controller;
use App\Services\Exams\ExamFlowService;
use Inertia\Inertia;
use Inertia\Response;

class WelcomePageController extends Controller
{
    public function __construct(
        private readonly ExamFlowService $examFlowService,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('WelcomePage', [
            'authorities' => $this->examFlowService->getAuthoritiesForWelcome(),
        ]);
    }
}
