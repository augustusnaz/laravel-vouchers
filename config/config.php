<?php

return [

    /*
     * Database table name that will be used in migration
     */
    'table' => 'vouchers',

    /*
     * Database pivot table name for vouchers and users relation
     */
    'relation_table' => 'user_voucher',

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

    /*
     * The user model that belongs to vouchers.
     */
    'user_model' => \App\Models\User::class,

    /*
     * The a custom model (other than user) that belongs to vouchers.
     */
    'custom_model' => \App\Models\Guest::class,

    /*
     * Default currency
     */
    'default_currency' => 'USD',

    /*
     * Default reward type
     */
    'default_reward_type' => \MOIREI\Vouchers\Models\Voucher::TYPE_PERCENTAGE,
];