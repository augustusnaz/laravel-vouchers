<?php

namespace MOIREI\Vouchers\Traits;

use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Facades\Vouchers;

trait HasVouchers
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'model');
    }

    /**
     * @param int $amount
     * @param array $data
     * @param null $expires_at
     * @return Voucher[]
     */
    public function createVouchers(int $amount, array $attributes = [ 'data' => [], 'expires_at' => null ])
    {
        return Vouchers::create($this, $amount, $attributes);
    }

    /**
     * @param array $data
     * @param null $expires_at
     * @return Voucher
     */
    public function createVoucher(array $attributes = [ 'data' => [], 'expires_at' => null ])
    {
        $vouchers = Vouchers::create($this, 1, $attributes);

        return $vouchers[0];
    }
}