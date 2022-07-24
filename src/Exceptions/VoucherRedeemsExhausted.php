<?php

namespace MOIREI\Vouchers\Exceptions;

use Illuminate\Database\Eloquent\Model;

class VoucherRedeemsExhausted extends \Exception
{
    protected $message = 'The voucher redeems has been exhausted.';

    protected $voucher;

    public static function create(Model $voucher)
    {
        return new static($voucher);
    }

    public function __construct(Model $voucher)
    {
        $this->voucher = $voucher;
    }
}
