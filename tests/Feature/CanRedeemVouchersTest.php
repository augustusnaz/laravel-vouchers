<?php

use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Models\Voucher;
use MOIREI\Vouchers\Tests\User;

uses()->group('can-redeem-voucher');

it('should have vouchers', function () {
    $voucher = Vouchers::create();

    /** @var User */
    $user = User::factory()->create();

    expect($user->vouchers()->count())->toEqual(0);

    $user->vouchers()->attach($voucher, [
        'redeemed_at' => now()
    ]);

    expect($user->vouchers()->count())->toEqual(1);
});

it('should redeem vouchers', function () {
    $voucher = Vouchers::create();

    /** @var User */
    $user = User::factory()->create();

    expect($user->vouchers()->count())->toEqual(0);

    $user->redeem($voucher);

    expect($user->vouchers()->count())->toEqual(1);
});

it('should redeem owned vouchers', function () {
    $voucher = Voucher::create();

    /** @var User */
    [$user1, $user2] = User::factory(2)->create();

    $voucher->allow($user1);
    $voucher->save();

    expect($user1->canRedeem($voucher))->toBeTrue();
    expect($user2->canRedeem($voucher))->toBeFalse();

    $user1->redeem($voucher);

    expect($user1->vouchers()->count())->toEqual(1);
});

it('should fail if redeeming is not allowed', function () {
    $voucher = Vouchers::create();

    /** @var User */
    $user = User::factory()->create();

    $voucher->deny($user);
    $voucher->save();

    expect($user->canRedeem($voucher))->toBeFalse();

    $this->expectException(\MOIREI\Vouchers\Exceptions\CannotRedeemVoucher::class);

    $user->redeem($voucher);
});
