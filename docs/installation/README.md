# Installation


```bash
composer require moirei/laravel-vouchers
```

## Prepare the database

```php
php artisan vendor:publish --tag=vouchers-migrations
```

Then run the migrations

```bash
php artisan migrate
```

## Prepare Models

Ascribe the `MOIREI\Vouchers\Traits\HasVouchers` trait to your item model class that you want to be associated with vouchers.

```php
use Illuminate\Database\Eloquent\Model;
use MOIREI\Vouchers\Traits\HasVouchers;

class Product extends Model
{
    use HasVouchers;

    ...
}
```

Ascribe the `MOIREI\Vouchers\Traits\HasVouchers` trait to your redeemer model class(s) that you want to be able to redeem vouchers.

```php
use Illuminate\Database\Eloquent\Model;
use MOIREI\Vouchers\Traits\CanRedeemVouchers;

class User extends Model
{
    use CanRedeemVouchers;

    ...
}
```
