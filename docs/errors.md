# Handling Errors

The `redeem` and `redeemVoucher` methods throw a couple of exceptions that you should catch and react to in your application:

### Voucher invalid

If a user tries to redeem an invalid code, the package will throw the following exception: `MOIREI\Vouchers\Exceptions\VoucherIsInvalid`.

### Voucher already redeemed or exhausted

All generated vouchers can be set for reuse. If a user tries to redeem a disposable voucher for the second time, or another user already redeemed this voucher, `MOIREI\Vouchers\Exceptions\VoucherAlreadyRedeemed` is thrown. If the reuse quantity has been exhausted, `MOIREI\Vouchers\Exceptions\VoucherRedeemsExhausted` is thrown.

### Not allowed

If model instances have been specifically allowed or denied ability to redeem voucher, `MOIREI\Vouchers\Exceptions\CannotRedeemVoucher` will be thrown accordingly.

### Voucher expired

If a user tries to redeem an expired voucher code, `MOIREI\Vouchers\Exceptions\VoucherExpired` is thrown.