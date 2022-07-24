<?php

return [

    /*
     * Voucher code configuration
     */
    'codes' => [
        /*
        * List of characters that will be used for voucher code generation.
        */
        'characters' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',

        /*
        * Voucher code prefix.
        *
        * Example: foo
        * Generated Code: foo-AGXF-1NH8
        */
        'prefix' => null,

        /*
        * Voucher code suffix.
        *
        * Example: foo
        * Generated Code: AGXF-1NH8-foo
        */
        'suffix' => null,

        /*
        * Code mask.
        * All asterisks will be removed by random characters.
        */
        'mask' => '****-****',

        /*
        * Separator to be used between prefix, code and suffix.
        */
        'separator' => '-',
    ],

    /*
     * The user model that belongs to vouchers.
     */
    'models' => [
        'vouchers' => \MOIREI\Vouchers\Models\Voucher::class,
        'users' => \App\Models\User::class,
        'products' => \App\Models\Product::class,
        'voucher_observer' => \MOIREI\Vouchers\Observers\VoucherObserver::class,
    ],

    'tables' => [
        /*
         * Database table name that will be used in migration
         */
        'vouchers' => 'vouchers',

        /*
         * Database pivot table name for vouchers and users relation
         */
        'redeemer_pivot_table' => 'redeemer_voucher',

        /*
         * Database pivot table name for vouchers and products relation
         */
        'item_pivot_table' => 'item_voucher',
    ],

    /*
     * Default reuse limit scheme
     */
    'default_limit_scheme' => \MOIREI\Vouchers\VoucherScheme::INSTANCE,
];
