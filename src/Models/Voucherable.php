<?php

namespace MOIREI\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;

class Voucherable extends Model
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('vouchers.pivot_table', 'user_voucher');
    }

    public function voucherables()
    {
        return $this->morphTo();
    }

}