<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'surname' => fake()->lastName(),
            'firstname' => fake()->firstName(),
            'othername' => fake()->firstName(),
            'phone' => fake()->unique()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => '200',
            'account_status' => 'ACTIVE',
            'title' => fake()->randomElement(['Mr', 'Mrs', 'Dr', 'Prof']),
        ];
    }

    public function recommender(): static
    {
        return $this->state(fn () => ['role' => '200']);
    }

    public function approver(): static
    {
        return $this->state(fn () => ['role' => '300']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['account_status' => 'INACTIVE']);
    }
}
