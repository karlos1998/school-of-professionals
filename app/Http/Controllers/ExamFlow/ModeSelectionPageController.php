<?php

namespace App\Http\Controllers\ExamFlow;

use App\Domain\Exams\Exceptions\ExamFlowException;
use App\Http\Controllers\Controller;
use App\Services\Exams\ExamFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModeSelectionPageController extends Controller
{
    public function __construct(
        private readonly ExamFlowService $examFlowService,
    ) {}

    public function __invoke(
        Request $request,
        string $authority,
        string $test,
    ): Response|RedirectResponse {
        try {
            $payload = $this->examFlowService->getModeSelectionPayload(
                authoritySlug: $authority,
                testSlug: $test,
                classSlug: $request->route('class'),
            );
        } catch (ExamFlowException) {
            abort(404);
        }

        return Inertia::render('ModeSelectionPage', $payload);
    }
}
