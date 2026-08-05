<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $initials = Str::upper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password', // Assumes hashed cast in model
            'full_name' => "$firstName $lastName",
            'role' => fake()->randomElement(['Super Admin', 'Staff', 'Librarian']),
            'avatar_initials' => $initials,
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }
}
