# Configuration



## Publish the config



```bash
php artisan vendor:publish --tag=vouchers-config
```

The configuration file will be placed in `config/vouchers.php`

## Models

Confugure your relationship models. `users` and `products` is given as the default redeemer and item types. The Voucher class may be extended to include other models.

```php
// vouchers.php
...
'models' => [
    'vouchers' => \App\Models\Ecommerce\Voucher::class,
    'users' => \App\Models\User::class,
    'products' => \App\Models\Ecommerce\Product::class,
],
...
```

