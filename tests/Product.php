<?php

namespace MOIREI\Vouchers\Tests;

use Database\Factories\MOIREI\Vouchers\Models\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MOIREI\Vouchers\Traits\HasVouchers;

class Product extends Model
{
    use HasVouchers, HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return new ProductFactory();
    }
}
