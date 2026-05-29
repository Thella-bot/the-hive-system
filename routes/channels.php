<?php

use App\Models\Module;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Legacy module channel
Broadcast::channel('module.{moduleId}', function ($user, $moduleId) {
    $module = Module::find($moduleId);
    if (!$module) return false;
    return $module->students()->where('user_id', $user->id)->exists()
        || $module->instructors()->where('user_id', $user->id)->exists()
        || $user->hasAnyRole(['super-admin', 'school-admin']);
});

// New chat system channels
Broadcast::channel('chat.module.{moduleId}', function ($user, $moduleId) {
    $module = Module::find($moduleId);
    if (!$module) return false;
    return $module->students()->where('user_id', $user->id)->exists()
        || $module->instructors()->where('user_id', $user->id)->exists()
        || $user->hasAnyRole(['super-admin', 'school-admin']);
});

Broadcast::channel('chat.department.{deptId}', function ($user, $deptId) {
    return $user->profile?->department_id == $deptId
        || $user->hasAnyRole(['super-admin', 'school-admin']);
});

Broadcast::channel('chat.general', function ($user) {
    return $user->hasAnyRole(['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin']);
});

Broadcast::channel('chat.direct.{channelId}', function ($user, $channelId) {
    $channel = \App\Models\ChatChannel::find($channelId);
    if (!$channel) return false;
    return in_array((string) $user->id, $channel->participants ?? []);
});

// Emergency broadcast — all authenticated users
Broadcast::channel('emergency', function ($user) {
    return true; // All authenticated users receive emergency alerts
});
