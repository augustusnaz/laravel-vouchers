<?php

use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\VoucherScheme;

uses()->group('voucher-instance-quantity-usage');

beforeEach(function () {
    $this->voucher = Voucher::create([
        'limit_scheme' => VoucherScheme::INSTANCE,
        'quantity' => 100,
    ]);
});

it('should get quantity used', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    expect($voucher->getQuantityUsed())->toEqual(0);
});

it('should increment quantity used', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $voucher->incrementUse();

    expect($voucher->getQuantityUsed())->toEqual(1);
});

it('should increment quantity used with count', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $voucher->incrementUse(5);
    $voucher->incrementUse(2);

    expect($voucher->getQuantityUsed())->toEqual(7);
});

it('should increment and exhaust use', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    expect($voucher->isExhausted())->toBeFalse();

    $voucher->incrementUse(100);

    expect($voucher->isExhausted())->toBeTrue();
});
