<?php

namespace Database\Factories\MOIREI\Vouchers\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use MOIREI\Vouchers\Tests\Product;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
