<?php

use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Tests\Product;
use MOIREI\Vouchers\Tests\User;
use MOIREI\Vouchers\VoucherScheme;

uses()->group('voucher-item-quantity-usage');

beforeEach(function () {
    /** @var Voucher */
    $voucher = Voucher::create([
        'limit_scheme' => VoucherScheme::ITEM,
        'quantity' => 100,
    ]);
    $this->product = Product::factory()->create();

    $voucher->setItems($this->product);

    $this->voucher = $voucher->fresh();
});

it('should get quantity used', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    expect($voucher->getQuantityUsed())->toEqual(0);
    expect($voucher->getQuantityUsed($this->product))->toEqual(0);
});

it('should increment quantity used', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $voucher->incrementModelUse($this->product);

    expect($voucher->getQuantityUsed())->toEqual(1);
    expect($voucher->getQuantityUsed($this->product))->toEqual(1);
});

it('should not increment quantity used for unknown item', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $product = Product::factory()->create();

    $voucher->incrementModelUse($this->product);
    $voucher->incrementModelUse($product);

    expect($voucher->getQuantityUsed())->toEqual(1);
    expect($voucher->getQuantityUsed($this->product))->toEqual(1);
    expect($voucher->getQuantityUsed($product))->toEqual(0);
});

it('should increment quantity used with count', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $voucher->incrementModelUse($this->product, 5);
    $voucher->incrementModelUse($this->product, 2);

    expect($voucher->getQuantityUsed())->toEqual(7);
    expect($voucher->getQuantityUsed($this->product))->toEqual(7);
});

it('should increment and exhaust use', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $product = Product::factory()->create();

    expect($voucher->isExhausted())->toBeFalse();

    $voucher->incrementModelUse($this->product, 100);

    expect($voucher->isExhausted())->toBeFalse();
    expect($voucher->isExhausted($this->product))->toBeTrue();
    expect($voucher->isExhausted($product))->toBeFalse();
});

it('should be redeemed', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    /** @var User */
    $user = User::factory()->create();

    /** @var Product */
    $product1 = $this->product;
    /** @var Product */
    $product2 = Product::factory()->create();
    /** @var Product */
    $product3 = Product::factory()->create();

    $voucher->update(['quantity' => 1]);
    $voucher->addItems($product2);
    $voucher->save();

    expect($voucher->isRedeemed())->toBeFalse();

    $user->redeem($voucher, $product1);
    $voucher->refresh();

    expect($voucher->isRedeemed())->toBeFalse();

    expect($voucher->isRedeemed($product1))->toBeTrue();
    expect($voucher->isRedeemed($product2))->toBeFalse();
    expect($voucher->isRedeemed($product3))->toBeFalse();

    $user->redeem($voucher, $product2);
    $voucher->refresh();

    expect($voucher->isRedeemed($product1))->toBeTrue();
    expect($voucher->isRedeemed($product2))->toBeTrue();
    expect($voucher->isRedeemed($product3))->toBeFalse();
});
