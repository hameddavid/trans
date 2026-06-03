<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'matric_number' => fake()->unique()->numerify('RUN/####/####'),
            'SURNAME' => fake()->lastName(),
            'FIRSTNAME' => fake()->firstName(),
            'OTHERNAME' => fake()->firstName(),
            'EMAIL1' => fake()->unique()->safeEmail(),
            'prog_code' => fake()->numerify('###'),
            'status' => 'active',
            'sex' => fake()->randomElement(['Male', 'Female']),
        ];
    }
}
