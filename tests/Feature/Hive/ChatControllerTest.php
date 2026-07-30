<?php

namespace Tests\Feature\Hive;

use App\Models\ChatChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatControllerTest extends HiveTestCase
{
    public function test_chat_index_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.chat.index'));

        $response->assertOk();
    }

    public function test_chat_channel_shows_channel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $channel = ChatChannel::factory()->create();
        $user->chatChannels()->attach($channel);

        $this->actingAs($user);

        $response = $this->get(route('hive.chat.channel', $channel));

        $response->assertOk();
    }

    public function test_chat_module_shows_module(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = \App\Models\Module::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.chat.module', $module));

        $response->assertOk();
    }
}