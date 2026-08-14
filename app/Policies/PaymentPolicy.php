<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'hr-manager']);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'hr-manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'registrar']);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}
