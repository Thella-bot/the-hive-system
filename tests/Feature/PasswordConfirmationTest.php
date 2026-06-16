<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_confirmed(): void
    {
        $this->markTestSkipped('Application does not expose Jetstream confirm-password route (fortify.views is false)');
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $this->markTestSkipped('Application does not expose Jetstream confirm-password route (fortify.views is false)');
    }
}
