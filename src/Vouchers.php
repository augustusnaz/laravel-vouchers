<?php

namespace MOIREI\Vouchers;

use MOIREI\Vouchers\Exceptions\VoucherExpired;
use MOIREI\Vouchers\Exceptions\VoucherIsInvalid;
use Illuminate\Database\Eloquent\Model;

class Vouchers
{
    /** @var VoucherGenerator */
    private $generator;

    public function __construct(VoucherGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generate the specified amount of codes and return
     * an array with all the generated codes.
     *
     * @param int $amount
     * @return string[]
     */
    public function generateCodes(int $amount): array
    {
        $codes = [];
        for ($i = 1; $i <= $amount; $i++) {
            $codes[] = static::generateUniqueCode();
        }
        return $codes;
    }

    /**
     * Create one discount code.
     *
     * @param Model|string|array $item
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher[]
     */
    public function create(
        Model|string|array $item = null,
        array $attributes = []
    ) {
        $model = static::model();
        $attributes['code'] = static::generateUniqueCode();
        $voucher = new $model($attributes);
        if ($item) {
            $voucher->setItems($item);
        }
        $vouchers[] = $voucher;
        $voucher->save();
        return $voucher;
    }

    /**
     * Create many discount codes.
     *
     * @param int $amount
     * @param Model|string|array $item
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher[]
     */
    public function createMany(
        int $amount = 1,
        Model|string|array $item = null,
        array $attributes = []
    ) {
        $model = static::model();
        foreach ($this->generateCodes($amount) as $voucherCode) {
            $attributes['code'] = $voucherCode;
            $voucher = new $model($attributes);
            if ($item) {
                $voucher->setItems($item);
            }
            $vouchers[] = $voucher;
            $voucher->save();
        }

        return $vouchers;
    }

    /**
     * Create many reusable vouchers.
     *
     * @param Model|string|array $item
     * @param int $amount
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher[]
     */
    public function createReuse(
        int $amount = 1,
        Model|string|array $item,
        array $attributes = [],
        int $reuse = 1
    ) {
        if (!isset($attributes['quantity'])) {
            $attributes['quantity'] = $reuse + 1;
        }
        return $this->createMany(
            amount: $amount,
            item: $item,
            attributes: $attributes,
        );
    }

    /**
     * Check if code is a valid voucher.
     *
     * @param string $code
     * @throws VoucherIsInvalid
     * @throws VoucherExpired
     * @return \MOIREI\Vouchers\Models\Voucher|null
     */
    public function check(string $code)
    {
        $model = static::model();
        $voucher = $model::whereCode($code)->first();
        if ($voucher === null) {
            throw VoucherIsInvalid::withCode($code);
        }
        if ($voucher->expired) {
            throw VoucherExpired::create($voucher);
        }
        return $voucher;
    }

    /**
     * Generate unique code
     * @return string
     */
    public function generateUniqueCode(): string
    {
        $model = static::model();
        $voucher = $this->generator->generateUnique();
        while ($model::whereCode($voucher)->count() > 0) {
            $voucher = $this->generator->generateUnique();
        }
        return $voucher;
    }

    /**
     * Get model class
     * @return string
     */
    public function model(): string
    {
        return config('vouchers.models.vouchers', \MOIREI\Vouchers\Models\Voucher::class);
    }
}
