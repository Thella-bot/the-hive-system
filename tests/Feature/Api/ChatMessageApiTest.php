<?php

namespace Tests\Feature\Api;

use App\Models\ChatChannel;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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

        $this->artisan('db:seed', [
            '--class' => RolePermissionSeeder::class,
        ]);

        $this->user = User::factory()->create();
        $this->user->assignRole('student');

        $this->channel = ChatChannel::factory()->direct([$this->user->id])->create();

        Sanctum::actingAs($this->user, ['*']);
    }

    public function test_can_list_messages_for_own_channel(): void
    {
        $response = $this->getJson("/api/channels/{$this->channel->id}/messages");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_unauthorized_user_cannot_list_messages(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->getJson("/api/channels/{$this->channel->id}/messages");

        $response->assertForbidden();
    }

    public function test_can_create_message(): void
    {
        $response = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Hello, world!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'message', 'user_id', 'chat_channel_id']);

        $this->assertDatabaseHas('messages', [
            'message' => 'Hello, world!',
            'chat_channel_id' => $this->channel->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_cannot_create_message_for_channel_without_membership(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Unauthorized message',
        ]);

        $response->assertForbidden();
    }

    public function test_message_requires_non_empty_content(): void
    {
        $response = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => '   ',
        ]);

        $response->assertUnprocessable();
    }

    public function test_message_strips_html_tags(): void
    {
        $response = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => '<script>alert("xss")</script>Hello',
        ]);

        $response->assertCreated();
        // strip_tags removes HTML tags but keeps content - Vue's {{ }} escaping prevents XSS
        $this->assertDatabaseHas('messages', [
            'message' => 'alert("xss")Hello',
        ]);
    }

    public function test_user_can_update_own_message(): void
    {
        // Create a message first
        $createResponse = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Original message',
        ]);
        $messageId = $createResponse->json('id');

        $response = $this->patchJson("/api/channels/{$this->channel->id}/messages/{$messageId}", [
            'message' => 'Updated message',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('messages', [
            'id' => $messageId,
            'message' => 'Updated message',
        ]);
    }

    public function test_user_can_delete_own_message(): void
    {
        // Create a message first
        $createResponse = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Message to delete',
        ]);
        $messageId = $createResponse->json('id');

        $response = $this->deleteJson("/api/channels/{$this->channel->id}/messages/{$messageId}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('messages', [
            'id' => $messageId,
        ]);
    }

    public function test_user_cannot_update_another_users_message(): void
    {
        // Create a message as the other user
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        $this->channel->participants = array_merge($this->channel->participants ?? [], [(string) $otherUser->id]);
        $this->channel->save();

        $message = $this->channel->messages()->create([
            'user_id' => $otherUser->id,
            'message' => 'Other user message',
        ]);

        $response = $this->patchJson("/api/channels/{$this->channel->id}/messages/{$message->id}", [
            'message' => 'Hacked message',
        ]);

        $response->assertForbidden();
    }

    public function test_direct_channel_creation(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');

        $response = $this->postJson("/api/channels/direct", [
            'participants' => [$otherUser->id],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('chat_channels', [
            'channel_type' => 'direct',
        ]);
    }

    public function test_direct_channel_requires_at_least_one_participant(): void
    {
        $response = $this->postJson("/api/channels/direct", [
            'participants' => [],
        ]);

        $response->assertUnprocessable();
    }

    public function test_direct_channel_with_same_participants_returns_existing(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');

        // Create direct channel first time
        $response1 = $this->postJson("/api/channels/direct", [
            'participants' => [$otherUser->id],
        ]);
        $response1->assertCreated();
        $channelId = $response1->json('id');

        // Try to create again with same participants
        $response2 = $this->postJson("/api/channels/direct", [
            'participants' => [$otherUser->id],
        ]);
        $response2->assertOk();
        $this->assertEquals($channelId, $response2->json('id'));
    }

    public function test_can_upload_attachment(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->image('test-image.png', 100, 100);

        $response = $this->postJson('/api/chat/attachments', [
            'file' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['path', 'name', 'size', 'mime_type', 'url']);
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('large-file.pdf', 15000); // 15MB

        $response = $this->postJson('/api/chat/attachments', [
            'file' => $file,
        ]);

        $response->assertUnprocessable();
    }

    public function test_can_send_message_with_attachment(): void
    {
        // Upload attachment first
        $file = \Illuminate\Http\UploadedFile::fake()->image('test-image.png', 100, 100);
        $uploadResponse = $this->postJson('/api/chat/attachments', [
            'file' => $file,
        ]);
        $attachment = $uploadResponse->json();

        // Send message with attachment
        $response = $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Check out this image',
            'attachments' => [$attachment],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('messages', [
            'message' => 'Check out this image',
        ]);
    }

    public function test_can_search_messages(): void
    {
        // Create some messages
        $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Hello world',
        ]);
        $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Goodbye world',
        ]);
        $this->postJson("/api/channels/{$this->channel->id}/messages", [
            'message' => 'Something else',
        ]);

        $response = $this->getJson("/api/channels/{$this->channel->id}/search?q=world");

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data);
    }

    public function test_search_requires_minimum_2_characters(): void
    {
        $response = $this->getJson("/api/channels/{$this->channel->id}/search?q=a");

        $response->assertUnprocessable();
    }

    public function test_unauthorized_user_cannot_search_messages(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->getJson("/api/channels/{$this->channel->id}/search?q=test");

        $response->assertForbidden();
    }

    public function test_can_mark_messages_as_read(): void
    {
        // Create some messages from other users
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        $this->channel->participants = array_merge($this->channel->participants ?? [], [(string) $otherUser->id]);
        $this->channel->save();

        $message1 = $this->channel->messages()->create([
            'user_id' => $otherUser->id,
            'message' => 'Message 1',
        ]);
        $message2 = $this->channel->messages()->create([
            'user_id' => $otherUser->id,
            'message' => 'Message 2',
        ]);

        $response = $this->postJson("/api/channels/{$this->channel->id}/read", [
            'last_read_id' => $message2->id,
        ]);

        $response->assertOk();
        $this->assertEquals(2, $response->json('marked_as_read'));
    }

    public function test_can_get_read_receipts_for_message(): void
    {
        $message = $this->channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => 'Test message',
        ]);

        // Mark as read
        $this->postJson("/api/channels/{$this->channel->id}/read", [
            'last_read_id' => $message->id,
        ]);

        $response = $this->getJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reads");

        $response->assertOk();
        $this->assertEquals(0, $response->json('read_count')); // Author doesn't count
    }

    public function test_can_get_unread_count(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        $this->channel->participants = array_merge($this->channel->participants ?? [], [(string) $otherUser->id]);
        $this->channel->save();

        $this->channel->messages()->create([
            'user_id' => $otherUser->id,
            'message' => 'Unread message 1',
        ]);
        $this->channel->messages()->create([
            'user_id' => $otherUser->id,
            'message' => 'Unread message 2',
        ]);

        $response = $this->getJson("/api/channels/{$this->channel->id}/unread");

        $response->assertOk();
        $this->assertEquals(2, $response->json('unread'));
    }

    public function test_unauthorized_user_cannot_mark_as_read(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->postJson("/api/channels/{$this->channel->id}/read", [
            'last_read_id' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_can_add_reaction_to_message(): void
    {
        $message = $this->channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => 'Test message',
        ]);

        $response = $this->postJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions", [
            'emoji' => '👍',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('message_reactions', [
            'message_id' => $message->id,
            'user_id' => $this->user->id,
            'emoji' => '👍',
        ]);
    }

    public function test_can_remove_reaction_from_message(): void
    {
        $message = $this->channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => 'Test message',
        ]);

        // Add reaction first
        $this->postJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions", [
            'emoji' => '👍',
        ]);

        $response = $this->deleteJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions", [
            'emoji' => '👍',
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('message_reactions', [
            'message_id' => $message->id,
            'user_id' => $this->user->id,
            'emoji' => '👍',
        ]);
    }

    public function test_can_get_reactions_for_message(): void
    {
        $message = $this->channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => 'Test message',
        ]);

        // Add reaction
        $this->postJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions", [
            'emoji' => '👍',
        ]);

        $response = $this->getJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions");

        $response->assertOk();
        $data = $response->json('reactions');
        $this->assertCount(1, $data);
        $this->assertEquals('👍', $data[0]['emoji']);
        $this->assertEquals(1, $data[0]['count']);
    }

    public function test_same_user_can_add_multiple_different_reactions(): void
    {
        $message = $this->channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => 'Test message',
        ]);

        $this->postJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions", [
            'emoji' => '👍',
        ]);
        $this->postJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions", [
            'emoji' => '❤️',
        ]);

        $response = $this->getJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions");

        $response->assertOk();
        $this->assertCount(2, $response->json('reactions'));
    }

    public function test_unauthorized_user_cannot_add_reaction(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $message = $this->channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => 'Test message',
        ]);

        $response = $this->postJson("/api/channels/{$this->channel->id}/messages/{$message->id}/reactions", [
            'emoji' => '👍',
        ]);

        $response->assertForbidden();
    }

    public function test_can_send_typing_indicator(): void
    {
        $response = $this->postJson("/api/channels/{$this->channel->id}/typing", [
            'is_typing' => true,
        ]);

        $response->assertOk();
    }

    public function test_typing_indicator_requires_boolean(): void
    {
        $response = $this->postJson("/api/channels/{$this->channel->id}/typing", [
            'is_typing' => 'yes',
        ]);

        $response->assertUnprocessable();
    }

    public function test_unauthorized_user_cannot_send_typing(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->postJson("/api/channels/{$this->channel->id}/typing", [
            'is_typing' => true,
        ]);

        $response->assertForbidden();
    }

    public function test_typing_indicator_requires_boolean(): void
    {
        $response = $this->postJson("/api/channels/{$this->channel->id}/typing", [
            'is_typing' => 'yes',
        ]);

        $response->assertUnprocessable();
    }

    public function test_unauthorized_user_cannot_send_typing(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->postJson("/api/channels/{$this->channel->id}/typing", [
            'is_typing' => true,
        ]);

        $response->assertForbidden();
    }
}
