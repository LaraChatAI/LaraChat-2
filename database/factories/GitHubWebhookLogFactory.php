<?php

namespace Database\Factories;

use App\Models\GitHubWebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class GitHubWebhookLogFactory extends Factory
{
    protected $model = GitHubWebhookLog::class;

    public function definition(): array
    {
        return [
            'event_type' => $this->faker->randomElement(['push', 'pull_request', 'issues', 'release', 'star', 'fork']),
            'delivery_id' => $this->faker->uuid(),
            'repository' => $this->faker->userName() . '/' . $this->faker->word(),
            'payload' => [
                'action' => $this->faker->randomElement(['opened', 'closed', 'created', 'published']),
                'repository' => [
                    'full_name' => $this->faker->userName() . '/' . $this->faker->word(),
                ],
            ],
            'status' => $this->faker->randomElement(['success', 'failed', 'processing']),
            'error_message' => null,
        ];
    }

    public function processing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'error_message' => 'Test error message',
            ];
        });
    }

    public function success()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'success',
                'error_message' => null,
            ];
        });
    }
}