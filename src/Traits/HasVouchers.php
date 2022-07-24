<?php

namespace MOIREI\Vouchers\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use MOIREI\Vouchers\Facades\Vouchers;

/**
 * @property \Illuminate\Support\Collection $vouchers
 */
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
            null,
            'voucher_id',
        );
    }

    /**
     * Create a voucher for the model.
     *
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher
     */
    public function createVoucher(array $attributes = [])
    {
        return Vouchers::create(
            item: $this,
            attributes: $attributes
        );
    }

    /**
     * Create vouchers for the model.
     *
     * @param int $amount
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher[]
     */
    public function createVouchers(int $amount, array $attributes = []): array
    {
        return Vouchers::createMany(
            amount: $amount,
            item: $this,
            attributes: $attributes
        );
    }
}
