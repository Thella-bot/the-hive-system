<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentAcknowledgement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentAcknowledgementFactory extends Factory
{
    protected $model = DocumentAcknowledgement::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'user_id' => User::factory(),
            'acknowledged_at' => now(),
        ];
    }
}
