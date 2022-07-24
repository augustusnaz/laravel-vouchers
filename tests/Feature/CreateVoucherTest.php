<?php

use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\VoucherScheme;

uses()->group('create-voucher');

it('should create discount code', function () {
    /** @var Voucher */
    $voucher = Vouchers::create();
    expect($voucher)->toBeInstanceOf(Voucher::class);
});

it('should create discount code with default config scheme', function () {
    $scheme = VoucherScheme::ITEM;
    config(['vouchers.default_limit_scheme' => $scheme]);

    /** @var Voucher */
    $voucher = Vouchers::create();

    expect($voucher->limit_scheme)->toEqual($scheme);
});

it('should create discount codes with different schemes', function () {

    Voucher::factory(2)->state(['limit_scheme' => VoucherScheme::INSTANCE])->create();
    Voucher::factory(3)->state(['limit_scheme' => VoucherScheme::ITEM])->create();
    Voucher::factory(4)->state(['limit_scheme' => VoucherScheme::REDEEMER])->create();

    expect(Voucher::count())->toEqual(9);
    expect(Voucher::ofScheme(VoucherScheme::INSTANCE)->count())->toEqual(2);
    expect(Voucher::instanceScheme()->count())->toEqual(2);
    expect(Voucher::ofScheme(VoucherScheme::ITEM)->count())->toEqual(3);
    expect(Voucher::itemScheme()->count())->toEqual(3);
    expect(Voucher::ofScheme(VoucherScheme::REDEEMER)->count())->toEqual(4);
    expect(Voucher::redeemerScheme()->count())->toEqual(4);
});
