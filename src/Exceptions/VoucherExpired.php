<?php

namespace MOIREI\Vouchers\Exceptions;

use Illuminate\Database\Eloquent\Model;

class VoucherExpired extends \Exception
{
    protected $message = 'The voucher is already expired.';

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
