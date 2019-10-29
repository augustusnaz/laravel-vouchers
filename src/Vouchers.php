<?php

namespace MOIREI\Vouchers;

use MOIREI\Vouchers\Exceptions\VoucherExpired;
use MOIREI\Vouchers\Exceptions\VoucherIsInvalid;
use MOIREI\Vouchers\Models\Voucher;
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
     * @return array
     */
    public function generate(int $amount = 1): array
    {
        $codes = [];

        for ($i = 1; $i <= $amount; $i++) {
            $codes[] = $this->getUniqueVoucher();
        }

        return $codes;
    }

    /**
     * @param Model $model
     * @param int $amount
     * @param array $attributes
     * @return array
     */
    public function create(Model $model, $amount = 1, array $attributes = [])
    {
        $attributes['model_id'] = $model->getKey();
        $attributes['model_type'] = $model->getMorphClass();
        $data = $attributes['data']?? [];
        unset($attributes['data']);

        foreach ($this->generate($amount) as $voucherCode) {
            $attributes['code'] = $voucherCode;
            $voucher = $vouchers[] = Voucher::create($attributes);
            $voucher->data = $data;
            $voucher->data->save();
        }

        return $vouchers;
    }

    /**
     * @param Model $model
     * @param int $amount
     * @param array $attributes
     * @return array
     */
    public function createReuse(Model $model, $amount = 1, array $attributes = [], $reuse = 1)
    {
        $attributes['is_disposable'] = false;
        if(!isset($attributes['quantity'])){
            $attributes['quantity'] = $reuse+1;
        }

        return $this->create($model, $amount, $attributes);
    }

    /**
     * @param string $code
     * @throws VoucherIsInvalid
     * @throws VoucherExpired
     * @return Voucher
     */
    public function check(string $code)
    {
        $voucher = Voucher::whereCode($code)->first();

        if ($voucher === null) {
            throw VoucherIsInvalid::withCode($code);
        }
        if ($voucher->isExpired()) {
            throw VoucherExpired::create($voucher);
        }

        return $voucher;
    }

    /**
     * @return string
     */
    protected function getUniqueVoucher(): string
    {
        $voucher = $this->generator->generateUnique();

        while (Voucher::whereCode($voucher)->count() > 0) {
            $voucher = $this->generator->generateUnique();
        }

        return $voucher;
    }
}
