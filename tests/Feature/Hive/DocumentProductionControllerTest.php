<?php

namespace Tests\Feature\Hive;

use App\Models\Application;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DocumentProductionControllerTest extends HiveTestCase
{
    use RefreshDatabase;

    public function test_document_production_index_requires_auth(): void
    {
        $response = $this->get(route('documents.production.index'));

        $response->assertRedirect();
    }

    public function test_document_production_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->get(route('documents.production.index'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Documents/Production/Index'));
    }

    public function test_generate_acceptance_letter_for_approved_application(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $student = User::factory()->create();
        $student->assignRole('student');

        $application = Application::create([
            'user_id' => $student->id,
            'name' => 'Test Student',
            'email' => 'test@example.com',
            'phone' => '+266 12345678',
            'programme_id' => null,
            'status' => 'approved',
            'admitted_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('documents.production.generate'), [
            'document_type' => 'acceptance_letter',
            'entity_type' => Application::class,
            'entity_id' => $application->id,
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $this->assertDatabaseHas('generated_documents', [
            'document_type' => 'acceptance_letter',
            'entity_type' => Application::class,
            'entity_id' => $application->id,
        ]);
    }

    public function test_audit_for_entity_returns_missing_documents(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('documents.production.audit', [
            'entity_type' => User::class,
            'entity_id' => $student->id,
        ]));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Documents/Production/Audit'));
    }

    public function test_audit_all_returns_results(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->get(route('documents.production.audit', [
            'entity_type' => User::class,
        ]));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Documents/Production/AuditAll'));
    }

    public function test_generate_requires_valid_entity(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->post(route('documents.production.generate'), [
            'document_type' => 'acceptance_letter',
            'entity_type' => Application::class,
            'entity_id' => 99999,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_generate_requires_applicable_document_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = Application::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Test Student',
            'email' => 'test@example.com',
            'phone' => '+266 12345678',
            'programme_id' => null,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('documents.production.generate'), [
            'document_type' => 'acceptance_letter',
            'entity_type' => Application::class,
            'entity_id' => $application->id,
        ]);

        $response->assertSessionHas('error');
    }
}
