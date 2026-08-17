<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Programme;
use Spatie\Permission\Models\Role;

class ReferenceDataService
{
    public function roles(): \Illuminate\Support\Collection
    {
        return cache()->rememberForever('reference_data.roles', function () {
            return Role::orderBy('name')->get(['id', 'name']);
        });
    }

    public function departments(): \Illuminate\Support\Collection
    {
        return cache()->rememberForever('reference_data.departments', function () {
            return Department::active()->select('id', 'name')->get();
        });
    }

    public function programmes(): \Illuminate\Support\Collection
    {
        return cache()->rememberForever('reference_data.programmes', function () {
            return Programme::orderBy('name')->get(['id', 'name', 'department_id']);
        });
    }

    public function all(): array
    {
        return cache()->rememberForever('reference_data.all', function () {
            return [
                'roles' => $this->roles(),
                'departments' => $this->departments(),
                'programmes' => $this->programmes(),
            ];
        });
    }

    public function flush(): void
    {
        cache()->forget('reference_data.roles');
        cache()->forget('reference_data.departments');
        cache()->forget('reference_data.programmes');
        cache()->forget('reference_data.all');
    }
}
