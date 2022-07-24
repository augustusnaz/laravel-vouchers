<?php

namespace MOIREI\Vouchers\Facades;

use Illuminate\Support\Facades\Facade;
use MOIREI\Vouchers\Models\Voucher;

/**
 * @method static string[] generateCodes(int $amount = 1)
 * @method static Voucher create(Model|string|array $item = null, array $attributes = [])
 * @method static Voucher[] createMany(int $amount = 1, Model|string|array $item = null, array $attributes = [])
 * @method static Voucher[] createReuse(int $amount = 1, Model|string|array $item, array $attributes = [], int $reuse = 1)
 * @method static Voucher|null check(string $code)
 * @method static string generateUniqueCode()
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
