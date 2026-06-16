<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class TwoFactorAuthenticationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_authentication_can_be_enabled(): void
    {
        $this->markTestSkipped('Application uses custom Hive routes and does not expose Jetstream 2FA routes');
    }

    public function test_recovery_codes_can_be_regenerated(): void
    {
        $this->markTestSkipped('Application uses custom Hive routes and does not expose Jetstream 2FA routes');
    }

    public function test_two_factor_authentication_can_be_disabled(): void
    {
        $this->markTestSkipped('Application uses custom Hive routes and does not expose Jetstream 2FA routes');
    }
}
