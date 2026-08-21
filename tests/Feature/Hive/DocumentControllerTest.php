<?php

namespace Tests\Feature\Hive;

use App\Models\Document;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Hive\Traits\CreatesAssessmentFixture;

class DocumentControllerTest extends HiveTestCase
{
    use CreatesAssessmentFixture;

    public function test_student_sees_enrolled_modules_in_module_select(): void
    {
        $fixture = $this->createAssessmentFixture();

        $this->actingAs($fixture['student1']);

        $response = $this->get(route('hive.documents.module-select'));

        $response->assertOk();
    }

    public function test_student_not_enrolled_does_not_see_module_in_module_select(): void
    {
        $fixture = $this->createAssessmentFixture();

        $this->actingAs($fixture['student2']);

        $response = $this->get(route('hive.documents.module-select'));

        $response->assertOk();
    }

    public function test_student_can_acknowledge_document_once(): void
    {
        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $document = Document::factory()->create([
            'module_id' => $fixture['module']->id,
            'audience' => 'module_students',
            'is_published' => true,
        ]);

        $response = $this->post(route('hive.documents.acknowledge', $document));

        $response->assertRedirect();
        $this->assertDatabaseHas('document_acknowledgements', [
            'document_id' => $document->id,
            'user_id' => $fixture['student1']->id,
        ]);
    }

    public function test_re_acknowledging_does_not_create_duplicate(): void
    {
        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $document = Document::factory()->create([
            'module_id' => $fixture['module']->id,
            'audience' => 'module_students',
            'is_published' => true,
        ]);

        $this->post(route('hive.documents.acknowledge', $document));
        $this->post(route('hive.documents.acknowledge', $document));

        $this->assertDatabaseCount('document_acknowledgements', 1);
    }

    public function test_student_cannot_view_acknowledgements(): void
    {
        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $document = Document::factory()->create([
            'module_id' => $fixture['module']->id,
            'audience' => 'module_students',
            'is_published' => true,
        ]);

        $response = $this->get(route('hive.documents.acknowledgements', $document));

        $response->assertRedirect();
    }
}
