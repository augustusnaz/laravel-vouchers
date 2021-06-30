# Relationships

Vouchers have polymorphic many-to-many relationship with redeemers and items.
Redeemers may be your user models while items may be your product models.

The polymorphic relationships means your voucher is not bound to any particular model type and therefore more flexible.

The Voucher class can be extended and configured with more relationship types.

## Redeemers
A default `users` relation has been included for retrieving `App\Model\User` models as defined in your config.

```php
// get users models
$voucher->users;

// get all
$voucher->redeemers;
```

## Items
A default `products` relation has been included for retrieving `App\Model\Product` models as defined in your config.

```php
// get products models
$voucher->products;

// get all items
$voucher->items;
```
