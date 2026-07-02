<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $firstName . ' ' . $lastName,
            'age' => fake()->numberBetween(18, 45),
            'date_of_birth' => fake()->dateTimeBetween('-45 years', '-18 years')->format('Y-m-d'),
            'contact' => '09' . fake()->numerify('#########'),
            'emergency_contact' => '09' . fake()->numerify('#########'),
            'address' => fake()->address(),
            'occupation' => fake()->randomElement(['Housewife', 'Teacher', 'Nurse', 'Sales', 'Office Worker', 'Business Owner']),
        ];
    }
}
