<?php

namespace MOIREI\Vouchers\Tests;

use Database\Factories\MOIREI\Vouchers\Models\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MOIREI\Vouchers\Traits\CanRedeemVouchers;

class User extends Model
{
    use CanRedeemVouchers, HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return new UserFactory();
    }
}
