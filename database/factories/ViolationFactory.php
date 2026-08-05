<?php

namespace Database\Factories;

use App\Models\Violation;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Violation>
 */
class ViolationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Violation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'type' => fake()->randomElement([
                'Noise Disturbance', 
                'Food/Drinks', 
                'Unauthorized Area', 
                'Property Damage', 
                'Misconduct'
            ]),
            'notes' => fake()->optional()->sentence(),
            'severity' => fake()->randomElement(['minor', 'moderate', 'severe']),
            'date' => fake()->date(),
        ];
    }
}
