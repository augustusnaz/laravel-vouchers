<?php

use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Tests\Product;

uses()->group('voucher-products');

it('should add product to voucher', function () {
    $product = Product::factory()->create();

    $voucher = Vouchers::create();

    expect($voucher->products)->toHaveCount(0);

    $voucher->addProducts($product);

    expect($voucher->products)->toHaveCount(1);
});

it('should set voucher products (override)', function () {
    [$product1, $product2] = Product::factory(2)->create();

    $voucher = Vouchers::create($product1);

    expect($voucher->products)->toHaveCount(1);
    expect($voucher->products->first()->is($product1))->toBeTrue();
    expect($voucher->products->first()->is($product2))->toBeFalse();

    $voucher->setProducts($product2);

    expect($voucher->products)->toHaveCount(1);
    expect($voucher->products->first()->is($product1))->toBeFalse();
    expect($voucher->products->first()->is($product2))->toBeTrue();
});

it('should remove product from voucher', function () {
    $product = Product::factory()->create();

    $voucher = Vouchers::create($product);

    expect($voucher->products)->toHaveCount(1);

    $voucher->removeProducts($product);

    expect($voucher->products)->toHaveCount(0);
});

it('expects setting products to not override', function () {
    [$product1, $product2] = Product::factory(2)->create();
    $voucher = Vouchers::create($product1);

    $voucher->addProducts($product2);

    expect($voucher->products)->toHaveCount(2);
});
