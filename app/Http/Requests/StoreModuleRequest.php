<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:modules',
            'description' => 'nullable|string',
            'credits'     => 'required|integer|min:1',
            'department_id'=> 'required|exists:departments,id',
            'programme_id'=> 'required|exists:programmes,id',
        ];
    }
}