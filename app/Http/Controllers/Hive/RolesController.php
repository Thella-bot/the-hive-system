<?php

namespace App\Http\Controllers\Hive;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * super-admin always holds every permission and must not be edited away,
     * otherwise the system can be locked out of its own role management.
     */
    private const LOCKED_ROLES = ['super-admin'];

    public function __construct(private readonly AuditService $audit) {}

    public function index()
    {
        $userCountByRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->selectRaw('roles.name, COUNT(*) as total')
            ->groupBy('roles.name')
            ->pluck('total', 'name')
            ->all();

        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn(Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $this->displayName($role->name),
                'is_locked' => in_array($role->name, self::LOCKED_ROLES, true),
                'permission_count' => $role->permissions->count(),
                'user_count' => (int) ($userCountByRole[$role->name] ?? 0),
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('Hive/Roles/Index', [
            'roles' => $roles,
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if (in_array($role->name, self::LOCKED_ROLES, true)) {
            return back()->with('error', "The {$role->name} role cannot be modified.");
        }

        $validated = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'max:100'],
        ]);

        $requested = array_values(array_unique($validated['permissions']));

        // Ignore unknown permission names rather than failing the whole save.
        $validNames = Permission::pluck('name')->all();
        $requested = array_values(array_intersect($requested, $validNames));

        $before = $role->permissions->pluck('name')->all();

        // sync() detaches anything not in the submitted list.
        $role->syncPermissions($requested);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $added = array_values(array_diff($requested, $before));
        $removed = array_values(array_diff($before, $requested));

        if ($added || $removed) {
            $this->audit->log('role.permissions_updated', newValues: [
                'role' => $role->name,
                'added' => $added,
                'removed' => $removed,
            ]);
        }

        return back()->with('success', $role->name . ' permissions updated.');
    }

    /**
     * Group permissions by the leading segment of their name so the matrix is
     * readable rather than one long list of ~180 rows.
     *
     * @return array<string, array<string>>
     */
    private function permissionGroups(): array
    {
        return Permission::orderBy('name')
            ->pluck('name')
            ->groupBy(fn(string $name) => $this->groupLabel(explode('-', $name)[0]))
            ->all();
    }

    private function groupLabel(string $prefix): string
    {
        return match ($prefix) {
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'manage' => 'Manage',
            'approve' => 'Approve',
            'assign' => 'Assign',
            'issue' => 'Issue',
            'return' => 'Return',
            'record' => 'Record',
            'process' => 'Process',
            'generate' => 'Generate',
            'export' => 'Export',
            'import' => 'Import',
            'send' => 'Send',
            'report' => 'Report',
            default => ucfirst($prefix),
        };
    }

    private function displayName(string $name): string
    {
        try {
            return UserRole::from($name)->displayName();
        } catch (\ValueError) {
            // `unapproved` and any custom roles are not in the enum.
            return ucwords(str_replace(['-', '_'], ' ', $name));
        }
    }
}
