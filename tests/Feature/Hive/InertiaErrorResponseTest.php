<?php

namespace Tests\Feature\Hive;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Hive\Traits\CreatesAssessmentFixture;

/**
 * Inertia rejects any non-Inertia response with:
 * "All Inertia requests must receive a valid Inertia response, however a plain
 * JSON response was received."
 *
 * Inertia's client sends X-Requested-With: XMLHttpRequest, so Request::ajax()
 * is true for Inertia requests. Exception handlers that check ajax()/expectsJson()
 * before the X-Inertia header therefore hand plain JSON to Inertia, which fails.
 *
 * The correct Inertia behaviour for a redirect is either a normal 3xx, or Inertia's
 * 409 "external/location" redirect (X-Inertia-Location header) which the client
 * follows. Both are valid; a JSON body is not.
 */
class InertiaErrorResponseTest extends HiveTestCase
{
    use CreatesAssessmentFixture;

    private function inertiaHeaders(): array
    {
        // HandleInertiaRequests::version() falls through to Inertia's default,
        // which is the Vite manifest hash. A GET Inertia request must carry the
        // matching X-Inertia-Version or Inertia short-circuits with its own 409
        // version-change response before the controller runs.
        $manifest = public_path('build/manifest.json');
        $version = is_file($manifest) ? md5_file($manifest) : null;

        return array_filter([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'text/html, application/xhtml+xml',
        ], fn ($v) => $v !== null);
    }

    private function assertIsValidInertiaRedirect($response): void
    {
        $status = $response->getStatusCode();
        $isRedirect = $status >= 300 && $status < 400;
        $isInertiaLocation = $status === 409
            && $response->headers->has('X-Inertia-Location');

        $this->assertTrue(
            $isRedirect || $isInertiaLocation,
            "Expected an Inertia redirect (3xx or 409 + X-Inertia-Location), got {$status}."
        );
    }

    private function assertBodyIsNotTheJsonMismatch($response): void
    {
        $body = (string) $response->getContent();

        $this->assertStringNotContainsString('"error_id"', $body, 'Inertia request must not receive a JSON error payload.');
        $this->assertStringNotContainsString(
            'All Inertia requests must receive a valid Inertia response',
            $body,
            'Inertia request must not receive the JSON mismatch error.'
        );
    }

    public function test_inertia_403_gets_a_valid_inertia_response_not_json(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.manage', $module), $this->inertiaHeaders());

        $this->assertIsValidInertiaRedirect($response);
        $this->assertBodyIsNotTheJsonMismatch($response);
    }

    public function test_inertia_403_carries_an_error_flash_for_the_ui(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.manage', $module), $this->inertiaHeaders());

        $response->assertSessionHas('error');
        $this->assertSame(
            'You do not have permission to perform that action.',
            session('error')['message'] ?? null
        );
    }

    public function test_non_inertia_403_still_redirects_with_flash(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.manage', $module));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_inertia_unauthenticated_redirects_to_login(): void
    {
        $response = $this->get(route('hive.grades.index'), $this->inertiaHeaders());

        $this->assertIsValidInertiaRedirect($response);
        $this->assertBodyIsNotTheJsonMismatch($response);
    }

    public function test_plain_json_api_request_still_receives_json_on_403(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        // A genuine API/JSON client (no X-Inertia) must still get JSON.
        $response = $this->getJson(route('hive.grades.manage', $module));

        $response->assertStatus(403);
        $this->assertArrayHasKey('message', $response->json());
    }

    public function test_plain_ajax_non_inertia_request_still_receives_json_on_403(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        // Without X-Inertia, an XHR is a real AJAX client and should get JSON.
        $response = $this->get(route('hive.grades.manage', $module), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(403);
        $this->assertArrayHasKey('message', $response->json());
    }
}
