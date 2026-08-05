<?php

namespace Database\Factories;

use App\Models\SectionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SectionLog>
 */
class SectionLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = SectionLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCapacity = fake()->numberBetween(20, 100);
        $occupied = fake()->numberBetween(0, $totalCapacity);
        $reserved = fake()->numberBetween(0, $totalCapacity - $occupied);
        $available = $totalCapacity - $occupied - $reserved;

        return [
            'section_code' => fake()->randomElement(['SEC-A', 'SEC-B']),
            'section_name' => fake()->randomElement([
                'General Reading', 
                'Periodicals', 
                'Reference Section'
            ]),
            'date' => fake()->date(),
            'hour' => fake()->numberBetween(0, 23),
            'occupied' => $occupied,
            'reserved' => $reserved,
            'available' => $available,
            'total_capacity' => $totalCapacity,
            'updated_at' => fake()->dateTime(),
        ];
    }
}
