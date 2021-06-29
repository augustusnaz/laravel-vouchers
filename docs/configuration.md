# Configuration



## Publish the config



```bash
php artisan vendor:publish --tag=vouchers-config
```

The configuration file will be placed in `config/vouchers.php`

## Models

Confugure your relationship models. Users model is given as a base. Redeemers maybe of any type.

```php
// vouchers.php
...
'models' => [
    'users' => \App\Models\User::class,
    'products' => \App\Models\Ecommerce\Product::class,
    'vouchers' => \App\Models\Ecommerce\Voucher::class,
],
...
```

