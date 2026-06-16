<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        // Application uses custom Hive user routes, not Jetstream routes
        $this->markTestSkipped('Application uses custom Hive user routes, not Jetstream password routes');
    }

    public function test_current_password_must_be_correct(): void
    {
        $this->markTestSkipped('Application uses custom Hive user routes, not Jetstream password routes');
    }

    public function test_new_passwords_must_match(): void
    {
        $this->markTestSkipped('Application uses custom Hive user routes, not Jetstream password routes');
    }
}
