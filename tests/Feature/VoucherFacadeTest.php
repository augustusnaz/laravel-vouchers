<?php

use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Tests\Product;

uses()->group('voucher-facade');

it('should create discount', function () {
    $voucher = Vouchers::create();
    expect($voucher)->toBeInstanceOf(Voucher::class);
});

it('should create discount for an item', function () {
    $product = Product::factory()->create();
    $voucher = Vouchers::create($product);

    expect($voucher->products)->toHaveCount(1);
    expect($voucher->products[0])->toBeInstanceOf(Product::class);
    expect($voucher->products[0]->is($product))->toBeTrue();

    expect($voucher->items)->toHaveCount(1);
    expect($voucher->items[0]->item)->toBeInstanceOf(Product::class);
    expect($voucher->items[0]->item->is($product))->toBeTrue();

    expect($voucher->voucherItems)->toHaveCount(1);
    expect($voucher->voucherItems[0])->toBeInstanceOf(Product::class);
    expect($voucher->voucherItems[0]->is($product))->toBeTrue();
});
