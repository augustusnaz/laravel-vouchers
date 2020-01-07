<?php

namespace MOIREI\Vouchers\Traits;

use Vouchers;
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
     * @throws VoucherExpired
     * @throws VoucherIsInvalid
     * @throws VoucherRedeemsExhausted
     * @throws CannotRedeemVoucher
     * @throws VoucherAlreadyRedeemed
     * @return mixed
     */
    public function redeem(string $code)
    {
        $voucher = Vouchers::check($code);

        if (($is_redeemed = $voucher->isRedeemed()) && $voucher->isDisposable()) {
            throw VoucherAlreadyRedeemed::create($voucher);
        }
        if ($is_redeemed) {
            throw VoucherRedeemsExhausted::create($voucher);
        }
        if ( !$this->canRedeem($voucher) ) {
            throw CannotRedeemVoucher::create($voucher);
        }
        if ($voucher->isExpired()) {
            throw VoucherExpired::create($voucher);
        }

        $voucher->quantity_used++;
        $voucher->save();

        $this->vouchers()->attach($voucher, [
            'redeemed_at' => now()
        ]);

        event(new VoucherRedeemed($this, $voucher));

        return $voucher;
    }

    /**
     * @param Voucher $voucher
     * @throws VoucherExpired
     * @throws VoucherIsInvalid
     * @throws VoucherRedeemsExhausted
     * @throws CannotRedeemVoucher
     * @throws VoucherAlreadyRedeemed
     * @return mixed
     */
    public function redeemVoucher(Voucher $voucher)
    {
        return $this->redeem($voucher->code);
    }

    /**
     * @param string|Voucher $voucher
     * @return boolean
     */
    public function canRedeem($voucher)
    {
        if(is_string($voucher)){
            $voucher = Vouchers::check($voucher);
        }

        if($voucher->allowed_users_array){
            foreach($voucher->allowed_users_array as $allowed_users){
                if($allowed_users['voucherable_type'] === $this->getMorphClass() &&
                   $allowed_users['voucherable_id'] === $this->getKey() ) return true;
            }
            return false;
        }
        if($voucher->disallowed_users_array){
            foreach($voucher->disallowed_users_array as $allowed_users){
                if($allowed_users['voucherable_type'] === $this->getMorphClass() &&
                   $allowed_users['voucherable_id'] === $this->getKey() ) return false;
            }
        }

        return true;
    }


    /**
     * @return mixed
     */
    public function vouchers()
    {
        return $this->morphToMany(Voucher::class, 'voucherable', config('vouchers.pivot_table', 'user_voucher'));
    }
}