<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;

use App\Actions\Hive\CreateNewStudent;
use App\Actions\Hive\UpdateStudent;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = User::role('student')->get();
        return Inertia::render('Hive/Students/Index', [
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Hive/Students/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateNewStudent $creator)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $creator->create($request->all());

        return redirect()->route('hive.students.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $student->load('programme'); // Eager load the student's programme
        return Inertia::render('Hive/Students/Edit', [
            'managedStudent' => $student,
            'programmes' => Programme::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student, UpdateStudent $updater)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $updater->update($student, $request->all());

        return redirect()->route('hive.students.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $student->delete();

        return redirect()->route('hive.students.index');
    }
}