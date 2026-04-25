<?php

namespace App\Http\Controllers\ExamFlow;

use App\Domain\Exams\Exceptions\ExamFlowException;
use App\Http\Controllers\Controller;
use App\Services\Exams\ExamFlowService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExamSessionPageController extends Controller
{
    public function __construct(
        private readonly ExamFlowService $examFlowService,
    ) {}

    public function __invoke(string $authority, string $test, ?string $class = null): Response|RedirectResponse
    {
        try {
            $payload = $this->examFlowService->resolveExamSession(
                authoritySlug: $authority,
                testSlug: $test,
                classSlug: $class,
            );
        } catch (ExamFlowException) {
            abort(404);
        }

        return Inertia::render('ExamSessionPage', $payload);
    }
}
