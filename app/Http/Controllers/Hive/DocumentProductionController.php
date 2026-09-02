<?php

namespace App\Http\Controllers\Hive;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\GeneratedDocument;
use App\Services\DocumentAuditor;
use App\Services\DocumentFactory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DocumentProductionController extends Controller
{
    public function __construct(protected DocumentFactory $factory, protected DocumentAuditor $auditor) {}

    public function index()
    {
        $types = DocumentType::labels();
        $recent = GeneratedDocument::with('generator')->latest()->limit(20)->get();

        return Inertia::render('Hive/Documents/Production/Index', [
            'documentTypes' => $types,
            'recent' => $recent,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', Rule::in(DocumentType::values())],
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);

        $type = DocumentType::from($validated['document_type']);
        $modelClass = $validated['entity_type'];

        if (!class_exists($modelClass)) {
            return back()->with('error', 'Invalid entity type.');
        }

        $entity = $modelClass::find($validated['entity_id']);
        if (!$entity) {
            return back()->with('error', 'Entity not found.');
        }

        if (!$type->isApplicable($entity)) {
            return back()->with('error', 'This document type is not applicable to the selected entity.');
        }

        try {
            return $this->factory->generate($type, $entity, $request->user()->id);
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to generate document: ' . $e->getMessage());
        }
    }

    public function audit(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'nullable|integer',
        ]);

         $entityType = $validated['entity_type'];
         $entityId = $validated['entity_id'] ?? null;

         if ($entityId) {
             $audit = $this->auditor->auditForEntity($entityType, $entityId);
             $entity = $entityType::find($entityId);

            return Inertia::render('Hive/Documents/Production/Audit', [
                'audit' => $audit,
                'entity' => $entity,
                'entityType' => $entityType,
            ]);
        }

        $results = $this->auditor->auditForAll($entityType, 50);
        $totalMissing = collect($results)->sum('missing_count');
        $totalEntities = count($results);

        return Inertia::render('Hive/Documents/Production/AuditAll', [
            'results' => $results,
            'totalMissing' => $totalMissing,
            'totalEntities' => $totalEntities,
            'entityType' => $entityType,
            'entityTypes' => [
                \App\Models\User::class => 'Students / Staff',
                \App\Models\Application::class => 'Applications',
                \App\Models\Payment::class => 'Payments',
                \App\Models\Invoice::class => 'Invoices',
            ],
        ]);
    }

    public function batchGenerate(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
        ]);

        $entityType = $validated['entity_type'];

        if (!in_array($entityType, [
            \App\Models\User::class,
            \App\Models\Application::class,
            \App\Models\Payment::class,
            \App\Models\Invoice::class,
        ])) {
            return back()->with('error', 'Invalid entity type for batch generation.');
        }

        $result = $this->auditor->batchGenerateMissing($entityType, $request->user()->id);

        return back()->with('success', "Batch generation complete. Generated: {$result['total_generated']}, Failed: {$result['total_failed']}");
    }
}
