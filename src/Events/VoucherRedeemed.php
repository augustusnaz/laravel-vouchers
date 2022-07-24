<?php

namespace MOIREI\Vouchers\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class VoucherRedeemed
{
    use SerializesModels;

    public $redeemer;

    /** @var Model */
    public $voucher;

    public function __construct($redeemer, Model $voucher)
    {
        $this->redeemer = $redeemer;
        $this->voucher = $voucher;
    }
}
