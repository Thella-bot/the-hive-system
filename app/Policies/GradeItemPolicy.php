<?php namespace App\Policies;
use App\Models\GradeItem;
use App\Models\User;
class GradeItemPolicy
{
    public function viewAny(User $user) { return $user->hasRole(['admin','instructor','student']); }
    public function create(User $user) { return $user->hasRole(['admin','instructor']); }
    public function update(User $user, GradeItem $gradeItem) {
        // Only instructors of the module (or admin) can update weight, name etc.
        if ($user->hasRole('admin')) return true;
        return $user->hasRole('instructor') && $gradeItem->module->instructors()->where('user_id', $user->id)->exists();
    }
}