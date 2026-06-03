<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'matric_number' => fake()->numerify('RUN/####/####'),
            'email' => fake()->safeEmail(),
            'names' => fake()->name(),
            'amount' => 12000.00,
            'rrr' => fake()->unique()->numerify('############'),
            'trans_ref' => fake()->unique()->uuid(),
            'destination' => 'NIGERIA',
            'gateway' => 'remita',
            'user_id' => null,
            'status_code' => '00',
            'status_msg' => 'success',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status_code' => '025',
            'status_msg' => 'pending',
        ]);
    }
}
