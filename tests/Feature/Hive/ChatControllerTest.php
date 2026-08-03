<?php

namespace Tests\Feature\Hive;

use App\Models\ChatChannel;
use App\Models\Module;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatControllerTest extends HiveTestCase
{
    public function test_chat_index_returns_success_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.chat.index'));

        $response->assertOk();
    }

    public function test_chat_index_returns_success_for_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $response = $this->get(route('hive.chat.index'));

        $response->assertOk();
    }

    public function test_chat_channel_shows_general_channel_for_staff(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $channel = ChatChannel::factory()->create([
            'channel_type' => 'general',
            'channel_id' => null,
            'name' => 'All Staff',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.chat.channel', $channel));

        $response->assertOk();
    }

    public function test_chat_channel_rejects_student_for_general_channel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $channel = ChatChannel::factory()->create([
            'channel_type' => 'general',
            'channel_id' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.chat.channel', $channel));

        $response->assertRedirect();
    }

    public function test_chat_module_shows_channel_for_enrolled_student(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $module = Module::factory()->create([
            'programme_id' => Programme::factory()->create()->id,
        ]);

        $student->modules()->attach($module->id);

        $this->actingAs($student);

        $response = $this->get(route('hive.chat.module', $module));

        $response->assertOk();
    }
}