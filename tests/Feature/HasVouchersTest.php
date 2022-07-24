<?php

use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Tests\Product;

uses()->group('has-voucher');

it('should have vouchers', function () {
    /** @var Voucher */
    $voucher = Voucher::factory()->create();

    /** @var Product */
    $product = Product::factory()->create();

    expect($product->vouchers()->count())->toEqual(0);

    $product->vouchers()->attach($voucher);

    expect($product->vouchers()->count())->toEqual(1);
});

it('should create voucher for model', function () {
    /** @var Product */
    $product = Product::factory()->create();

    $voucher = $product->createVoucher();

    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($product->vouchers()->count())->toEqual(1);
    expect($voucher->products()->count())->toEqual(1);
});

it('should create multiple vouchers for model', function () {
    /** @var Product */
    $product = Product::factory()->create();

    $vouchers = $product->createVouchers(2);

    expect($vouchers)->toHaveCount(2);
    expect($product->vouchers()->count())->toEqual(2);
});

it('expects owner product to be voucher item', function () {
    /** @var Product $product1 */
    [$product1, $product2] = Product::factory(2)->create();

    $voucher = $product1->createVoucher();

    expect($voucher->isItem($product1))->toBeTrue();
    expect($voucher->isItem($product2->id))->toBeFalse();
});
