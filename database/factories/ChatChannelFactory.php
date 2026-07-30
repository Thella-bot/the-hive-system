<?php

namespace Database\Factories;

use App\Models\ChatChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatChannelFactory extends Factory
{
    protected $model = ChatChannel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'channel_type' => 'general',
            'channel_id' => null,
            'participants' => [],
        ];
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel_type' => 'general',
            'channel_id' => null,
            'name' => 'All Staff',
        ]);
    }

    public function department(int $departmentId): static
    {
        return $this->state(fn (array $attributes) => [
            'channel_type' => 'department',
            'channel_id' => $departmentId,
        ]);
    }

    public function module(int $moduleId): static
    {
        return $this->state(fn (array $attributes) => [
            'channel_type' => 'module',
            'channel_id' => $moduleId,
        ]);
    }

    public function direct(array $participants): static
    {
        return $this->state(fn (array $attributes) => [
            'channel_type' => 'direct',
            'participants' => $participants,
        ]);
    }
}