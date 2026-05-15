<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        $users = User::search($query)->get();

        return Inertia::render('Search/Index', [
            'query' => $query,
            'users' => $users,
        ]);
    }
}
