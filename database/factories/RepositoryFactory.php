<?php

namespace Database\Factories;

use App\Models\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepositoryFactory extends Factory
{
    protected $model = Repository::class;

    public function definition(): array
    {
        $name = $this->faker->userName() . '/' . $this->faker->word();
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'url' => 'https://github.com/' . $name . '.git',
            'local_path' => '/var/www/' . $this->faker->word(),
            'branch' => 'main',
            'last_pulled_at' => now(),
        ];
    }
}