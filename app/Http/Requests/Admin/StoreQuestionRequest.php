<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'position' => ['required', 'integer', 'min:1'],
            'content' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'answers' => ['required', 'array', 'min:2'],
            'answers.*.content' => ['required', 'string'],
            'answers.*.is_correct' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $correctAnswers = collect($this->input('answers', []))
                ->filter(fn (array $answer): bool => (bool) ($answer['is_correct'] ?? false))
                ->count();

            if ($correctAnswers !== 1) {
                $validator->errors()->add('answers', 'Dokładnie jedna odpowiedź musi być poprawna.');
            }
        });
    }
}
