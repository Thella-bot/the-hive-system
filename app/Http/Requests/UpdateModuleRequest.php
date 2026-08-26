<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Module;
use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = $this->route('module');
        return $this->user()?->can('update', $module) ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:modules,code,' . $this->route('module')?->id,
            'description' => 'nullable|string',
            'credits'     => 'required|integer|min:1',
            'delivery_mode' => 'required|string|in:in_person,online,hybrid',
            'meeting_platform' => 'required_if:delivery_mode,online,hybrid|string|max:100',
            'meeting_link' => 'required_if:delivery_mode,online,hybrid|url|max:500',
            'location' => 'required_if:delivery_mode,in_person,hybrid|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'programme_id' => 'required|exists:programmes,id',
        ];
    }
}
