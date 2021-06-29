<?php

namespace MOIREI\Vouchers\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Facades\Vouchers;

trait HasVouchers
{
    /**
     * Set the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function vouchers(): MorphToMany
    {
        return $this->morphToMany(
            config('vouchers.models.vouchers'),
            'item',
            config('vouchers.tables.item_pivot_table', 'item_voucher'),
        );
    }

    /**
     * @param int $amount
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher[]
     */
    public function createVouchers(int $amount, array $attributes = ['data' => [], 'expires_at' => null]): array
    {
        return Vouchers::create($this, $amount, $attributes);
    }

    /**
     * @param array $data
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher
     */
    public function createVoucher(array $attributes = ['data' => [], 'expires_at' => null]): Voucher
    {
        $vouchers = Vouchers::create($this, 1, $attributes);

        return $vouchers[0];
    }
}
