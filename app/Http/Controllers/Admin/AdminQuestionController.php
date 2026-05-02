<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Services\Admin\AdminQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminQuestionController extends Controller
{
    public function __construct(public AdminQuestionService $adminQuestionService) {}

    public function index(Request $request, string $examId): Response
    {
        $perPage = $request->integer('per_page', 50);

        return Inertia::render('Admin/QuestionsPage', $this->adminQuestionService->indexPayload((int) $examId, $perPage));
    }

    public function store(StoreQuestionRequest $request, string $examId): RedirectResponse
    {
        $this->adminQuestionService->create((int) $examId, $request->validated());

        return back()->with('success', 'Pytanie zostało dodane.');
    }

    public function update(UpdateQuestionRequest $request, string $examId, string $questionId): RedirectResponse
    {
        $this->adminQuestionService->update((int) $examId, (int) $questionId, $request->validated());

        return back()->with('success', 'Pytanie zostało zaktualizowane.');
    }

    public function destroy(string $examId, string $questionId): RedirectResponse
    {
        $this->adminQuestionService->delete((int) $examId, (int) $questionId);

        return back()->with('success', 'Pytanie zostało usunięte.');
    }
}
