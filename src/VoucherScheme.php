<?php

namespace MOIREI\Vouchers;

enum VoucherScheme: string
{
    /**
     * Apply voucher limits per voucher
     */
    case INSTANCE = 'limit-per-instance';

    /**
     * Apply voucher limits per target item
     */
    case ITEM = 'limit-per-item';

    /**
     * Apply voucher limits per redeemer
     */
    case REDEEMER = 'limit-per-redeemer';

    /**
     * Check if enum is equal to another enum or value.
     *
     * @param self|string $value
     * @return bool
     */
    public function is($value): bool
    {
        return $this->value === (is_object($value) ? $value->value : $value);
    }

    /**
     * Get all enum values
     *
     * @return array
     */
    public static function values(): array
    {
        return array_map(
            fn ($enum) => $enum->value,
            self::cases()
        );
    }
}
