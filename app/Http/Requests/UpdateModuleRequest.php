<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:modules,code,' . $this->route('module')?->id,
            'description' => 'nullable|string',
            'credits'     => 'required|integer|min:1',
            'delivery_mode' => 'required|string|in:in_person,online,hybrid',
            'meeting_platform' => 'nullable|string|max:100',
            'meeting_link' => 'nullable|url|max:500',
            'location' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'programme_id' => 'required|exists:programmes,id',
        ];
    }
}
