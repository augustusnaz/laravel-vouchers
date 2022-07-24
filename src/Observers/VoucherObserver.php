<?php

namespace MOIREI\Vouchers\Observers;

use MOIREI\Vouchers\Facades\Vouchers;
use MOIREI\Vouchers\Models\Voucher;

class VoucherObserver
{
    /**
     * @param Voucher $voucher
     */
    public function creating(Voucher $voucher)
    {
        if (!$voucher->code) {
            $voucher->code = Vouchers::generateUniqueCode();
        }

        if (isset($voucher->allow_models)) {
            if (is_array($voucher->allow_models)) {
                $voucher->allow($voucher->allow_models);
            }
            unset($voucher->allow_models);
        }

        if (isset($voucher->deny_models)) {
            if (is_array($voucher->deny_models)) {
                $voucher->deny($voucher->deny_models);
            }
            unset($voucher->deny_models);
        }
    }

    /**
     * @param Voucher $voucher
     */
    public function created(Voucher $voucher)
    {
        $voucher->flushQueuedItems();
    }

    /**
     * @param Voucher $voucher
     */
    public function updating(Voucher $voucher)
    {
        if (isset($voucher->allow_models)) {
            if (is_array($voucher->allow_models)) {
                $voucher->allow($voucher->allow_models);
            }
            unset($voucher->allow_models);
        }

        if (isset($voucher->deny_models)) {
            if (is_array($voucher->deny_models)) {
                $voucher->deny($voucher->deny_models);
            }
            unset($voucher->deny_models);
        }
    }
}
