<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    // Index – show list of programmes with modules
    public function index() {
        $programmes = Programme::with('modules')->get();
        return Inertia::render('Modules/Index', compact('programmes'));
    }

    // Store a programme
    public function storeProgramme(Request $request) {
        $data = $request->validate(['name'=>'required', 'description'=>'nullable', 'duration_years'=>'required|integer']);
        Programme::create($data);
        return back();
    }

    // Store a module
    public function storeModule(Request $request) {
        $data = $request->validate(['programme_id'=>'required|exists:programmes,id','name'=>'required','code'=>'required|unique:modules','description'=>'nullable','credits'=>'integer']);
        Module::create($data);
        return back();
    }
}