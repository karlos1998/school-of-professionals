<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateExamSettingsRequest;
use App\Services\Admin\AdminExamSettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminExamSettingsController extends Controller
{
    public function __construct(public AdminExamSettingsService $adminExamSettingsService) {}

    public function index(): Response
    {
        return Inertia::render('Admin/ExamSettingsPage', $this->adminExamSettingsService->indexPayload());
    }

    public function update(UpdateExamSettingsRequest $request): RedirectResponse
    {
        /** @var array{question_limit:int,passing_threshold:int} $data */
        $data = $request->validated();
        $this->adminExamSettingsService->update($data);

        return back()->with('success', 'Ustawienia egzaminu zostały zapisane.');
    }
}
