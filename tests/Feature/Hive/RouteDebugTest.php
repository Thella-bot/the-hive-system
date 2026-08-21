<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteDebugTest extends HiveTestCase
{
    public function test_debug_gradable_create_path(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');
        $this->actingAs($user);

        $response = $this->get('/hive/gradables/create');
        dump('Status with path:', $response->status());
    }
}
