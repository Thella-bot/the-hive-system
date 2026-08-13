<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year'       => 'required|integer|min:2000|max:2099|unique:academic_years,name,' . $this->route('academic_year')?->id,
            'is_current' => 'boolean',
        ];
    }
}
