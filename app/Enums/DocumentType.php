<?php

namespace App\Enums;

enum DocumentType: string
{
    case AcceptanceLetter = 'acceptance_letter';
    case RejectionLetter = 'rejection_letter';
    case ProofOfEnrolment = 'proof_of_enrolment';
    case ProofOfRegistration = 'proof_of_registration';
    case CertificateOfCompletion = 'certificate_of_completion';
    case Transcript = 'transcript';
    case ReferenceLetter = 'reference_letter';
    case StudentIdCard = 'student_id_card';
    case DisciplinaryWarning = 'disciplinary_warning';
    case DisciplinarySuspension = 'disciplinary_suspension';
    case DisciplinaryExpulsion = 'disciplinary_expulsion';
    case WorkPlacementLetter = 'work_placement_letter';
    case PaymentReceipt = 'payment_receipt';
    case Invoice = 'invoice';
    case StaffAppointmentLetter = 'staff_appointment_letter';
    case StaffWarningLetter = 'staff_warning_letter';
    case Payslip = 'payslip';
    case GeneralCorrespondence = 'general_correspondence';
    case InternalMemo = 'internal_memo';

    public function label(): string
    {
        return match ($this) {
            self::AcceptanceLetter => 'Acceptance Letter',
            self::RejectionLetter => 'Rejection Letter',
            self::ProofOfEnrolment => 'Proof of Enrolment',
            self::ProofOfRegistration => 'Proof of Registration',
            self::CertificateOfCompletion => 'Certificate of Completion',
            self::Transcript => 'Academic Transcript',
            self::ReferenceLetter => 'Reference Letter',
            self::StudentIdCard => 'Student ID Card',
            self::DisciplinaryWarning => 'Disciplinary Warning',
            self::DisciplinarySuspension => 'Disciplinary Suspension',
            self::DisciplinaryExpulsion => 'Disciplinary Expulsion',
            self::WorkPlacementLetter => 'Work Placement Letter',
            self::PaymentReceipt => 'Payment Receipt',
            self::Invoice => 'Invoice',
            self::StaffAppointmentLetter => 'Staff Appointment Letter',
            self::StaffWarningLetter => 'Staff Warning Letter',
            self::Payslip => 'Payslip',
            self::GeneralCorrespondence => 'General Correspondence',
            self::InternalMemo => 'Internal Memo',
        };
    }

    public function template(): string
    {
        return match ($this) {
            self::AcceptanceLetter => 'pdf.documents.acceptance',
            self::RejectionLetter => 'pdf.documents.rejection',
            self::ProofOfEnrolment => 'pdf.documents.proof_of_enrolment',
            self::ProofOfRegistration => 'pdf.documents.proof_of_enrolment',
            self::CertificateOfCompletion => 'pdf.documents.certificate',
            self::Transcript => 'pdf.transcript',
            self::ReferenceLetter => 'pdf.documents.reference',
            self::StudentIdCard => 'pdf.student-id-card',
            self::DisciplinaryWarning => 'pdf.documents.student_warning',
            self::DisciplinarySuspension => 'pdf.documents.student_suspension',
            self::DisciplinaryExpulsion => 'pdf.documents.student_expulsion',
            self::WorkPlacementLetter => 'pdf.documents.work_placement',
            self::PaymentReceipt => 'pdf.documents.payment_receipt',
            self::Invoice => 'pdf.documents.invoice',
            self::StaffAppointmentLetter => 'pdf.documents.staff_appointment',
            self::StaffWarningLetter => 'pdf.documents.staff_warning',
            self::Payslip => 'pdf.payslip',
            self::GeneralCorrespondence => 'pdf.documents.general_correspondence',
            self::InternalMemo => 'pdf.documents.internal_memo',
        };
    }

    public function entityType(): string
    {
        return match ($this) {
            self::AcceptanceLetter,
            self::RejectionLetter => \App\Models\Application::class,

            self::ProofOfEnrolment,
            self::ProofOfRegistration,
            self::CertificateOfCompletion,
            self::Transcript,
            self::ReferenceLetter,
            self::StudentIdCard,
            self::DisciplinaryWarning,
            self::DisciplinarySuspension,
            self::DisciplinaryExpulsion,
            self::WorkPlacementLetter => \App\Models\User::class,

            self::PaymentReceipt => \App\Models\Payment::class,
            self::Invoice => \App\Models\Invoice::class,
            self::StaffAppointmentLetter,
            self::StaffWarningLetter => \App\Models\User::class,

            self::Payslip => \App\Models\Payslip::class,

            self::GeneralCorrespondence,
            self::InternalMemo => \App\Models\Document::class,
        };
    }

    public function isApplicable(object $entity): bool
    {
        return match ($this) {
            self::AcceptanceLetter => $entity instanceof \App\Models\Application && $entity->status === 'approved',
            self::RejectionLetter => $entity instanceof \App\Models\Application && $entity->status === 'rejected',
            self::ProofOfEnrolment => $entity instanceof \App\Models\User && $entity->enrollments()->exists(),
            self::ProofOfRegistration => $entity instanceof \App\Models\User && $entity->applications()->where('registration_status', 'completed')->exists(),
            self::CertificateOfCompletion => $entity instanceof \App\Models\User && $entity->profile && $entity->profile->status === 'graduated',
            self::Transcript => $entity instanceof \App\Models\User && $entity->submissions()->exists(),
            self::ReferenceLetter => $entity instanceof \App\Models\User && $entity->profile && $entity->profile->status === 'graduated',
            self::StudentIdCard => $entity instanceof \App\Models\User && $entity->hasRole('student') && $entity->student_number,
            self::DisciplinaryWarning => $entity instanceof \App\Models\User && $entity->disciplinaryActions()->where('type', 'warning')->exists(),
            self::DisciplinarySuspension => $entity instanceof \App\Models\User && $entity->disciplinaryActions()->where('type', 'suspension')->exists(),
            self::DisciplinaryExpulsion => $entity instanceof \App\Models\User && $entity->disciplinaryActions()->where('type', 'expulsion')->exists(),
            self::WorkPlacementLetter => $entity instanceof \App\Models\User && $entity->placements()->where('status', 'active')->exists(),
            self::PaymentReceipt => $entity instanceof \App\Models\Payment && $entity->status === 'completed',
            self::Invoice => $entity instanceof \App\Models\Invoice && $entity->status !== 'cancelled',
            self::StaffAppointmentLetter => $entity instanceof \App\Models\User && $entity->isStaff() && $entity->profile && $entity->profile->hire_date,
            self::StaffWarningLetter => $entity instanceof \App\Models\User && $entity->isStaff() && $entity->disciplinaryActions()->where('type', 'warning')->exists(),
            self::Payslip => $entity instanceof \App\Models\Payslip,
            self::GeneralCorrespondence,
            self::InternalMemo => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return array_combine(self::values(), array_map(fn (self $case) => $case->label(), self::cases()));
    }
}
