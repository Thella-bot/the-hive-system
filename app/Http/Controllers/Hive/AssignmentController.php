<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Module;
use App\Notifications\NewAssignment;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function __construct() {
        $this->authorizeResource(Assignment::class, 'assignment');
        // Note: Authorization for index/list is handled via Gate::authorize('viewAny', ...)

    }

    // Instructor sees own assignments; student sees assignments of enrolled modules
    public function index(Request $request) {
        // Ensure list access is covered by policy (viewAny)
        Gate::authorize('viewAny', Assignment::class);

        $user = $request->user();



        if ($user->hasRole('instructor') || $user->hasRole('admin')) {
            $assignments = Assignment::with('module')
                ->when($user->hasRole('instructor'), fn($q)=>$q->where('instructor_id', $user->id))
                ->latest()->get();
        } else { // student
            $moduleIds = $user->modules()->pluck('id');
            $assignments = Assignment::with('module')
                ->whereIn('module_id', $moduleIds)
                ->latest()
                ->get();
        }

        return Inertia::render('Hive/Assignments/Index', compact('assignments'));
    }

    // Show form to create
    public function create() {
        // Instructors only see their assigned modules (if not admin, filter)
        $modules = auth()->user()->hasRole('admin')
            ? Module::all()
            : auth()->user()->instructedModules;
        return Inertia::render('Hive/Assignments/Create', ['modules' => $modules]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'module_id'=>'required|exists:modules,id',
            'title'=>'required|string',
            'description'=>'nullable|string',
            'due_date'=>'required|date',
            'max_file_size'=>'integer|min:1',
            'allowed_types'=>'nullable|string',
        ]);
        $data['instructor_id'] = auth()->id();
        $assignment = Assignment::create($data);

        $students = $assignment->module->students;
        Notification::send($students, new NewAssignment($assignment));

        return redirect()->route('hive.assignments.index')->with('success','Assignment created');
    }

    public function show(Assignment $assignment) {
        $assignment->load('module', 'submissions.student');
        return Inertia::render('Hive/Assignments/Show', ['assignment' => $assignment]);
    }

}

