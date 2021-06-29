# Create Voucher

## Using the Facade

You can create one or multiple vouchers by using the `Vouchers` facade:

```php
$product = Product::find(1);

// Create 5 vouchers associated to the product model.
$vouchers = Vouchers::create($product, 5);

// Create with model attributes
$attributes = [
    'expires_at' => today()->addDays(7),
    'quantity' => 2,
];
// for multiple items (products)
$vouchers = Vouchers::create([$product, $product2], 5, $attributes);
```

The return value is an array containing all generated `Voucher` models.

The Voucher model has a property `code` which contains the generated voucher code.

## Using the Voucher class

In addition, you can also create vouchers by using the `create` method on the Voucher model:

```php
$product = Product::find(1);

// Create a single Voucher model instance
$voucher = Voucher::make($attributes)->setItems($product)->save();
// for multiple items (products)
$voucher = Voucher::make($attributes)->setItems([$product, $product2])->save();
```

## Using the product model

Yet again, you can create vouchers by using the `createVouchers` method on a product model:

```php
$product = Product::find(1);

// Returns an array of Vouchers
$vouchers = $product->createVouchers(2);

// Returns a single Voucher model instance
$voucher = $product->createVoucher();
```

