<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Http\Resources\Admin\QuestionResource;
use App\Models\Exam;
use App\Models\Question;
use App\Services\Admin\AdminQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminQuestionController extends Controller
{
    public function __construct(public AdminQuestionService $adminQuestionService) {}

    public function index(Request $request, Exam $exam): Response
    {
        $perPage = $request->integer('per_page', 50);
        $questions = $this->adminQuestionService->paginateForExam($exam, $perPage);

        return Inertia::render('Admin/QuestionsPage', [
            'exam' => ['id' => $exam->id, 'name' => $exam->name],
            'questions' => QuestionResource::collection($questions),
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
            ],
        ]);
    }

    public function store(StoreQuestionRequest $request, Exam $exam): RedirectResponse
    {
        $this->adminQuestionService->create($exam, $request->validated());

        return back()->with('success', 'Pytanie zostało dodane.');
    }

    public function update(UpdateQuestionRequest $request, Exam $exam, Question $question): RedirectResponse
    {
        abort_unless($question->exam_id === $exam->id, 404);
        $this->adminQuestionService->update($question, $request->validated());

        return back()->with('success', 'Pytanie zostało zaktualizowane.');
    }

    public function destroy(Exam $exam, Question $question): RedirectResponse
    {
        abort_unless($question->exam_id === $exam->id, 404);
        $this->adminQuestionService->delete($question);

        return back()->with('success', 'Pytanie zostało usunięte.');
    }
}
