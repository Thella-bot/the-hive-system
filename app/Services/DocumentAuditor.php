<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\GeneratedDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DocumentAuditor
{
    public function __construct(protected DocumentFactory $factory) {}

    public function auditForEntity(string $entityType, int $entityId): array
    {
        $modelClass = $entityType;
        if (!class_exists($modelClass)) {
            return [];
        }

        $entity = $modelClass::find($entityId);
        if (!$entity) {
            return [];
        }

        $required = $this->getRequiredDocumentTypes($entity);
        $existing = GeneratedDocument::forEntity($entityType, $entityId)
            ->whereIn('document_type', array_column($required, 'type'))
            ->get(['document_type', 'generated_at'])
            ->keyBy('document_type');

        $results = [];
        foreach ($required as $doc) {
            $type = DocumentType::from($doc['type']);
            $generated = $existing->get($doc['type']);

            $results[] = [
                'type' => $doc['type'],
                'label' => $type->label(),
                'is_applicable' => true,
                'is_generated' => (bool) $generated,
                'generated_at' => $generated?->generated_at,
                'template' => $type->template(),
            ];
        }

        return $results;
    }

    public function auditForAll(string $entityType, ?int $limit = null): array
    {
        $modelClass = $entityType;
        if (!class_exists($modelClass)) {
            return [];
        }

        $query = $modelClass::query();
        if ($limit) {
            $query->limit($limit);
        }

        $entities = $query->get();
        $results = [];

        foreach ($entities as $entity) {
            $audit = $this->auditForEntity($entityType, $entity->id);
            $missing = array_values(array_filter($audit, fn ($a) => !$a['is_generated']));

            if (!empty($missing)) {
                $results[] = [
                    'entity_id' => $entity->id,
                    'entity_label' => $this->entityLabel($entity),
                    'entity_type' => $entityType,
                    'missing_documents' => $missing,
                    'missing_count' => count($missing),
                ];
            }
        }

        return $results;
    }

    public function getMissingDocumentTypes(object $entity): array
    {
        $required = $this->getRequiredDocumentTypes($entity);
        $entityType = $entity::class;

        $existing = GeneratedDocument::forEntity($entityType, $entity->id)
            ->whereIn('document_type', array_column($required, 'type'))
            ->pluck('document_type')
            ->toArray();

        return array_values(array_filter(array_column($required, 'type'), fn ($t) => !in_array($t, $existing)));
    }

    public function batchGenerateMissing(string $entityType, int $userId = null): array
    {
        $results = $this->auditForAll($entityType);
        $generated = [];
        $failed = [];

        foreach ($results as $result) {
            foreach ($result['missing_documents'] as $doc) {
                $entity = $entityType::find($result['entity_id']);
                if (!$entity || !DocumentType::from($doc['type'])->isApplicable($entity)) {
                    continue;
                }

                try {
                    $this->factory->generate(DocumentType::from($doc['type']), $entity, $userId);
                    $generated[] = [
                        'entity_id' => $result['entity_id'],
                        'entity_label' => $result['entity_label'],
                        'document_type' => $doc['type'],
                        'document_label' => $doc['label'],
                        'status' => 'generated',
                    ];
                } catch (\Throwable $e) {
                    $failed[] = [
                        'entity_id' => $result['entity_id'],
                        'entity_label' => $result['entity_label'],
                        'document_type' => $doc['type'],
                        'document_label' => $doc['label'],
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        return [
            'generated' => $generated,
            'failed' => $failed,
            'total_generated' => count($generated),
            'total_failed' => count($failed),
        ];
    }

    private function getRequiredDocumentTypes(object $entity): array
    {
        $type = $entity::class;

        if ($type === \App\Models\Application::class) {
            return $this->applicationDocuments($entity);
        }

        if ($type === \App\Models\User::class) {
            return $this->userDocuments($entity);
        }

        if ($type === \App\Models\Payment::class) {
            return $this->paymentDocuments($entity);
        }

        if ($type === \App\Models\Invoice::class) {
            return $this->invoiceDocuments($entity);
        }

        return [];
    }

    private function applicationDocuments(\App\Models\Application $app): array
    {
        $required = [];

        if ($app->status === 'approved') {
            $required[] = ['type' => DocumentType::AcceptanceLetter->value, 'label' => DocumentType::AcceptanceLetter->label()];
        }

        if ($app->status === 'rejected') {
            $required[] = ['type' => DocumentType::RejectionLetter->value, 'label' => DocumentType::RejectionLetter->label()];
        }

        return $required;
    }

    private function userDocuments(User $user): array
    {
        $required = [];

        if ($user->enrollments()->exists()) {
            $required[] = ['type' => DocumentType::ProofOfEnrolment->value, 'label' => DocumentType::ProofOfEnrolment->label()];
        }

        if ($user->applications()->where('registration_status', 'completed')->exists()) {
            $required[] = ['type' => DocumentType::ProofOfRegistration->value, 'label' => DocumentType::ProofOfRegistration->label()];
        }

        if ($user->profile && $user->profile->status === 'graduated') {
            $required[] = ['type' => DocumentType::CertificateOfCompletion->value, 'label' => DocumentType::CertificateOfCompletion->label()];
            $required[] = ['type' => DocumentType::ReferenceLetter->value, 'label' => DocumentType::ReferenceLetter->label()];
        }

        if ($user->submissions()->exists()) {
            $required[] = ['type' => DocumentType::Transcript->value, 'label' => DocumentType::Transcript->label()];
        }

        if ($user->hasRole('student') && $user->student_number) {
            $required[] = ['type' => DocumentType::StudentIdCard->value, 'label' => DocumentType::StudentIdCard->label()];
        }

        if ($user->disciplinaryActions()->where('type', 'warning')->exists()) {
            $label = $user->isStudent() ? DocumentType::DisciplinaryWarning->label() : DocumentType::StaffWarningLetter->label();
            $type = $user->isStudent() ? DocumentType::DisciplinaryWarning->value : DocumentType::StaffWarningLetter->value;
            $required[] = ['type' => $type, 'label' => $label];
        }

        if ($user->disciplinaryActions()->where('type', 'suspension')->exists() && $user->isStudent()) {
            $required[] = ['type' => DocumentType::DisciplinarySuspension->value, 'label' => DocumentType::DisciplinarySuspension->label()];
        }

        if ($user->disciplinaryActions()->where('type', 'expulsion')->exists() && $user->isStudent()) {
            $required[] = ['type' => DocumentType::DisciplinaryExpulsion->value, 'label' => DocumentType::DisciplinaryExpulsion->label()];
        }

        if ($user->placements()->where('status', 'active')->exists()) {
            $required[] = ['type' => DocumentType::WorkPlacementLetter->value, 'label' => DocumentType::WorkPlacementLetter->label()];
        }

        if ($user->isStaff() && $user->profile && $user->profile->hire_date) {
            $required[] = ['type' => DocumentType::StaffAppointmentLetter->value, 'label' => DocumentType::StaffAppointmentLetter->label()];
        }

        return $required;
    }

    private function paymentDocuments(Payment $payment): array
    {
        $required = [];

        if ($payment->status === 'completed') {
            $required[] = ['type' => DocumentType::PaymentReceipt->value, 'label' => DocumentType::PaymentReceipt->label()];
        }

        return $required;
    }

    private function invoiceDocuments(\App\Models\Invoice $invoice): array
    {
        $required = [];

        if ($invoice->status !== 'cancelled') {
            $required[] = ['type' => DocumentType::Invoice->value, 'label' => DocumentType::Invoice->label()];
        }

        return $required;
    }

    private function entityLabel(object $entity): string
    {
        return match (true) {
            $entity instanceof User => $entity->name . ' (' . ($entity->student_number ?? $entity->email) . ')',
            $entity instanceof \App\Models\Application => 'Application #' . $entity->id . ' - ' . ($entity->user?->name ?? $entity->name),
            $entity instanceof Payment => 'Payment ' . ($entity->receipt_number ?? '#' . $entity->id),
            $entity instanceof \App\Models\Invoice => 'Invoice ' . $entity->invoice_number,
            default => get_class($entity) . ' #' . ($entity->id ?? 'N/A'),
        };
    }
}
