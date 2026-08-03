<?php

namespace Tests\Feature\Api;

use App\Models\ChatChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChatMessageApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ChatChannel $channel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('student');

        $this->channel = ChatChannel::factory()->create();
        $this->user->chatChannels()->attach($this->channel);

        Sanctum::actingAs($this->user, ['*']);
    }

    public function test_unauthenticated_user_cannot_list_messages(): void
    {
        $response = $this->getJson("/api/chat/channel/{$this->channel->id}/messages");

        $response->assertUnauthorized();
    }

    public function test_can_list_messages_for_own_channel(): void
    {
        $response = $this->getJson("/api/chat/channel/{$this->channel->id}/messages");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_unauthorized_user_cannot_list_messages(): void
    {
        $otherUser = User::factory()->create()->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->getJson("/api/chat/channel/{$this->channel->id}/messages");

        $response->assertForbidden();
    }

    public function test_can_create_message(): void
    {
        $response = $this->postJson("/api/chat/channel/{$this->channel->id}/messages", [
            'content' => 'Hello, world!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'content', 'sender_id', 'channel_id']]);

        $this->assertDatabaseHas('messages', [
            'content' => 'Hello, world!',
            'channel_id' => $this->channel->id,
            'sender_id' => $this->user->id,
        ]);
    }

    public function test_cannot_create_message_for_channel_without_membership(): void
    {
        $otherChannel = ChatChannel::factory()->create();
        $otherUser = User::factory()->create()->assignRole('student');
        $otherUser->chatChannels()->attach($otherChannel);
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->postJson("/api/chat/channel/{$this->channel->id}/messages", [
            'content' => 'Unauthorized message',
        ]);

        $response->assertForbidden();
    }
}