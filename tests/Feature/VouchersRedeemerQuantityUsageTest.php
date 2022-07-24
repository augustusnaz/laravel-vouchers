<?php

use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Tests\User;
use MOIREI\Vouchers\VoucherScheme;

uses()->group('voucher-redeemer-quantity-usage');

beforeEach(function () {
    /** @var Voucher */
    $voucher = Voucher::create([
        'limit_scheme' => VoucherScheme::REDEEMER,
        'quantity' => 100,
    ]);
    $this->user = User::factory()->create();

    $voucher->allow($this->user);
    $voucher->save();

    $this->voucher = $voucher->fresh();
});

it('should get quantity used', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    expect($voucher->getQuantityUsed())->toEqual(0);
    expect($voucher->getQuantityUsed($this->user))->toEqual(0);
});

it('should increment quantity used', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $voucher->incrementModelUse($this->user);

    expect($voucher->getQuantityUsed())->toEqual(1);
    expect($voucher->getQuantityUsed($this->user))->toEqual(1);
});

it('should not increment quantity used for unknown item', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $user = User::factory()->create();

    $voucher->incrementModelUse($this->user);
    $voucher->incrementModelUse($user);

    expect($voucher->getQuantityUsed())->toEqual(1);
    expect($voucher->getQuantityUsed($this->user))->toEqual(1);
    expect($voucher->getQuantityUsed($user))->toEqual(0);
});

it('should increment quantity used with count', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $voucher->incrementModelUse($this->user, 5);
    $voucher->incrementModelUse($this->user, 2);

    expect($voucher->getQuantityUsed())->toEqual(7);
    expect($voucher->getQuantityUsed($this->user))->toEqual(7);
});

it('should increment and exhaust use', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    $user = User::factory()->create();

    expect($voucher->isExhausted())->toBeFalse();

    $voucher->incrementModelUse($this->user, 100);

    expect($voucher->isExhausted())->toBeFalse();
    expect($voucher->isExhausted($this->user))->toBeTrue();
    expect($voucher->isExhausted($user))->toBeFalse();
});

it('should be redeemed', function () {
    /** @var Voucher */
    $voucher = $this->voucher;

    /** @var User */
    $user1 = $this->user;
    /** @var User */
    $user2 = User::factory()->create();
    /** @var User */
    $user3 = User::factory()->create();

    $voucher->update(['quantity' => 1]);
    $voucher->allow($user2);
    $voucher->save();

    expect($voucher->isRedeemed())->toBeFalse();

    $user1->redeem($voucher);
    $voucher->refresh();

    expect($voucher->isRedeemed())->toBeFalse();

    expect($voucher->isRedeemed($user1))->toBeTrue();
    expect($voucher->isRedeemed($user2))->toBeFalse();
    expect($voucher->isRedeemed($user3))->toBeFalse();

    $user2->redeem($voucher);
    $voucher->refresh();

    expect($voucher->isRedeemed($user1))->toBeTrue();
    expect($voucher->isRedeemed($user2))->toBeTrue();
    expect($voucher->isRedeemed($user3))->toBeFalse();
});
