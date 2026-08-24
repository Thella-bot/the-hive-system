<?php
declare(strict_types=1);

namespace App\Observers;

use App\Services\ReferenceDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleObserver
{
    public function __construct(protected ReferenceDataService $referenceData) {}

    public function saved(Role $role): void
    {
        $this->logChange($role, 'saved');
        $this->referenceData->flush();
    }

    public function deleted(Role $role): void
    {
        $this->logChange($role, 'deleted');
        $this->referenceData->flush();
    }

    private function logChange(Role $role, string $action): void
    {
        $user = Auth::user();

        Log::channel('audit')->info('Role changed', [
            'action'        => $action,
            'role_name'     => $role->name,
            'role_id'       => $role->id,
            'actor_user_id' => $user?->id,
            'actor_email'   => $user?->email,
            'ip_address'    => request()?->ip(),
            'user_agent'    => request()?->userAgent(),
            'timestamp'     => now()->toDateTimeString(),
        ]);
    }
}
