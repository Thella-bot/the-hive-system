<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::with('module')->latest()->get();
        return Inertia::render('Hive/Tests/Index', [
            'tests' => $tests,
        ]);
    }
}