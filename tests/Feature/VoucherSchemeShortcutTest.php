<?php

use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\VoucherScheme;

uses()->group('voucher-sheme-shortcuts');

it('expects limitUsePer to set scheme', function () {
    /** @var Voucher */
    $voucher = Voucher::factory()->state(['limit_scheme' => VoucherScheme::INSTANCE])->create();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::INSTANCE);

    $voucher->limitUsePer(VoucherScheme::ITEM);

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::ITEM);
});

it('expects limitUsePer to set scheme and quantity', function () {
    /** @var Voucher */
    $voucher = Voucher::factory()->state(['limit_scheme' => VoucherScheme::INSTANCE])->create();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::INSTANCE);

    $voucher->limitUsePer(VoucherScheme::ITEM, 6);

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::ITEM);
    expect($voucher->quantity)->toEqual(6);
});

it('expects limitUsePerInstance to set scheme', function () {
    /** @var Voucher */
    $voucher = Voucher::factory()->state(['limit_scheme' => VoucherScheme::ITEM])->create();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::ITEM);

    $voucher->limitUsePerInstance();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::INSTANCE);
});

it('expects limitUsePerItem to set scheme', function () {
    /** @var Voucher */
    $voucher = Voucher::factory()->state(['limit_scheme' => VoucherScheme::INSTANCE])->create();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::INSTANCE);

    $voucher->limitUsePerItem();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::ITEM);
});

it('expects limitUsePerRedeemer to set scheme', function () {
    /** @var Voucher */
    $voucher = Voucher::factory()->state(['limit_scheme' => VoucherScheme::INSTANCE])->create();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::INSTANCE);

    $voucher->limitUsePerRedeemer();

    expect($voucher->limit_scheme)->toEqual(VoucherScheme::REDEEMER);
});
