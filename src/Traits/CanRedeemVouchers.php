<?php

namespace MOIREI\Vouchers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use InvalidArgumentException;
use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Events\VoucherRedeemed;
use MOIREI\Vouchers\Exceptions\VoucherExpired;
use MOIREI\Vouchers\Exceptions\VoucherIsInvalid;
use MOIREI\Vouchers\Exceptions\VoucherAlreadyRedeemed;
use MOIREI\Vouchers\Exceptions\VoucherRedeemsExhausted;
use MOIREI\Vouchers\Exceptions\CannotRedeemVoucher;

trait CanRedeemVouchers
{
    /**
     * @param string $code
     * @param \Illuminate\Database\Eloquent\Model|string|null $item
     * @throws \MOIREI\Vouchers\Exceptions\VoucherExpired
     * @throws \MOIREI\Vouchers\Exceptions\VoucherIsInvalid
     * @throws \MOIREI\Vouchers\Exceptions\VoucherRedeemsExhausted
     * @throws \MOIREI\Vouchers\Exceptions\CannotRedeemVoucher
     * @throws \MOIREI\Vouchers\Exceptions\VoucherAlreadyRedeemed
     * @throws InvalidArgumentException
     * @return \MOIREI\Vouchers\Models\Voucher
     */
    public function redeem(string $code, Model|string|null $item = null): Voucher
    {
        $voucher = Vouchers::check($code);

        if (($is_redeemed = $voucher->isRedeemed($this, $item)) && $voucher->isDisposable()) {
            throw VoucherAlreadyRedeemed::create($voucher);
        }
        if ($is_redeemed) {
            throw VoucherRedeemsExhausted::create($voucher);
        }
        if (!$item) {
            $items = $voucher->items;
            if ($items->count() > 1) {
                throw new InvalidArgumentException("Please provide an item.");
            } else {
                $item = $items->shift();
            }
        }
        if (!$this->canRedeem($voucher, $item)) {
            throw CannotRedeemVoucher::create($voucher, $item);
        }
        if ($voucher->isExpired()) {
            throw VoucherExpired::create($voucher);
        }

        $voucher->incrementUse(1, $this, $item);
        $voucher->save();

        $this->vouchers()->attach($voucher, [
            'redeemed_at' => now()
        ]);

        event(new VoucherRedeemed($this, $voucher));

        return $voucher;
    }

    /**
     * Alias for redeem()
     *
     * @param string $code
     * @throws \MOIREI\Vouchers\Exceptions\VoucherExpired
     * @throws \MOIREI\Vouchers\Exceptions\VoucherIsInvalid
     * @throws \MOIREI\Vouchers\Exceptions\VoucherRedeemsExhausted
     * @throws \MOIREI\Vouchers\Exceptions\CannotRedeemVoucher
     * @throws \MOIREI\Vouchers\Exceptions\VoucherAlreadyRedeemed
     * @return \MOIREI\Vouchers\Models\Voucher
     */
    public function redeemVoucher(string $voucher): Voucher
    {
        return $this->redeem($voucher);
    }

    /**
     * Check whether the user instance can redeem this voucher
     * @param Voucher|string $voucher
     * @param \Illuminate\Database\Eloquent\Model|string|null $product
     * @return bool
     */
    public function canRedeem(Voucher|string $voucher, Model|string|null $product = null): bool
    {
        if (is_string($voucher)) {
            $voucher = Vouchers::check($voucher);
        }

        if ($product && !$voucher->isItem($product)) {
            return false;
        }

        if ($voucher->allowed_users_array) {
            foreach ($voucher->allowed_users_array as $allowed_users) {
                if (
                    $allowed_users['voucherable_type'] === $this->getMorphClass() &&
                    $allowed_users['voucherable_id'] === $this->getKey()
                ) return true;
            }
            return false;
        }
        if ($voucher->disallowed_users_array) {
            foreach ($voucher->disallowed_users_array as $allowed_users) {
                if (
                    $allowed_users['voucherable_type'] === $this->getMorphClass() &&
                    $allowed_users['voucherable_id'] === $this->getKey()
                ) return false;
            }
        }

        return true;
    }


    /**
     * Get vouchers
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function vouchers(): MorphToMany
    {
        return $this->morphToMany(
            config('vouchers.models.vouchers'),
            'voucherable',
            config('vouchers.tables.redeemer_pivot_table', 'redeemer_voucher'),
        );
    }
}
