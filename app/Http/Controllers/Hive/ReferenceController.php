<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    public function generate(Student $student, Request $request)
    {
        // In a real scenario, you'd get these from the request or student grades.
        $data = [
            'office' => 'Academic Office',
            'ref' => 'HBCI/REF/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'recipient_title' => 'Dr',
            'recipient_name' => 'John Doe',
            'recipient_position' => 'Admissions Officer',
            'recipient_org' => 'University of Example',
            'recipient_city' => 'Cape Town',
            'recipient_last_name' => 'Doe',
            'student' => $student,
            'programme' => $student->enrollments->first()->programme ?? (object) ['name' => 'Culinary Arts'],
            'application_for' => 'the position of Sous Chef',
            'relationship' => 'Programme Coordinator',
            'period_known' => '2 years',
            'start_year' => '2023',
            'completion_status' => 'has successfully completed',
            'grade_summary' => 'commendable results',
            'gpa_record' => '3.8 GPA',
            'academic_achievements' => 'Excelled in pastry and kitchen management modules.',
            'character_traits' => 'hardworking and innovative',
            'character_examples' => 'took initiative in organising a charity dinner',
            'character_details' => 'Showed excellent leadership and teamwork skills.',
            'industry_readiness' => 'Ready to work in a fast-paced professional kitchen.',
            'referee_name' => $this->getSignatory('lecturer'),
            'referee_title' => 'Senior Lecturer',
            'referee_phone' => '+266 XXXX XXXX',
            'referee_email' => 'lecturer@hbci.ac.ls',
        ];

        $pdf = Pdf::loadView('pdf.documents.reference', $data);
        return $pdf->stream('Reference_' . $student->name . '.pdf');
    }

    private function getSignatory($role)
    {
        $user = \App\Models\User::role($role)->first();
        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
