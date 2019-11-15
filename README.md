# Laravel Vouchers



An ecommerce voucher generator. It associates vouchers with any eloquent model and allows multiple models to own/redeem vouchers.

This package is based on [beyondcode / **laravel-vouchers**](https://github.com/beyondcode/laravel-vouchers) and [zgabievi / **laravel-promocodes**](https://github.com/zgabievi/laravel-promocodes). Check them out, you might find them more appropriate for your application.



## Unique Features

* Can reuse Voucher
* Polymorphic attachment to models i.e. Vouchers can be redeemed by any model. Useful for multi-auth or User/Guest architecture
* Can specify allowed model instances that can redeem a Voucher
* Can exclude model instances from redeeming a Voucher
* Can store Voucher value as %, monetary (workable with multi-currency), or as other
* Implements [moirei/**laravel-model-data**](https://github.com/augustusnaz/laravel-model-data)



Example usage:

```php
$product = Product::find(1);
$voucher = $product->createVoucher();

$user->redeemVoucher($voucher);
```



## Installation

You can install the package via composer:

```bash
composer require moirei/laravel-vouchers
```

Install the service provider (skip for Laravel>=5.5);

```
// config/app.php
'providers' => [
    ...
    MOIREI\Vouchers\VouchersServiceProvider::class,
],
```

Next publish the migration with

```bash
php artisan vendor:publish --provider=MOIREI\Vouchers\VouchersServiceProvider --tag="migrations"
```

Then run the migrations:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider=MOIREI\Vouchers\VouchersServiceProvider --tag="config"
```



## Usage

This package allows you to create vouchers and associate them with model instances. Vouchers are redeemable by any model with the `CanRedeemVouchers` trait. This means that a voucher would give *any* model access to *any* model with the `HasVouchers` trait.

To setup, add `MOIREI\Vouchers\Traits\HasVouchers` trait to all your Eloquent model class that you want to be associated with vouchers. Then, add the `MOIREI\Vouchers\Traits\CanRedeemVouchers` trait to the model class that you want to be able to redeem vouchers.



## Creating Vouchers

### Using the Facade

You can create one or multiple vouchers by using the `Vouchers` facade:

```php
$product = Product::find(1);
$user1 = User::find(1);
$user2 = User::find(2);
$guest1 = Guest::find(1);

// Create 5 vouchers associated to the product model.
$vouchers = Vouchers::create($product, 5);

// Create with model attributes
$attributes = [
    'expires_at' => today()->addDays(7),
    'reward_type' => Voucher::TYPE_PERCENTAGE,
    'is_disposable' => false, // all generated vouchers are by default disposable
    'quantity' => 2, // ignored if disposable
    'allow_models' => [ $user1, $guest1 ],
    'deny_models' => [ $user2 ],
];
$vouchers = Vouchers::create($product, 5, $attributes);


```

The return value is an array containing all generated `Voucher` models. 

The Voucher model has a property `code` which contains the generated voucher code.

### Using the Voucher class

In addition, you can also create vouchers by using the `create` method on the Voucher model:

```php
$product = Product::find(1);

// Create a single Voucher model instance
$voucher = Voucher::create([
  	'model_id' => $product->getKey(), // required in this case
    'model_type' => $product->getMorphClass(), // required in this case
    'expires_at' => today()->addDays(7),
]);
```

### Using an Eloquent model

Yet again, you can create vouchers by using the `createVouchers` method on the associated model:

```php
$product = Product::find(1);

// Returns an array of Vouchers
$vouchers = $product->createVouchers(2);

// Returns a single Voucher model instance
$voucher = $product->createVoucher();
```

### Reusable vouchers

All vouchers are by default disposable. To make reusable, set the quantity and set the `is_disposable` attribute to `false`, or use the `reuse()` method.

```php
$product = Product::find(1);

$vouchers = $product->createVouchers(2, [
    'is_disposable' => false,
    'quantity' => 2, // reuse once
]);

$vouchers = $product->createVouchers(2)
    		->reuse() // reuse once
    		->save();

$vouchers = $product->createVouchers(2)
    		->reuse(2) // reuse twice
    		->save();
```

Using the facade;

```php
$vouchers = Vouchers::createReuse($product, 1, $attribute); // reuse once
$vouchers = Vouchers::createReuse($product, 1, $attribute, 2); // reuse twice
$vouchers = Vouchers::createReuse($product, 1, ['quantity' => 3]); // reuse twice
```

### Additional data

This package uses `moirei/laravel-model-data` to associate arbitrary data with your vouchers. 

You can read/write data with

```php
$product = Product::find(1);

$vouchers = $product->createVouchers(2, [
    'data' => [
        'note' => 'Special discount',
    ],
]);

$voucher = $user->redeem('ABC-DEF');
$from = $voucher->data->get('note');
```

Similarly,

```php
$vouchers = $product->createVouchers(2)
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

### Vouchers with expiry dates

You can also create vouchers that will only be available until a certain date. Expired vouchers cannot be redeemed.
The `expires_at` attribute accepts a Carbon instance.

```php
$product = Product::find(1);

$product->createVouchers(2, [
    'expires_at' => today()->addDays(7),
]);
```

Or

```php
$product->createVouchers(2)
    	->days(7)
    	->save();
```

### Allowing or denying model instances

Making vouchers redeemable by any model type means it can be used with multi-auth users/guests/resellers type setup. If in any case you need to generate vouchers only redeemable by a certain user group/locale/etc., you can use the allow_models/deny_models attributes. 

```php
$vouchers = Vouchers::create($product, 5, [
    'currency_code' => 'AUD',
    'reward_type' => Voucher::TYPE_MONETARY,
    'reward' => 10,
    'allow_models' => [ $user1, $guest1 ],
    'deny_models' => [ $user2 ],
]);
```

The `allow_models` and `deny_models` values can also be passed as attributes to the `Vouchers` facade and the `Voucher` class.

These attributes can be passed even after the voucher is created. You can also chain them;

```php
$vouchers->currency('AUD')
    	 ->rewardType(Voucher::TYPE_OTHER)
    	 ->rewardValue(10)
    	 ->allow([
             $user1,
         ])	
    	 ->deny([
             $guest1,
         ]);
```



## Redeeming Vouchers

To redeem a voucher by code, use the `redeem` method. Unfortunately this method does not have any magic:

```php
$voucher = $user->redeem('ABCD-EFGH');
```

If the voucher is valid, the method will return the voucher model associated with this code.

To redeem an existing Voucher model, you can use the `redeemVoucher` method:

```php
$user->redeemVoucher($voucher);
```

After a voucher is successfully redeemed, this package will fire a `MOIREI\Vouchers\Events\VoucherRedeemed` event. The event contains the redeemer instance and the voucher instance.

### Accessing the associated model

The `Voucher` model has a `model` relation that points to the associated Eloquent model:

```php
$voucher = $user->redeem('ABCD-EFGH');

$product = $voucher->model;
```

### Accessing redeemers

To retrieve the models that have redeemed a voucher, use

```php
$redeemers = $voucher->redeemers;
```

This returns a Collection with mixed model types. The collection items are not Eloquent relationships. To access the underlying pivot relationship, use `related_redeemers`. This also returns a Collection.



## Handling Errors

The `redeem` and `redeemVoucher` methods throw a couple of exceptions that you should catch and react to in your application:

### Voucher invalid

If a user tries to redeem an invalid code, the package will throw the following exception: `MOIREI\Vouchers\Exceptions\VoucherIsInvalid`.

### Voucher already redeemed or exhausted

All generated vouchers can be set for reuse. If a user tries to redeem a disposable voucher for the second time, or another user already redeemed this voucher, `MOIREI\Vouchers\Exceptions\VoucherAlreadyRedeemed` is thrown. If the reuse quantity has been exhausted, `MOIREI\Vouchers\Exceptions\VoucherRedeemsExhausted` is thrown.

### Not allowed

If model instances have been specifically allowed or denied ability to redeem voucher, `MOIREI\Vouchers\Exceptions\CannotRedeemVoucher` will be thrown accordingly.

### Voucher expired

If a user tries to redeem an expired voucher code, `MOIREI\Vouchers\Exceptions\VoucherExpired` is thrown.



## Notes

* The `allow_models` and `deny_models` attributes mentioned above are actually saved as `can_redeem` and `cannot_redeem` internally. They are mutated on boot creating or updating.
* If you manage your resources with Nova, [Nova Multiselect]( https://novapackages.com/packages/optimistdigital/nova-multiselect-field ) can be used directly with the `can_redeem` and `cannot_redeem` attributes. Example code [here](doc/nova-resource-example.md).



## Credits

- [Augustus Okoye](https://github.com/augustusnaz)
- [beyondcode / **laravel-vouchers**](https://github.com/beyondcode/laravel-vouchers)
- [zgabievi / **laravel-promocodes**](https://github.com/zgabievi/laravel-promocodes)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
