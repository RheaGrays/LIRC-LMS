<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->numerify('####-#####'),
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->lastName(),
            'department' => fake()->randomElement([
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Computer Science',
                'Bachelor of Arts in Communication'
            ]),
            'year_level' => fake()->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year']),
            'email' => fake()->optional()->safeEmail(),
            'contact' => fake()->optional()->phoneNumber(),
            'photo_path' => null,
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
