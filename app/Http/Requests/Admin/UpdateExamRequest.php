<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
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
            'exam_authority_id' => ['required', 'integer', 'exists:exam_authorities,id'],
            'exam_category_id' => ['required', 'integer', 'exists:exam_categories,id'],
            'exam_class_id' => ['nullable', 'integer', 'exists:exam_classes,id'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
        ];
    }
}
