<?php

namespace App\Http\Livewire;

use App\Services\Dashboard\StudentDashboardData;
use Livewire\Component;

class StudentDashboard extends Component
{
    public function render()
    {
        $data = (new StudentDashboardData())->getData(auth()->user());

        return view('livewire.student-dashboard', $data);
    }
}