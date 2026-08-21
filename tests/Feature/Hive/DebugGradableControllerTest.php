<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Feature\Hive\Traits\CreatesAssessmentFixture;

class DebugGradableControllerTest extends HiveTestCase
{
    use CreatesAssessmentFixture;

    public function test_debug_role_check(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');
        
        // Refresh user from database
        $user->refresh();
        
        echo "User ID: " . $user->id . PHP_EOL;
        echo "Has chef-instructor: " . ($user->hasRole('chef-instructor') ? 'YES' : 'NO') . PHP_EOL;
        echo "Roles: " . print_r((array) $user->getRoleNames(), true) . PHP_EOL;
        
        $role = Role::where('name', 'chef-instructor')->first();
        echo "Role exists: " . ($role ? 'YES' : 'NO') . PHP_EOL;
        
        // Check what the middleware would see
        $request = \Illuminate\Http\Request::create('http://localhost/hive/gradables/create', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $middleware = new \Spatie\Permission\Middleware\RoleMiddleware();
        
        try {
            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            }, 'super-admin|academic-director|it-support|chef-instructor|pastry-instructor|sous-chef|examination-cell');
            echo "Middleware response: " . $response->getStatusCode() . PHP_EOL;
        } catch (\Exception $e) {
            echo "Middleware exception: " . get_class($e) . ": " . $e->getMessage() . PHP_EOL;
        }
    }
}
