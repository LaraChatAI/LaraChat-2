<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(),
            'repository' => $this->faker->userName() . '/' . $this->faker->word(),
            'project_directory' => '/var/www/' . $this->faker->word(),
            'claude_session_id' => $this->faker->uuid(),
            'filename' => 'session_' . $this->faker->uuid() . '.json',
            'is_processing' => false,
            'archived' => false,
        ];
    }
}