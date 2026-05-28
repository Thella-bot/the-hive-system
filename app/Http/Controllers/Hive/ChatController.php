<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get modules the user is enrolled in (for students) or teaching (for instructors)
        $modules = $user->isStudent()
            ? $user->modules()->with('instructors')->get()
            : $user->instructedModules()->with('programme')->get();

        return inertia('Hive/Modules/ChatIndex', [
            'modules' => $modules,
        ]);
    }

    public function show(Module $module)
    {
        $user = auth()->user();

        // Check if user is enrolled in this module or is an instructor
        $isEnrolled = $user->modules()->where('module_id', $module->id)->exists();
        $isInstructor = $module->instructors()->where('user_id', $user->id)->exists();
        $isAdmin = $user->hasAnyRole(['super-admin', 'school-admin']);

        if (!$isEnrolled && !$isInstructor && !$isAdmin) {
            abort(403, 'You are not enrolled in this module.');
        }

        return inertia('Hive/Modules/Chat', [
            'module' => $module,
        ]);
    }
}