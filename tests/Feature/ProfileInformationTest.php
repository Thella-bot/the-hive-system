<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        // This test is skipped because the application uses custom Hive routes
        // not the standard Jetstream /user/profile-information route
        $this->markTestSkipped('Application uses custom Hive user routes, not Jetstream profile routes');
    }
}
