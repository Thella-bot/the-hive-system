<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'                 => 'nullable|string|max:255',
            'last_name'                  => 'nullable|string|max:255',
            'date_of_birth'              => 'nullable|date|before:today',
            'phone'                      => 'nullable|string|max:20',
            'address'                    => 'nullable|string|max:500',
            'emergency_contact_name'     => 'nullable|string|max:255',
            'emergency_contact_phone'    => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'dietary_restrictions'       => 'nullable|string',
            'bio'                        => 'nullable|string',
            'specialization'             => 'nullable|string|max:255',
            'gender'                     => 'nullable|string|max:20',
            'national_id_number'         => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id_number')->ignore($this->user()->id)],
            'profile_picture'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
