<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\GeneratedDocument;
use App\Models\Payment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DocumentFactory
{
    public function __construct(protected SignatoryService $signatory, protected StudentIdCardService $idCard) {}

    public function generate(DocumentType $type, object $entity, ?int $userId = null): Response
    {
        $data = $this->buildData($type, $entity);
        $view = $type->template();
        $fileName = $this->fileName($type, $entity);
        $disk = 'local';
        $cacheKey = "document.pdf.{$fileName}";

        if (Cache::has($cacheKey)) {
            $path = Cache::get($cacheKey);
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->download($path);
            }
            Cache::forget($cacheKey);
        }

        $pdf = Pdf::loadView($view, $data);

        if ($type === DocumentType::StudentIdCard) {
            $this->idCard->configurePdf($pdf);
        } elseif ($type === DocumentType::CertificateOfCompletion) {
            $pdf->setPaper('A4', 'landscape');
        }

        $output = $pdf->output();
        $path = 'generated-documents/' . $fileName;
        Storage::disk($disk)->put($path, $output);
        Cache::put($cacheKey, $path, now()->addHours(24));

        GeneratedDocument::create([
            'document_type' => $type->value,
            'entity_type' => $type->entityType(),
            'entity_id' => $entity instanceof \Illuminate\Database\Eloquent\Model ? $entity->id : 0,
            'generated_by' => $userId ?? auth()->id(),
            'file_path' => $path,
            'generated_at' => now(),
        ]);

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    public function fileName(DocumentType $type, object $entity): string
    {
        $base = match ($type) {
            DocumentType::AcceptanceLetter => 'Acceptance',
            DocumentType::RejectionLetter => 'Rejection',
            DocumentType::ProofOfEnrolment => 'Proof_of_Enrolment',
            DocumentType::ProofOfRegistration => 'Proof_of_Registration',
            DocumentType::CertificateOfCompletion => 'Certificate',
            DocumentType::Transcript => 'Transcript',
            DocumentType::ReferenceLetter => 'Reference',
            DocumentType::StudentIdCard => 'Student_ID',
            DocumentType::DisciplinaryWarning => 'Warning',
            DocumentType::DisciplinarySuspension => 'Suspension',
            DocumentType::DisciplinaryExpulsion => 'Expulsion',
            DocumentType::WorkPlacementLetter => 'Placement',
            DocumentType::PaymentReceipt => 'Receipt',
            DocumentType::Invoice => 'Invoice',
            DocumentType::StaffAppointmentLetter => 'Appointment',
            DocumentType::StaffWarningLetter => 'Staff_Warning',
            DocumentType::Payslip => 'Payslip',
            DocumentType::GeneralCorrespondence => 'Correspondence',
            DocumentType::InternalMemo => 'Memo',
        };

        $subject = match (true) {
            $entity instanceof User => $entity->name,
            $entity instanceof Payment => $entity->receipt_number ?? 'Payment',
            $entity instanceof \App\Models\Invoice => $entity->invoice_number ?? 'Invoice',
            default => 'Document',
        };

        return $base . '_' . $subject . '_' . date('Ymd') . '.pdf';
    }

    public function buildData(DocumentType $type, object $entity): array
    {
        $abbr = config('institution.abbreviation', 'HBCI');

        return match ($type) {
            DocumentType::AcceptanceLetter => $this->acceptanceData($entity),
            DocumentType::RejectionLetter => $this->rejectionData($entity),
            DocumentType::ProofOfEnrolment => $this->proofOfEnrolmentData($entity),
            DocumentType::ProofOfRegistration => $this->proofOfRegistrationData($entity),
            DocumentType::CertificateOfCompletion => $this->certificateData($entity),
            DocumentType::Transcript => $this->transcriptData($entity),
            DocumentType::ReferenceLetter => $this->referenceData($entity),
            DocumentType::StudentIdCard => $this->studentIdCardData($entity),
            DocumentType::DisciplinaryWarning => $this->disciplinaryWarningData($entity),
            DocumentType::DisciplinarySuspension => $this->disciplinarySuspensionData($entity),
            DocumentType::DisciplinaryExpulsion => $this->disciplinaryExpulsionData($entity),
            DocumentType::WorkPlacementLetter => $this->placementData($entity),
            DocumentType::PaymentReceipt => $this->paymentReceiptData($entity),
            DocumentType::Invoice => $this->invoiceData($entity),
            DocumentType::StaffAppointmentLetter => $this->staffAppointmentData($entity),
            DocumentType::StaffWarningLetter => $this->staffWarningData($entity),
            DocumentType::Payslip => $this->payslipData($entity),
            DocumentType::GeneralCorrespondence,
            DocumentType::InternalMemo => [],
        };
    }

    private function acceptanceData(object $entity): array
    {
        $app = $entity;
        $user = $app->user;

        return [
            'office' => config('institution.registrar_office') . ' --- Admissions',
            'ref' => config('institution.abbreviation') . '/ADM/' . date('Y') . '/' . $app->id,
            'date' => now(),
            'student' => $user,
            'programme' => $app->programme,
            'enrollment' => $app,
            'intake_date' => $app->admitted_at ?? now(),
            'deadline' => now()->addDays(14),
            'registration_fee' => config('institution.default_registration_fee', 500.00),
            'registrar_name' => $this->signatory->get('registrar'),
        ];
    }

    private function rejectionData(object $entity): array
    {
        $app = $entity;
        $user = $app->user;

        return [
            'office' => config('institution.registrar_office') . ' --- Admissions',
            'ref' => config('institution.abbreviation') . '/ADM/' . date('Y') . '/' . $app->id,
            'date' => now(),
            'student' => $user,
            'programme' => $app->programme,
            'intake_month' => $app->admitted_at ? $app->admitted_at->format('F Y') : now()->format('F Y'),
            'registrar_name' => $this->signatory->get('registrar'),
            'phone' => config('institution.phone', '+266 XXXX XXXX'),
        ];
    }

    private function proofOfEnrolmentData(object $entity): array
    {
        $student = $entity;
        $student->load(['profile', 'enrollments.module.programme']);
        $enrollment = $student->enrollments->first();
        $programme = $enrollment?->module?->programme ?? $student->programme;
        $profile = $student->profile;

        $fullName = $student->name;
        if ($profile && $profile->first_name && $profile->last_name) {
            $fullName = $profile->first_name . ' ' . $profile->last_name;
        }

        $dob = $profile?->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth) : null;

        return [
            'office' => config('institution.registrar_office'),
            'ref' => config('institution.abbreviation') . '/REG/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'student' => (object) [
                'full_name' => $fullName,
                'student_number' => $student->student_number ?? ($profile?->student_number ?? 'N/A'),
                'dob' => $dob,
                'id_number' => $student->national_id_number ?? null,
            ],
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'year_of_study' => 1,
            'total_years' => $programme->duration ?? 3,
            'academic_year' => date('Y') . '/' . (date('Y') + 1),
            'mode_of_study' => 'Full-Time',
            'status' => 'ACTIVE',
            'enrolment_date' => $profile->enrollment_date ?? $student->created_at,
            'expected_completion' => now()->addYears($programme->duration ?? 3),
            'registrar_name' => $this->signatory->get('registrar'),
            'modules' => $student->modules()->get(),
        ];
    }

    private function proofOfRegistrationData(object $entity): array
    {
        $student = $entity;
        $student->load(['profile', 'enrollments.module.programme', 'applications']);
        $application = $student->applications()->where('registration_status', 'completed')->latest()->first();
        $programme = $application?->programme ?? $student->programme;
        $profile = $student->profile;

        $fullName = $student->name;
        if ($profile && $profile->first_name && $profile->last_name) {
            $fullName = $profile->first_name . ' ' . $profile->last_name;
        }

        $dob = $profile?->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth) : null;

        return [
            'office' => config('institution.registrar_office'),
            'ref' => config('institution.abbreviation') . '/REG/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'student' => (object) [
                'full_name' => $fullName,
                'student_number' => $student->student_number ?? ($profile?->student_number ?? 'N/A'),
                'dob' => $dob,
                'id_number' => $student->national_id_number ?? null,
            ],
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'year_of_study' => 1,
            'total_years' => $programme->duration ?? 3,
            'academic_year' => date('Y') . '/' . (date('Y') + 1),
            'mode_of_study' => 'Full-Time',
            'status' => 'REGISTERED',
            'enrolment_date' => $profile->enrollment_date ?? $student->created_at,
            'expected_completion' => now()->addYears($programme->duration ?? 3),
            'registration_date' => $application?->registered_at ?? now(),
            'registrar_name' => $this->signatory->get('registrar'),
            'modules' => $student->modules()->get(),
        ];
    }

    private function certificateData(object $entity): array
    {
        $student = $entity;
        $student->load(['profile', 'enrollments.module.programme']);
        $enrollment = $student->enrollments->first();
        $programme = $enrollment?->module?->programme ?? $student->programme;

        return [
            'office' => config('institution.registrar_office'),
            'ref' => config('institution.abbreviation') . '/REG/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'student' => $student,
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'award' => 'Merit',
            'director_name' => $this->signatory->get('super-admin'),
            'registrar_name' => $this->signatory->get('registrar'),
            'issue_date' => now(),
            'certificate_number' => config('institution.abbreviation') . '-CERT-' . date('Y') . '-' . $student->id,
        ];
    }

    private function transcriptData(object $entity): array
    {
        $student = $entity;
        $student->load(['submissions.gradable.module.programme', 'enrollments.module.programme']);

        return [
            'student' => $student,
            'programme' => $student->programme,
            'modules' => $student->modules()->get(),
            'submissions' => $student->submissions,
            'grades' => $student->submissions->map(fn ($s) => [
                'module' => $s->gradable->module->name ?? 'N/A',
                'module_code' => $s->gradable->module->code ?? 'N/A',
                'grade' => $s->grade ?? 'N/A',
                'max_marks' => $s->gradable->max_marks ?? 100,
                'submitted_at' => $s->submitted_at,
            ]),
            'registrar_name' => $this->signatory->get('registrar'),
        ];
    }

    private function referenceData(object $entity): array
    {
        $student = $entity;
        $programme = $student->programme;

        return [
            'office' => config('institution.academic_office'),
            'ref' => config('institution.abbreviation') . '/REF/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'recipient_title' => 'Dr',
            'recipient_name' => 'John Doe',
            'recipient_position' => 'Admissions Officer',
            'recipient_org' => 'University of Example',
            'recipient_city' => 'Maseru',
            'recipient_last_name' => 'Doe',
            'student' => $student,
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts'],
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
            'referee_name' => $this->signatory->get('super-admin'),
            'referee_title' => 'Senior Lecturer',
            'referee_phone' => '+266 XXXX XXXX',
            'referee_email' => 'lecturer@hbci.ac.ls',
        ];
    }

    private function studentIdCardData(object $entity): array
    {
        if (! $entity instanceof User) {
            throw new \InvalidArgumentException('Student ID card requires a User instance.');
        }

        return $this->idCard->templateData($entity);
    }

    private function disciplinaryWarningData(object $entity): array
    {
        $user = $entity;
        $isStudent = $user->hasRole('student');
        $disciplinary = $user->disciplinaryActions()->where('type', 'warning')->latest()->first();

        return [
            'office' => $isStudent ? config('institution.student_affairs_office') . ' --- Disciplinary' : config('institution.hr_office'),
            'ref' => config('institution.abbreviation') . '/DSC/' . date('Y') . '/' . ($disciplinary?->id ?? $user->id),
            'date' => now(),
            'student' => $user,
            'programme' => $user->enrollments->first()->programme ?? (object) ['name' => 'N/A'],
            'warning_type' => $disciplinary?->warning_level ?? 'First',
            'offence' => $disciplinary?->offence ?? 'Policy Breach',
            'hearing_date' => $disciplinary?->hearing_date ?? now(),
            'incident_description' => $disciplinary?->incident_description ?? 'Description of incident...',
            'rule_violated' => $disciplinary?->policy_violated ?? 'Student Code of Conduct',
            'advisor_name' => $disciplinary?->advisor_name ?? 'Dean of Students',
            'meeting_deadline' => now()->addDays(3),
            'dean_name' => $isStudent ? $this->signatory->get('dean') : $this->signatory->get('hr-manager'),
            'staff' => $user,
            'policy_violated' => $disciplinary?->policy_violated ?? config('institution.abbreviation') . ' Employment Policy',
            'hr_rep' => $disciplinary?->hr_rep ?? 'HR Representative',
            'expiry_date' => $disciplinary?->expiry_date ?? now()->addMonths(6),
            'corrective_actions' => $disciplinary?->corrective_actions ?? ['Attend training session'],
            'hr_manager_name' => $this->signatory->get('hr-manager'),
        ];
    }

    private function disciplinarySuspensionData(object $entity): array
    {
        $user = $entity;
        $disciplinary = $user->disciplinaryActions()->where('type', 'suspension')->latest()->first();

        return [
            'office' => config('institution.student_affairs_office') . ' --- Disciplinary',
            'ref' => config('institution.abbreviation') . '/DSC/' . date('Y') . '/' . ($disciplinary?->id ?? $user->id),
            'date' => now(),
            'student' => $user,
            'programme' => $user->enrollments->first()->programme ?? (object) ['name' => 'N/A'],
            'offence' => $disciplinary?->offence ?? 'Policy Breach',
            'hearing_date' => $disciplinary?->hearing_date ?? now(),
            'suspension_type' => 'Punitive',
            'effective_date' => $disciplinary?->effective_date ?? now(),
            'duration' => $disciplinary?->duration ?? 'Until Further Notice',
            'return_date' => $disciplinary?->return_date,
            'campus_access' => $disciplinary?->campus_access ?? 'Prohibited',
            'surrender_date' => $disciplinary?->surrender_date ?? now()->addDay(),
            'review_date' => $disciplinary?->review_date ?? now()->addWeeks(2),
            'director_name' => $this->signatory->get('director'),
        ];
    }

    private function disciplinaryExpulsionData(object $entity): array
    {
        $user = $entity;
        $disciplinary = $user->disciplinaryActions()->where('type', 'expulsion')->latest()->first();

        return [
            'office' => config('institution.student_affairs_office') . ' --- Disciplinary',
            'ref' => config('institution.abbreviation') . '/DSC/' . date('Y') . '/' . ($disciplinary?->id ?? $user->id),
            'date' => now(),
            'student' => $user,
            'programme' => $user->enrollments->first()->programme ?? (object) ['name' => 'N/A'],
            'hearing_date' => $disciplinary?->hearing_date ?? now(),
            'effective_date' => $disciplinary?->effective_date ?? now(),
            'grounds' => $disciplinary?->grounds ?? ['Violation of Code of Conduct'],
            'compliance_deadline' => now()->addDays(5),
            'director_name' => $this->signatory->get('director'),
        ];
    }

    private function placementData(object $entity): array
    {
        $placement = $entity;
        $student = $placement->student ?? $entity;

        return [
            'office' => config('institution.academic_office') . ' --- Work Placement',
            'ref' => config('institution.abbreviation') . '/WP/' . date('Y') . '/' . ($placement->id ?? $student->id),
            'date' => now(),
            'recipient_title' => 'Mr/Ms',
            'recipient_name' => $placement->organisation_name ?? 'Organisation',
            'recipient_position' => 'HR Manager',
            'recipient_org' => $placement->organisation_name ?? 'Organisation',
            'recipient_city' => 'Maseru',
            'recipient_last_name' => 'Manager',
            'student' => $student,
            'programme' => $placement->programme ?? $student->programme,
            'year_of_study' => 1,
            'total_years' => 3,
            'placement_start' => $placement->start_date ?? now(),
            'placement_end' => $placement->end_date ?? now()->addMonths(3),
            'duration' => $placement->duration ?? '3 Months',
            'proposed_start' => $placement->start_date ?? now(),
            'placement_type' => $placement->type ?? 'Compulsory',
            'industry_sector' => 'culinary / hospitality',
            'coordinator_name' => $this->signatory->get('academic-director'),
        ];
    }

    private function paymentReceiptData(object $entity): array
    {
        $payment = $entity;

        return [
            'office' => config('institution.finance_office'),
            'ref' => config('institution.abbreviation') . '/FIN/' . date('Y') . '/' . $payment->id,
            'date' => $payment->created_at,
            'receipt_number' => $payment->receipt_number,
            'payer_name' => $payment->payer_name,
            'student_number' => $payment->student_number ?? 'N/A',
            'programme_name' => $payment->programme_name ?? 'N/A',
            'items' => $payment->items ?? [
                [
                    'description' => 'Payment Received',
                    'qty' => 1,
                    'unit_price' => $payment->amount,
                    'total' => $payment->amount,
                ]
            ],
            'sub_total' => $payment->sub_total ?? $payment->amount,
            'discount' => $payment->discount ?? 0,
            'total_paid' => $payment->amount,
            'payment_method' => $payment->method,
            'bank_ref' => $payment->bank_ref ?? '',
            'academic_year' => $payment->academic_year ?? date('Y'),
            'cohort' => $payment->cohort ?? '',
            'amount_words' => \App\Services\NumberToWords::convert($payment->amount),
            'cashier_name' => $this->signatory->get('finance'),
        ];
    }

    private function invoiceData(object $entity): array
    {
        $invoice = $entity;

        return [
            'office' => config('institution.finance_office'),
            'ref' => config('institution.abbreviation') . '/INV/' . date('Y') . '/' . $invoice->id,
            'date' => $invoice->issued_at ?? now(),
            'invoice_number' => $invoice->invoice_number,
            'student' => $invoice->user,
            'programme' => $invoice->programme,
            'items' => [
                [
                    'description' => $invoice->description ?? 'Tuition Fee',
                    'qty' => 1,
                    'unit_price' => $invoice->amount,
                    'total' => $invoice->amount,
                ]
            ],
            'sub_total' => $invoice->amount,
            'total' => $invoice->amount,
            'due_date' => $invoice->due_date,
            'status' => $invoice->status,
            'finance_officer' => $this->signatory->get('finance'),
        ];
    }

    private function staffAppointmentData(object $entity): array
    {
        $staff = $entity;
        $staff->load(['profile.department', 'roles']);

        return [
            'office' => config('institution.hr_office'),
            'ref' => config('institution.abbreviation') . '/HR/' . date('Y') . '/' . $staff->id,
            'date' => now(),
            'staff' => $staff,
            'position' => $staff->profile->designation ?? 'Chef Instructor',
            'department' => $staff->profile->department->name ?? 'Culinary Arts',
            'contract_type' => 'Permanent',
            'contract_start' => $staff->profile->hire_date ?? now(),
            'contract_end' => null,
            'commencement_date' => $staff->profile->hire_date ?? now(),
            'reporting_to' => 'Head of Department',
            'salary' => config('institution.default_salary', 15000.00),
            'probation_period' => config('institution.default_probation', '3 Months'),
            'pay_day' => config('institution.default_pay_day', '25th'),
            'notice_period' => config('institution.default_notice_period', '1 Month'),
            'acceptance_deadline' => now()->addDays(7),
            'director_name' => $this->signatory->get('super-admin'),
        ];
    }

    private function staffWarningData(object $entity): array
    {
        $staff = $entity;
        $staff->load(['profile.department']);
        $disciplinary = $staff->disciplinaryActions()->where('type', 'warning')->latest()->first();

        return [
            'office' => config('institution.hr_office'),
            'ref' => config('institution.abbreviation') . '/HR/DSC/' . date('Y') . '/' . ($disciplinary?->id ?? $staff->id),
            'date' => now(),
            'staff' => $staff,
            'warning_type' => $disciplinary?->warning_level ?? 'First',
            'offence' => $disciplinary?->offence ?? 'Policy Breach',
            'hearing_date' => $disciplinary?->hearing_date ?? now(),
            'hr_rep' => $disciplinary?->hr_rep ?? 'HR Representative',
            'incident_description' => $disciplinary?->incident_description ?? 'Description of incident...',
            'policy_violated' => $disciplinary?->policy_violated ?? 'Employment Policy Section X',
            'expiry_date' => $disciplinary?->expiry_date ?? now()->addMonths(6),
            'corrective_actions' => $disciplinary?->corrective_actions ?? ['Attend training session'],
            'hr_manager_name' => $this->signatory->get('hr-manager'),
        ];
    }

    private function payslipData(object $entity): array
    {
        $payslip = $entity;

        return [
            'payslip' => $payslip,
            'user' => $payslip->user,
            'amount_words' => \App\Services\NumberToWords::convert($payslip->net_salary),
            'generated_at' => now(),
        ];
    }
}
