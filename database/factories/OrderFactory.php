<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_claimed_by_sales_rep' => $this->faker->boolean(),
            'is_claimed_by_setter' => $this->faker->boolean(),
            'invoice_number' => $this->faker->unique()->numerify('INV-####'),
            'invoice_date' => $this->faker->dateTimeBetween('-7 day'),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'product_name' => $this->faker->word(),
            'amount_excluding_vat' => $this->faker->numberBetween(1, 1000),
            'amount_including_vat' => $this->faker->numberBetween(1, 1000),
        ];
    }
}
