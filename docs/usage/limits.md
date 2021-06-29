# Usage Limits


Reuse
Limit uses: quantity, product, users
expire, days

## Limit Quantity


### Reusable vouchers

```php
$product = Product::find(1);

$attributes = [
    'limit_scheme' => Voucher::LIMIT_ITEM, // options LIMIT_INSTANCE (default), LIMIT_ITEM, LIMIT_REDEEMER
    'quantity' => 2, // 2 uses
];

$vouchers = $product->createVouchers(2, $attributes);

$vouchers = $product->createVouchers(2)
    		->reuse() // reuse once
    		->save();

$vouchers = $product->createVouchers(2)
    		->reuse(2) // reuse twice
    		->save();
```

Using the facade;

```php
$vouchers = Vouchers::createReuse($product, 1, $attributes); // reuse once
$vouchers = Vouchers::createReuse($product, 1, $attributes, 2); // reuse twice
$vouchers = Vouchers::createReuse($product, 1, ['quantity' => 3]); // reuse twice
```

Limit schemes is used to specify whether the total quantity is reduced per instance, associated items or per redeemer.

| Name                     | Description                                                  |
| ------------------------ | ------------------------------------------------------------ |
| LIMIT_INSTANCE (default) | Redeems are accumulated on every invocation on the instance. |
| LIMIT_ITEM            | When one or more items are associated with the Voucher, number of redeems is counted per provided product instance. This means the Voucher may be exhausted for one product and not the other |
| LIMIT_REDEEMER           | Number of redeems is counted per redeemer. This means the voucher may be exhausted for one redeemer and not the other |



## Limit Redeemers

Making vouchers redeemable by any model type means it can be used with multi-auth users/guests/resellers type setup. If in any case you need to generate vouchers only redeemable by a certain user group/locale/etc., you can use the allow_models/deny_models attributes. Specifies who may or may not have the ability to redeem a voucher instance.

```php
$product = Product::find(1);
$user1 = User::find(1);
$user2 = User::find(2);
$guest1 = Guest::find(1);

// Create 5 vouchers associated to the product model.
$vouchers = Vouchers::create($product, 5);

// Create with model attributes
$attributes = [
    'allow_models' => [ $user1, $guest1 ],
    'deny_models' => [ $user2 ],
    'quantity' => 2,
    'limit_scheme' => Voucher::LIMIT_REDEEMER, // each redeemer may only redeem the voucher(s) twice
];
$vouchers = Vouchers::create($product, 5, $attributes);
```

The `allow_models` and `deny_models` values can also be passed as attributes to the `Vouchers` facade and the `Voucher` class.

These attributes can be passed even after the voucher is created. You can also use the `allow` and `deny` methods;

```php
$vouchers->allow([
             $user1,
         ])
    	 ->deny([
             $guest1,
         ]);
```

**Notes**

* The `allow_models` and `deny_models` attributes mentioned above are actually saved as `can_redeem` and `cannot_redeem` internally. They are mutated on boot creating or updating.


## Expiry

You can also create vouchers that will only be available until a certain date. Expired vouchers cannot be redeemed.
The `expires_at` attribute accepts a Carbon instance.

```php
$product = Product::find(1);

// Set in attributes
$voucher = Voucher::make([
    'expires_at' => today()->addDays(7),
])->setItems($product)->save();
// or
$product->createVouchers(2, [
    'expires_at' => today()->addDays(7),
]);
```

Or use the `days` method

```php
$voucher = Voucher::make()->days(7)->save();
```

Vouchers may be expired instantly with
```php
$voucher->expire()->save();
// or give it a date
$voucher->expire(now()->addHours(1))->save();
```


## Specify Products
The provided products are the only items the voucher maybe redeemed for.

```php
[$product1, $product2] = Product::all();

// Create a single Voucher model instance
$voucher = Voucher::make()->setItems([$product1, $product2])->save();

$user->redeem($voucher, $product1);
```
