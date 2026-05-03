<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateExamSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'question_limit' => ['required', 'integer', 'min:1', 'max:200'],
            'passing_threshold' => ['required', 'integer', 'min:0', 'max:200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $questionLimit = (int) $this->input('question_limit');
            $passingThreshold = (int) $this->input('passing_threshold');

            if ($passingThreshold > $questionLimit) {
                $validator->errors()->add('passing_threshold', 'Próg zaliczenia nie może być większy niż liczba pytań.');
            }
        });
    }
}
