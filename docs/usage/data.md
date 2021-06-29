# Additional Data
This package uses `moirei/laravel-model-data` to associate arbitrary data with your vouchers.

You can read/write data with

```php
$product = Product::find(1);

[$voucher, $voucher2] = $product->createVouchers(2, [
    'data' => [
        'note' => 'Special discount',
    ],
]);

$voucher = $user->redeem('ABC-DEF');
$from = $voucher->data->get('note');
```

Similarly,

```php
[$voucher, $voucher2] = $product->createVouchers(2)
    		->days(7) // expiry days
    		->data([
                'message' => [
                    'from' => 'MOIREI',
                ]
            ])
    		->save();

$voucher = $user->redeem('ABC-DEF');
$from = $voucher->data('message.from');
```

See [augustusnaz/laravel-model-data](https://github.com/augustusnaz/laravel-model-data) for full documentation.
