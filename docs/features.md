# Features

This package allows you to create vouchers and associate them with model instances. Vouchers are redeemable by any model with the `CanRedeemVouchers` trait. This means that a voucher would give *any* model access to *any* model with the `HasVouchers` trait.


* 🔅 Associate one voucher with one or more items
* ❤ Flexible Redeemer Models with polymorphic relationship to redeemers. Vouchers can be redeemed by any model. Useful for multi-auth or User/Guest architecture
* 📝 Multiple redeems
* 💪 Limit Access: can define model instances that are allowed or excluded from redeeming a Voucher
* Multiple limit scheme; exhaust redeems per instance, per user or per item
* Implements [moirei/**laravel-model-data**](https://github.com/augustusnaz/laravel-model-data)
