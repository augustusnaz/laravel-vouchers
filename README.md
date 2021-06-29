# Laravel Vouchers

An ecommerce voucher generator. It associates vouchers with one or more eloquent model and allows multiple models to own/redeem vouchers.
## Documentation

All documentation is available at [the documentation site](https://augustusnaz.github.io/laravel-vouchers).


## :green_heart: Unique Features

* 🔅 Associate one voucher with one or more items
* ❤ Flexible Redeemer Models with polymorphic relationship to redeemers. Vouchers can be redeemed by any model. Useful for multi-auth or User/Guest architecture
* 📝 Use vouchers once or multiple times
* 💪 Limit Access: can define model instances that are allowed or excluded from redeeming a Voucher
* Multiple limit scheme; exhaust redeems per instance, per user or per product
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

Next publish the migration with

```bash
php artisan vendor:publish --tag="vouchers-migrations"
```

Then run the migrations:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="vouchers-config"
```

## Notes

* The `allow_models` and `deny_models` attributes mentioned above are actually saved as `can_redeem` and `cannot_redeem` internally. They are mutated on boot creating or updating.
* If you manage your resources with Nova, [Nova Multiselect]( https://novapackages.com/packages/optimistdigital/nova-multiselect-field ) can be used directly with the `can_redeem` and `cannot_redeem` attributes. Example code [here]([doc/nova-resource-example.md](https://augustusnaz.github.io/installation/nova-example)).



## Credits

- [Augustus Okoye](https://github.com/augustusnaz)
- [beyondcode/**laravel-vouchers**](https://github.com/beyondcode/laravel-vouchers)
- [zgabievi/**laravel-promocodes**](https://github.com/zgabievi/laravel-promocodes)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
