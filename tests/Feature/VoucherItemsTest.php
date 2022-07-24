<?php

use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Tests\Product;

uses()->group('voucher-items');

it('should add item to voucher', function () {
    $product = Product::factory()->create();

    $voucher = Vouchers::create();

    expect($voucher->products)->toHaveCount(0);
    expect($voucher->voucherItems)->toHaveCount(0);
    expect($voucher->items)->toHaveCount(0);

    $voucher->addItems($product);

    expect($voucher->products)->toHaveCount(1);
    expect($voucher->voucherItems)->toHaveCount(1);
    expect($voucher->items)->toHaveCount(1);
});

it('should set voucher items (override)', function () {
    [$product1, $product2] = Product::factory(2)->create();

    $voucher = Vouchers::create($product1);

    expect($voucher->products)->toHaveCount(1);
    expect($voucher->voucherItems)->toHaveCount(1);
    expect($voucher->items)->toHaveCount(1);
    expect($voucher->voucherItems->first()->is($product1))->toBeTrue();
    expect($voucher->voucherItems->first()->is($product2))->toBeFalse();

    $voucher->setItems($product2);

    expect($voucher->products)->toHaveCount(1);
    expect($voucher->voucherItems)->toHaveCount(1);
    expect($voucher->items)->toHaveCount(1);
    expect($voucher->voucherItems->first()->is($product1))->toBeFalse();
    expect($voucher->voucherItems->first()->is($product2))->toBeTrue();
});

it('should remove item from voucher', function () {
    $product = Product::factory()->create();

    $voucher = Vouchers::create($product);

    expect($voucher->products)->toHaveCount(1);
    expect($voucher->voucherItems)->toHaveCount(1);
    expect($voucher->items)->toHaveCount(1);

    $voucher->removeItems($product);

    expect($voucher->products)->toHaveCount(0);
    expect($voucher->voucherItems)->toHaveCount(0);
    expect($voucher->items)->toHaveCount(0);
});

it('expects setting items to not override', function () {
    [$product1, $product2] = Product::factory(2)->create();
    $voucher = Vouchers::create($product1);

    $voucher->addItems($product2);

    expect($voucher->products)->toHaveCount(2);
    expect($voucher->voucherItems)->toHaveCount(2);
    expect($voucher->items)->toHaveCount(2);
});
