<?php

namespace MOIREI\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVoucherable extends Model
{
    protected $with = ['item'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('vouchers.tables.item_pivot_table', 'item_voucher');
    }

    public function item()
    {
        return $this->morphTo();
    }
}
