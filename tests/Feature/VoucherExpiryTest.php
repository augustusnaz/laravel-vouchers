<?php

use MOIREI\Vouchers\Models\Voucher;
use function Spatie\PestPluginTestTime\testTime;

uses()->group('voucher-expiry');

it('expects voucher to be active in the future', function () {
    /** @var Voucher */
    $voucher = Voucher::create([
        'active_date' => now()->addHour(),
    ]);

    expect($voucher->active)->toBeFalse();
    expect(Voucher::query()->active()->count())->toEqual(0);

    testTime()->addHour(2);

    expect($voucher->active)->toBeTrue();
    expect(Voucher::query()->active()->count())->toEqual(1);
});

it('expects voucher to be expired in the future', function () {
    /** @var Voucher */
    $voucher = Voucher::create([
        'expires_at' => now()->addHour(),
    ]);

    expect($voucher->expired)->toBeFalse();
    expect($voucher->active)->toBeTrue();
    expect(Voucher::query()->expired()->count())->toEqual(0);

    testTime()->addHour(2);

    expect($voucher->expired)->toBeTrue();
    expect($voucher->active)->toBeFalse();
    expect(Voucher::query()->expired()->count())->toEqual(1);
});

it('expects voucher to be active and expiry between dates', function () {
    /** @var Voucher */
    $voucher = Voucher::create([
        'active_date' => now()->addHours(1),
        'expires_at' => now()->addHours(5),
    ]);

    expect($voucher->active)->toBeFalse();
    expect($voucher->expired)->toBeFalse();
    expect(Voucher::query()->active()->count())->toEqual(0);
    expect(Voucher::query()->expired()->count())->toEqual(0);

    testTime()->addHour(2);

    expect($voucher->active)->toBeTrue();
    expect($voucher->expired)->toBeFalse();
    expect(Voucher::query()->active()->count())->toEqual(1);
    expect(Voucher::query()->expired()->count())->toEqual(0);

    testTime()->addHour(2);

    expect($voucher->active)->toBeTrue();
    expect($voucher->expired)->toBeFalse();
    expect(Voucher::query()->active()->count())->toEqual(1);
    expect(Voucher::query()->expired()->count())->toEqual(0);

    testTime()->addHour(1);

    expect($voucher->active)->toBeFalse();
    expect($voucher->expired)->toBeTrue();
    expect(Voucher::query()->active()->count())->toEqual(0);
    expect(Voucher::query()->expired()->count())->toEqual(1);
});
