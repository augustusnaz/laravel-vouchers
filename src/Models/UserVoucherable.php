<?php

namespace MOIREI\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;

class UserVoucherable extends Model
{
    protected $with = ['voucherable'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('vouchers.tables.redeemer_pivot_table', 'redeemer_voucher');
    }

    public function voucherable()
    {
        return $this->morphTo();
    }
}
