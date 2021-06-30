<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use MOIREI\Vouchers\Models\Voucher;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $voucherTable = config('vouchers.tables.vouchers', 'vouchers');
        $redeemerPivotTable = config('vouchers.tables.redeemer_pivot_table', 'redeemer_voucher');
        $itemPivotTable = config('vouchers.tables.item_pivot_table', 'item_voucher');

        Schema::create($voucherTable, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 32)->unique();
            $table->integer('quantity')->nullable()->default(1);
            $table->json('quantity_used')->nullable();
            $table->string('limit_scheme', 24)->default(config('vouchers.default_limit_scheme', Voucher::LIMIT_INSTANCE));
            $table->text('can_redeem')->nullable();
            $table->text('cannot_redeem')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->double('value', 10, 2)->nullable();
            $table->text('data')->nullable();
        });

        Schema::create($redeemerPivotTable, function (Blueprint $table) use ($voucherTable) {
            $table->bigIncrements('id');
            $table->morphs('voucherable');
            $table->unsignedBigInteger('voucher_id');
            $table->timestamp('redeemed_at');

            $table->foreign('voucher_id')->references('id')->on($voucherTable);
        });

        Schema::create($itemPivotTable, function (Blueprint $table) use ($voucherTable) {
            $table->bigIncrements('id');
            $table->morphs('item');
            $table->unsignedBigInteger('voucher_id');

            $table->foreign('voucher_id')->references('id')->on($voucherTable);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(config('vouchers.table', 'vouchers'));
        Schema::dropIfExists(config('vouchers.pivot_table', 'redeemer_voucher'));
    }
}
