<?php

namespace App\Http\Controllers\ExamFlow;

use App\Domain\Exams\Exceptions\ExamFlowException;
use App\Http\Controllers\Controller;
use App\Services\Exams\ExamFlowService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuthorityTestsPageController extends Controller
{
    public function __construct(
        private readonly ExamFlowService $examFlowService,
    ) {}

    public function __invoke(string $authority): Response|RedirectResponse
    {
        try {
            $payload = $this->examFlowService->getAuthorityTests($authority);
        } catch (ExamFlowException) {
            abort(404);
        }

        return Inertia::render('AuthorityTestsPage', $payload + [
            'homeUrl' => route('exam-flow.welcome'),
        ]);
    }
}
