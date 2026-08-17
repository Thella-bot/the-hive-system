<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'registrar']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'registrar'])
            || $invoice->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'registrar']);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'registrar']);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }
}
