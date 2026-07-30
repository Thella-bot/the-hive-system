<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'body'            => 'required|string',
            'body_html'       => 'nullable|string',
            'category'        => 'nullable|string|max:50',
            'target_roles'    => 'nullable|array',
            'target_modules'  => 'nullable|array',
            'target_modules.*'=> 'exists:modules,id',
            'is_pinned'       => 'nullable|boolean',
            'priority'        => 'nullable|in:normal,urgent,emergency',
            'expires_at'      => 'nullable|date',
        ];
    }
}