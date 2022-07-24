# Creating Vouchers

The `Vouchers` facades provides a set of utility functions for quickly creating vouchers.

```php
use MOIREI\Vouchers\Facades\Vouchers;

$voucher = Vouchers::create();
$vouchers = Vouchers::createMany();
```

You can also create a voucher via the Voucher model.

```php
use MOIREI\Vouchers\Models\Voucher;

$voucher = Voucher::create();
```



## Create vouchers with items

You can create one or multiple vouchers and assign them to any number of models.

```php
[$product1, $product2] = Product::take(2)->get();

// Create 5 vouchers associated to the product model.
$vouchers = Vouchers::createMany(5, $product1);

// Create with model attributes
$attributes = [
    'expires_at' => today()->addDays(7),
    'quantity' => 2,
    'value' => 10, // optional value e.g. 10% off
];
// for multiple items
$vouchers = Vouchers::createMany(5, [$product1, $product2], $attributes);
```

The return value is an array containing all generated `Voucher` models.

## Create via item model

Yet again, you can create vouchers by using the `createVoucher` and `createVouchers` methods on an item model:

```php
// Returns an array of Vouchers
$vouchers = $product->createVouchers(2);

// Returns a single Voucher model instance
$voucher = $product->createVoucher();
```

