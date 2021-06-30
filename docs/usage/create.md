# Create Voucher

## Using the Facade

You can create one or multiple vouchers by using the `Vouchers` facade:

```php
[$product1, $product2] = Product::take(2)->get();

// Create 5 vouchers associated to the product model.
$vouchers = Vouchers::create($product, 5);

// Create with model attributes
$attributes = [
    'expires_at' => today()->addDays(7),
    'quantity' => 2,
    'value' => 10, // optional value e.g. 10% off
];
// for multiple items
$vouchers = Vouchers::create([$product, $product2], 5, $attributes);
```

The return value is an array containing all generated `Voucher` models.

## Using the Voucher class

In addition, you can also create vouchers by using the `create` method on the Voucher model:

```php
// Create a single Voucher model instance
$voucher = Voucher::make($attributes)->setItems($product)->save();
// for multiple items
$voucher = Voucher::make($attributes)->setItems([$product, $product2])->save();
```

## Using an Item model

Yet again, you can create vouchers by using the `createVouchers` method on an item model:

```php
// Returns an array of Vouchers
$vouchers = $product->createVouchers(2);

// Returns a single Voucher model instance
$voucher = $product->createVoucher();
```

