<?php

namespace MOIREI\Vouchers\Exceptions;

use MOIREI\Vouchers\Models\Voucher;

class VoucherRedeemsExhausted extends \Exception
{
    protected $message = 'The voucher redeems has been exhausted.';

    protected $voucher;

    public static function create(Voucher $voucher)
    {
        return new static($voucher);
    }

    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
    }
}