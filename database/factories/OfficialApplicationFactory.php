<?php

namespace Database\Factories;

use App\Models\OfficialApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficialApplicationFactory extends Factory
{
    protected $model = OfficialApplication::class;

    public function definition(): array
    {
        return [
            'matric_number' => fake()->numerify('RUN/####/####'),
            'applicant_id' => null,
            'delivery_mode' => 'soft',
            'transcript_type' => 'OFFICIAL',
            'address' => fake()->address(),
            'email' => fake()->safeEmail(),
            'destination' => 'NIGERIA',
            'recipient' => fake()->name(),
            'app_status' => 'PENDING',
            'graduation_year' => fake()->year(),
            'qualification' => 'B.Sc',
            'prog_name' => 'Computer Science',
            'dept' => 'Computer Science',
            'fac' => 'Sciences',
        ];
    }

    public function recommended(): static
    {
        return $this->state(fn () => ['app_status' => 'RECOMMENDED']);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['app_status' => 'APPROVED']);
    }
}
