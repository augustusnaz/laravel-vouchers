# Usage Limits

Voucher quantity (number of times it may be redeemed), associated items, expiry dates, allowd/denied uses, are optional parameters that may be used to further limit who or what can redeem a voucher.

## Limit quantity

### Reusable vouchers

```php
use MOIREI\Vouchers\VoucherScheme;

$product = Product::find(1);

$attributes = [
    'limit_scheme' => VoucherScheme::ITEM, // options INSTANCE (default), ITEM, REDEEMER
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

Using the Facade;

```php
$vouchers = Vouchers::createReuse($product, 1, $attributes); // reuse once
$vouchers = Vouchers::createReuse($product, 1, $attributes, 2); // reuse twice
$vouchers = Vouchers::createReuse($product, 1, ['quantity' => 3]); // reuse twice
```

Limit scheme is used to specify whether the total quantity is reduced per instance, associated items or per redeemer.

| Name                 | Description                                                  |
| -------------------- | ------------------------------------------------------------ |
| `INSTANCE` (default) | Redeems are accumulated on every invocation on the instance. |
| `ITEM`               | When one or more items are associated with the Voucher, number of redeems is counted per provided product instance. This means the Voucher may be exhausted for one product and not the other |
| `REDEEMER`           | Number of redeems is counted per redeemer. This means the voucher may be exhausted for one redeemer and not the other |

## Limit redeemers

Making vouchers redeemable by any model type means it can be used with multi-auth users/guests/resellers type setup. If in any case you need to generate vouchers only redeemable by a certain user group/locale/etc., you can use the allow_models/deny_models attributes. Ultimately specifies who may or may not have the ability to redeem a voucher instance.

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
    'limit_scheme' => VoucherScheme::REDEEMER, // each redeemer may only redeem the voucher(s) twice
];
$vouchers = Vouchers::create($product, 5, $attributes);
```

The `allow_models` and `deny_models` values can also be passed as attributes to the `Vouchers` facade and the `Voucher` class.

These attributes can be passed even after the voucher is created. You can also use the `allow` and `deny` methods;

```php
$vouchers->allow([$user1])
    	 ->allow($user2, ...)
    	 ->deny([$guest1]);
```

**Notes**

- The `allow_models` and `deny_models` attributes mentioned above are actually saved as `can_redeem` and `cannot_redeem` internally. They are intercepted on boot creating and updating.

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

To prune expired vouchers, call the `pruneExpired` method.

```php
// prune expired items until now
$voucher->pruneExpired();

// prune expired vouchers before yesterday
$voucher->pruneExpired(now()->yesterday());
```



## Active dates

Active dates make it possible to create vouchers ahead of time. An example usage is generate vouchers for seasonal sales. 

```php
$product->createVouchers(2, [
    'active_date' => today()->addDays(7), // christmas sale
]);
```

Can used with `expires_at` to create vouchers with limited use window.



## Limit by specific items

The provided products below are the only items the voucher maybe redeemed against.

```php
[$product1, $product2] = Product::all();

// Create a single Voucher model instance
$voucher = Voucher::make()->setItems([$product1, $product2])->save();

$user->redeem($voucher, $product1);
```
