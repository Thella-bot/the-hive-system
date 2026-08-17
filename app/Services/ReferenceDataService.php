<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Programme;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class ReferenceDataService
{
    private function cached(string $key, \Closure $callback): mixed
    {
        try {
            return Cache::rememberForever($key, $callback);
        } catch (\Throwable) {
            return $callback();
        }
    }

    private function forget(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (\Throwable) {
            // Ignore cache failures
        }
    }

    public function roles(): \Illuminate\Support\Collection
    {
        return $this->cached('reference_data.roles', function () {
            return Role::orderBy('name')->get(['id', 'name']);
        });
    }

    public function departments(): \Illuminate\Support\Collection
    {
        return $this->cached('reference_data.departments', function () {
            return Department::active()->select('id', 'name')->get();
        });
    }

    public function programmes(): \Illuminate\Support\Collection
    {
        return $this->cached('reference_data.programmes', function () {
            return Programme::orderBy('name')->get(['id', 'name', 'department_id']);
        });
    }

    public function all(): array
    {
        return $this->cached('reference_data.all', function () {
            return [
                'roles' => $this->roles(),
                'departments' => $this->departments(),
                'programmes' => $this->programmes(),
            ];
        });
    }

    public function flush(): void
    {
        $this->forget('reference_data.roles');
        $this->forget('reference_data.departments');
        $this->forget('reference_data.programmes');
        $this->forget('reference_data.all');
    }
}
