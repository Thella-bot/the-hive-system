<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentTask;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return StudentTask::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'due_date', 'completed', 'created_at']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $task = StudentTask::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        return $task;
    }

    public function update(Request $request, StudentTask $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'completed' => 'required|boolean',
        ]);

        $task->update(['completed' => $data['completed']]);

        return $task;
    }

    public function destroy(StudentTask $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $task->delete();

        return response()->json(['success' => true]);
    }
}