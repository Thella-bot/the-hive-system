<?php

namespace App\Http\Requests;

use App\Models\Gradable;
use Illuminate\Foundation\Http\FormRequest;

class StoreGradableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Gradable::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'submission_type' => 'required|string|in:file_upload,online_fillable,online_multiple_choice',
            'module_id' => 'required|exists:modules,id',
            'due_date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:0',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'show_correct_answers' => 'nullable|boolean',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'description' => 'nullable|string',
            'max_marks' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:100',
            'max_file_size' => 'nullable|integer|min:1|max:102400',
            'allowed_types' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*.title' => 'required|string|max:255',
            'attachments.*.file' => 'required|file|max:51200',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert boolean strings to actual booleans
        $this->merge([
            'show_correct_answers' => $this->boolean('show_correct_answers'),
            'shuffle_questions' => $this->boolean('shuffle_questions'),
            'shuffle_options' => $this->boolean('shuffle_options'),
        ]);
    }
}
