<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PaginatedResourcePayloadDto;
use App\Http\Resources\Admin\QuestionCollection;
use App\Repositories\Contracts\AdminExamRepositoryInterface;
use App\Repositories\Contracts\AdminQuestionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AdminQuestionService
{
    public function __construct(
        public AdminExamRepositoryInterface $examRepository,
        public AdminQuestionRepositoryInterface $questionRepository,
    ) {}

    /** @return array<string, mixed> */
    public function indexPayload(int $examId, int $perPage = 50): array
    {
        $exam = $this->examRepository->findById($examId);
        if ($exam === null) {
            throw (new ModelNotFoundException())->setModel('exam', [$examId]);
        }

        $questions = $this->questionRepository->paginateForExam($examId, $perPage);
        /** @var array<string, mixed> $questionCollection */
        $questionCollection = (new QuestionCollection($questions))->response()->getData(true);
        $payload = PaginatedResourcePayloadDto::fromCollectionAndPaginator($questionCollection, $questions);

        return [
            'exam' => ['id' => $exam->id, 'name' => $exam->name],
            'questions' => $payload->toArray(),
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(int $examId, array $data): void
    {
        $exam = $this->examRepository->findById($examId);
        if ($exam === null) {
            throw (new ModelNotFoundException())->setModel('exam', [$examId]);
        }

        DB::transaction(function () use ($exam, $data): void {
            $question = $this->questionRepository->createForExam($exam->id, $data);
            $this->questionRepository->replaceAnswers($question, $data['answers']);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(int $examId, int $questionId, array $data): void
    {
        $question = $this->questionRepository->findById($questionId);
        if ($question === null || $question->exam_id !== $examId) {
            throw (new ModelNotFoundException())->setModel('question', [$questionId]);
        }

        DB::transaction(function () use ($question, $data): void {
            $this->questionRepository->update($question, $data);
            $this->questionRepository->replaceAnswers($question, $data['answers']);
        });
    }

    public function delete(int $examId, int $questionId): void
    {
        $question = $this->questionRepository->findById($questionId);
        if ($question === null || $question->exam_id !== $examId) {
            throw (new ModelNotFoundException())->setModel('question', [$questionId]);
        }

        $this->questionRepository->delete($question);
    }
}
