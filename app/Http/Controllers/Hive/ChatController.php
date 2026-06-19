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
            ? ChatChannel::firstOrCreate(
                ['channel_type' => 'general', 'channel_id' => null],
                ['name' => 'All Staff']
            )
            : null;

        // Only show department channel to staff with a department
        $deptChannels = null;
        if ($user->isStaff() && $user->profile?->department_id) {
            $dept = Department::find($user->profile->department_id);
            if ($dept) {
                $deptChannels = ChatChannel::firstOrCreate(
                    ['channel_type' => 'department', 'channel_id' => $dept->id],
                    ['name' => $dept->name]
                );
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
        $user = auth()->user();

        $canAccess = match ($channel->channel_type) {
            'module' => $user->modules()->where('module_id', $channel->channel_id)->exists()
                        || $user->instructedModules()->where('module_id', $channel->channel_id)->exists()
                        || $user->isAdmin(),
            'department' => $user->profile?->department_id == $channel->channel_id
                           || $user->isAdmin(),
            'general' => $user->isStaff(),
            'direct' => in_array((string) $user->id, $channel->participants ?? []),
            default => false,
        };

        if (!$canAccess) {
            abort(403);
        }

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

        $channel = ChatChannel::firstOrCreate(
            ['channel_type' => 'module', 'channel_id' => $module->id],
            ['name' => $module->name]
        );

        return inertia('Hive/Modules/Chat', [
            'module' => $module,
            'channel' => $channel,
        ]);
    }
}
