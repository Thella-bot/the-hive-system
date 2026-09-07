<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\GeneratedDocument;
use App\Models\User;

class GeneratedDocumentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
            'admissions-officer',
            'examination-cell',
            'registrar',
            'finance',
            'procurement-manager',
            'storekeeper',
            'hr-manager',
            'librarian',
            'career-services',
            'events-pr-manager',
            'cafeteria-manager',
        ]);
    }

    public function view(User $user, GeneratedDocument $document): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
            'admissions-officer',
            'examination-cell',
            'registrar',
            'finance',
            'procurement-manager',
            'storekeeper',
            'hr-manager',
            'librarian',
            'career-services',
            'events-pr-manager',
            'cafeteria-manager',
        ]);
    }

    public function generate(User $user): bool
    {
        return $this->create($user);
    }

    public function audit(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'registrar',
            'finance',
        ]);
    }

    public function batchAudit(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}