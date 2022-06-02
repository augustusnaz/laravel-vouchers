<?php

namespace MOIREI\Vouchers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MOIREI\Vouchers\Vouchers
 */
class Vouchers extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'vouchers';
    }
}
