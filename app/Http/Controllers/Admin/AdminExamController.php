<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Services\Admin\AdminExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminExamController extends Controller
{
    public function __construct(public AdminExamService $adminExamService) {}

    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 50);

        return Inertia::render('Admin/ExamsPage', $this->adminExamService->indexPayload($perPage));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->adminExamService->create($request->validated());

        return back()->with('success', 'Test został dodany.');
    }

    public function update(UpdateExamRequest $request, string $examId): RedirectResponse
    {
        $this->adminExamService->update((int) $examId, $request->validated());

        return back()->with('success', 'Test został zaktualizowany.');
    }

    public function destroy(string $examId): RedirectResponse
    {
        $this->adminExamService->delete((int) $examId);

        return back()->with('success', 'Test został usunięty.');
    }
}
