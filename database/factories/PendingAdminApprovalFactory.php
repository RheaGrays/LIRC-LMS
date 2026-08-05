<?php

namespace Database\Factories;

use App\Models\PendingAdminApproval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PendingAdminApproval>
 */
class PendingAdminApprovalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = PendingAdminApproval::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'full_name' => fake()->name(),
            'role' => fake()->randomElement(['Super Admin', 'Staff', 'Librarian']),
            'password_hash' => Hash::make('password'),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
