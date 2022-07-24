<?php

namespace Database\Factories\MOIREI\Vouchers\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\VoucherScheme;

class VoucherFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Voucher::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 100),
            'limit_scheme' => $this->faker->randomElement(VoucherScheme::values()),
        ];
    }
}
