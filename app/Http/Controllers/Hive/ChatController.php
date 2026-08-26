<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\Department;
use App\Models\Module;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $moduleChannels = $user->isStudent()
            ? $user->modules()->with('instructors')->get()
            : $user->instructedModules()->with('programme')->get();

        // Only show general channel to staff, not students
        $generalChannel = $user->isStaff()
            ? ChatChannel::getGeneralChannel()
            : null;

        // Only show department channel to staff with a department
        $deptChannels = null;
        if ($user->isStaff() && $user->profile?->department_id) {
            $dept = Department::find($user->profile->department_id);
            if ($dept) {
                $deptChannels = ChatChannel::getDepartmentChannel($dept->id, $dept->name);
            }
        }

        return inertia('Hive/Modules/ChatIndex', [
            'moduleChannels' => $moduleChannels,
            'generalChannel' => $generalChannel,
            'deptChannels' => $deptChannels,
        ]);
    }

    public function showChannel(ChatChannel $channel)
    {
        $this->authorize('view', $channel);

        return inertia('Hive/Modules/Chat', ['channel' => $channel]);
    }

    public function showModule(Module $module)
    {
        $user = auth()->user();

        $isEnrolled = $user->modules()->where('module_id', $module->id)->exists();
        $isInstructor = $module->instructors()->where('user_id', $user->id)->exists();
        $isAdmin = $user->isAdmin();

        if (!$isEnrolled && !$isInstructor && !$isAdmin) {
            abort(403, 'You are not enrolled in this module.');
        }

        $channel = ChatChannel::getModuleChannel($module->id, $module->name);

        return inertia('Hive/Modules/Chat', [
            'module' => $module,
            'channel' => $channel,
        ]);
    }
}
