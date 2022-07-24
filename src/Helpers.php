<?php

namespace MOIREI\Vouchers;

use Illuminate\Database\Eloquent\Model;

class Helpers
{
    /**
     * If all items in array are of value
     *
     * @param array $array
     * @param mixed $value
     * @return bool
     */
    public static function isAll(array $array, mixed $value): bool
    {
        if (!count($array)) return false;

        if ($array[0] !== $value) return false;

        return count(array_unique($array)) == 1;
    }

    /**
     * List of true/false indicating if models exist in heystack
     *
     * @param Model[] $models
     * @param Model[] $models
     * @return bool[]
     */
    public static function in($models, $heystack): array
    {
        $list = [];
        foreach ($models as $model) {
            $added = false;
            foreach ($heystack as $user) {
                if ($user->is($model)) {
                    $added = true;
                    $list[] = $user->is($model);
                    break;
                }
            }
            if (!$added) {
                $list[] = false;
            }
        }
        return $list;
    }

    /**
     * If any models exists in heystack
     *
     * @param Model[] $models
     * @param Model[] $models
     * @return bool
     */
    public static function anyIn($models, $heystack): bool
    {
        $anyExists = false;
        foreach ($models as $model) {
            foreach ($heystack as $m) {
                $anyExists = $m->is($model);
                if ($anyExists) break;
            }
            if ($anyExists) break;
        }

        return $anyExists;
    }

    /**
     * Get Voucher model key representation
     * @param Model $model
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public static function getModelKey(Model $item, string $prefix = '', string $suffix = ''): string
    {
        return $prefix . $item->getMorphClass() . ':' . $item->getKey() . $suffix;
    }
}
