<?php

use MOIREI\Vouchers\Helpers;

uses()->group('helpers-Helpersay-isall');

it('expects all Helpersay items to be true [1]', function () {
    expect(Helpers::isAll([true, true, true], true))->toBeTrue();
});

it('expects all Helpersay items to be true [2]', function () {
    expect(Helpers::isAll([false, false, false], false))->toBeTrue();
});

it('expects all Helpersay items to be true [3]', function () {
    expect(Helpers::isAll([2, 2, 2], 2))->toBeTrue();
});

it('expects mixed Helpersay items to be false [1]', function () {
    expect(Helpers::isAll([false, true, false], true))->toBeFalse();
});

it('expects mixed Helpersay items to be false [2]', function () {
    expect(Helpers::isAll([false, true, false], false))->toBeFalse();
});

it('expects mixed Helpersay items to be false [3]', function () {
    expect(Helpers::isAll([2, 4, 2], 2))->toBeFalse();
});

it('expects mixed Helpersay items to be false [4]', function () {
    expect(Helpers::isAll([2, 2, 2], 1))->toBeFalse();
});
