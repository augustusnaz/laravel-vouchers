<?php

namespace MOIREI\Vouchers\Events;

use Illuminate\Queue\SerializesModels;
use MOIREI\Vouchers\Models\Voucher;

class VoucherRedeemed
{
    use SerializesModels;

    public $redeemer;

    /** @var Voucher */
    public $voucher;

    public function __construct($redeemer, Voucher $voucher)
    {
        $this->redeemer = $redeemer;
        $this->voucher = $voucher;
    }
}