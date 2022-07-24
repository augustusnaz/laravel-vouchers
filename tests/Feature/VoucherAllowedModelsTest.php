<?php

use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Tests\User;

uses()->group('voucher-allowed-models');

it('should add allowed users to discount code', function () {
    $voucher = Vouchers::create();
    $user = User::factory()->create();

    expect($voucher->allowdUsers)->toHaveCount(0);
    expect($voucher->isAllowed($user))->toBeTrue();

    $voucher->update(['can_redeem' => []]);
    $voucher->refresh();

    expect($voucher->isAllowed($user))->toBeFalse();

    $voucher->allow($user);
    $voucher->save();

    expect($voucher->allowdUsers)->toHaveCount(1);
    expect($voucher->allowdUsers->first()->is($user))->toBeTrue();
    expect($voucher->isAllowed($user))->toBeTrue();
});

it('should add disallowed (denied) users to discount code', function () {
    $voucher = Vouchers::create();
    $user = User::factory()->create();

    expect($voucher->disallowdUsers)->toHaveCount(0);
    expect($voucher->isDisallowed($user))->toBeFalse();

    $voucher->deny($user);
    $voucher->save();

    expect($voucher->disallowdUsers)->toHaveCount(1);
    expect($voucher->disallowdUsers->first()->is($user))->toBeTrue();
    expect($voucher->isDisallowed($user))->toBeTrue();
});

it('should add allowed and disallowed users to discount code', function () {
    $voucher = Vouchers::create();
    [$user1, $user2] = User::factory(2)->create();

    expect($voucher->allowdUsers)->toHaveCount(0);
    expect($voucher->disallowdUsers)->toHaveCount(0);

    $voucher->allow($user1);
    $voucher->deny($user2);
    $voucher->save();

    expect($voucher->allowdUsers)->toHaveCount(1);
    expect($voucher->disallowdUsers)->toHaveCount(1);

    expect($voucher->isAllowed($user1))->toBeTrue();
    expect($voucher->isAllowed($user2))->toBeFalse();

    expect($voucher->isDisallowed($user1))->toBeFalse();
    expect($voucher->isDisallowed($user2))->toBeTrue();
});

it('should allow once denied model', function () {
    $voucher = Vouchers::create();
    $user = User::factory()->create();

    expect($voucher->allowdUsers)->toHaveCount(0);
    expect($voucher->disallowdUsers)->toHaveCount(0);

    $voucher->deny($user);
    $voucher->save();

    expect($voucher->isAllowed($user))->toBeFalse();

    $voucher->allow($user);
    $voucher->save();

    expect($voucher->isAllowed($user))->toBeTrue();
});

it('should denied once allowed model', function () {
    $voucher = Vouchers::create();
    $user = User::factory()->create();

    expect($voucher->allowdUsers)->toHaveCount(0);
    expect($voucher->disallowdUsers)->toHaveCount(0);

    $voucher->allow($user);
    $voucher->save();

    expect($voucher->isAllowed($user))->toBeTrue();

    $voucher->deny($user);
    $voucher->save();

    expect($voucher->isAllowed($user))->toBeFalse();
});
