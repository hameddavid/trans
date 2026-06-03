<?php

namespace Database\Factories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'surname' => fake()->lastName(),
            'firstname' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'mobile' => fake()->unique()->numerify('080########'),
            'matric_number' => fake()->unique()->numerify('RUN/####/####'),
            'sex' => fake()->randomElement(['Male', 'Female']),
            'type' => 'applicant',
        ];
    }
}
