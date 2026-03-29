<?php

namespace MOIREI\Vouchers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use InvalidArgumentException;
use MOIREI\Vouchers\Events\VoucherRedeemed;
use MOIREI\Vouchers\Exceptions\VoucherExpired;
use MOIREI\Vouchers\Exceptions\VoucherAlreadyRedeemed;
use MOIREI\Vouchers\Exceptions\VoucherRedeemsExhausted;
use MOIREI\Vouchers\Exceptions\CannotRedeemVoucher;
use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\VoucherScheme;

/**
 * @property \Illuminate\Support\Collection $vouchers
 */
trait CanRedeemVouchers
{
    /**
     * Redeem a voucher or voucher code
     *
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

        if (!$voucher->active) {
            throw new \Exception("Cannot redeem inactive voucher");
        }

        if ($voucher->limit_scheme->is(VoucherScheme::REDEEMER)) {
            $is_redeemed = $voucher->isRedeemed($this);
        } else {
            $is_redeemed = $voucher->isRedeemed($item);
        }

        if ($is_redeemed && $voucher->isDisposable()) {
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

        if ($voucher->limit_scheme->is(VoucherScheme::ITEM)) {
            // $item is required
            if (!$item) {
                throw new InvalidArgumentException("Please provide an item.");
            }
            $voucher->incrementModelUse($item);
        } elseif ($voucher->limit_scheme->is(VoucherScheme::REDEEMER)) {
            $voucher->incrementModelUse($this);
        } else {
            $voucher->incrementUse();
        }

        $this->vouchers()->attach($voucher, [
            'redeemed_at' => now()
        ]);

        event(new VoucherRedeemed($this, $voucher));

        return $voucher;
    }

    /**
     * Alias for redeem()
     * Redeem a voucher or voucher code
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
     * Check whether the user instance can redeem a voucher or voucher code.
     *
     * @param Voucher|string $voucher
     * @param Model|array|string|null $product
     * @return bool
     */
    public function canRedeem(Voucher|string $voucher, Model|array|string|null $product = null): bool
    {
        if (is_string($voucher)) {
            $voucher = Vouchers::check($voucher);
        }

        if ($product !== null && $voucher->limit_scheme->is(VoucherScheme::ITEM)) {
            if ($product instanceof Model || is_string($product)) {
                $product = [$product];
            }
            return $voucher->isAnyItem($product);
        }

        return $voucher->isAllowed($this);
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
            'redeemer',
            config('vouchers.tables.redeemer_pivot_table', 'redeemer_voucher'),
            null,
            'voucher_id'
        );
    }
}
