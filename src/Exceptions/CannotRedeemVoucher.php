<?php

namespace MOIREI\Vouchers\Exceptions;

use Illuminate\Database\Eloquent\Model;
use MOIREI\Vouchers\Models\Voucher;

class CannotRedeemVoucher extends \Exception
{
    protected $message = 'Instance is disallowed to redeem this voucher or provided item not allowed.';

    protected $voucher;
    protected $item;

    public static function create(Voucher $voucher, Model|null $item = null)
    {
        return new static($voucher, $item);
    }

    public function __construct(Voucher $voucher, Model|null $item = null)
    {
        $this->voucher = $voucher;
        $this->item = $item;
    }
}
