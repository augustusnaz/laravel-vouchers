# Redeem Vouchers

To redeem a voucher by code, use the `redeem` method. Unfortunately this method does not have any magic:

```php
$voucher = $user->redeem('ABCD-EFGH');
// or
$voucher = Voucher::find(1);
$user->redeem($voucher);
```

If the voucher is valid, the method will return the voucher model associated with this code.

After a voucher is successfully redeemed, this package will fire a `MOIREI\Vouchers\Events\VoucherRedeemed` event. The event contains the redeemer instance and the voucher instance.

To redeem a voucher with multiple items, provide an item to redeem
```php
[$product1, $product2] = Product::take(2)->get();

[$voucher] = Vouchers::create([$product1, $product2], 1, [
    'limit_scheme' => Voucher::LIMIT_ITEM,
]);
// or
$voucher = Voucher::make()
          ->setItems([$product1, $product2])
          ->limitUsePerItem()
          ->save();

// redeem for a perticular product
$user->redeem($voucher, $product1);
```