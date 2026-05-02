<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Http\Resources\Admin\ExamResource;
use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\ExamClass;
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
        $exams = $this->adminExamService->paginate($perPage);

        return Inertia::render('Admin/ExamsPage', [
            'exams' => ExamResource::collection($exams),
            'pagination' => [
                'current_page' => $exams->currentPage(),
                'last_page' => $exams->lastPage(),
                'per_page' => $exams->perPage(),
                'total' => $exams->total(),
            ],
            'authorities' => ExamAuthority::query()->orderBy('name')->get(['id', 'name']),
            'categories' => ExamCategory::query()->orderBy('name')->get(['id', 'name']),
            'classes' => ExamClass::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->adminExamService->create($request->validated());

        return back()->with('success', 'Test został dodany.');
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->adminExamService->update($exam, $request->validated());

        return back()->with('success', 'Test został zaktualizowany.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->adminExamService->delete($exam);

        return back()->with('success', 'Test został usunięty.');
    }
}
