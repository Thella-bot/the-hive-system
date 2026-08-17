<?php

namespace App\Providers;

use App\Observers\DepartmentObserver;
use App\Observers\ProgrammeObserver;
use App\Observers\RoleObserver;
use App\Services\ReferenceDataService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use App\Models\Programme;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/hive.php'));

        $referenceData = app(ReferenceDataService::class);

        Role::observe(RoleObserver::class);
        Department::observe(DepartmentObserver::class);
        Programme::observe(ProgrammeObserver::class);
    }
}