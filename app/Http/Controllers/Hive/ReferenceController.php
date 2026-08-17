<?php
declare(strict_types=1);

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\GeneratesDocumentPdfs;
use App\Models\User;
use App\Services\SignatoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    use GeneratesDocumentPdfs;

    public function __construct(protected SignatoryService $signatory)
    {
        $this->authorizeResource(User::class, 'student');
    }

    public function generate(User $student, Request $request)
    {
        $student->load('enrollments.module.programme');

        $data = [
            'office' => config('institution.academic_office'),
            'ref' => config('institution.abbreviation') . '/REF/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'recipient_title' => 'Dr',
            'recipient_name' => 'John Doe',
            'recipient_position' => 'Admissions Officer',
            'recipient_org' => 'University of Example',
            'recipient_city' => 'Cape Town',
            'recipient_last_name' => 'Doe',
            'student' => $student,
            'programme' => $student->enrollments->first()->module->programme ?? (object) ['name' => 'Culinary Arts'],
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
            'referee_name' => $this->signatory->get('lecturer'),
            'referee_title' => 'Senior Lecturer',
            'referee_phone' => '+266 XXXX XXXX',
            'referee_email' => 'lecturer@hbci.ac.ls',
        ];

        return $this->generatePdf('pdf.documents.reference', $data, 'Reference_' . $student->name . '.pdf', $student->id);
    }
}
