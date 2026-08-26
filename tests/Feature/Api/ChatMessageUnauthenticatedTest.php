<?php

namespace Tests\Feature\Api;

use App\Models\ChatChannel;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatMessageUnauthenticatedTest extends TestCase
{
    use RefreshDatabase;

    private ChatChannel $channel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', [
            '--class' => RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $this->channel = ChatChannel::factory()->direct([$user->id])->create();
    }

    public function test_unauthenticated_user_cannot_list_messages(): void
    {
        $response = $this->getJson("/api/channels/{$this->channel->id}/messages");

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_message(): void
    {
        $response = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Hello from unauthenticated user',
        ]);

        $response->assertUnauthorized();
    }
}
