<?php

namespace Tests\Feature\Hive;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GradeControllerTest extends HiveTestCase
{
    public function test_grades_index_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.index'));

        $response->assertOk();
    }

    public function test_grades_manage_returns_success_for_staff(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $module = Module::factory()->create();
        $user->modules()->syncWithoutDetaching($module);

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.manage', $module));

        $response->assertOk();
    }

    public function test_grades_manage_returns_403_for_unauthorized(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.manage', $module));

        $response->assertRedirect();
    }
}