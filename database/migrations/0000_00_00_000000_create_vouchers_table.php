<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    protected string $voucherTable;
    protected string $redeemerPivotTable;
    protected string $itemPivotTable;

    public function __construct()
    {
        $this->voucherTable = config('vouchers.tables.vouchers', 'vouchers');
        $this->redeemerPivotTable = config('vouchers.tables.redeemer_pivot_table', 'redeemer_voucher');
        $this->itemPivotTable = config('vouchers.tables.item_pivot_table', 'item_voucher');
    }
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create($this->voucherTable, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 32)->unique();
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->json('quantity_used')->nullable();
            $table->string('limit_scheme', 24);
            $table->json('can_redeem')->nullable();
            $table->json('cannot_redeem')->nullable();
            $table->dateTime('active_date')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->double('value', 10, 2)->nullable();
            $table->text('data')->nullable();
        });

        Schema::create($this->redeemerPivotTable, function (Blueprint $table) {
            $table->string('redeemer_type');
            $table->string('redeemer_id');
            $table->index(['redeemer_type', 'redeemer_id']);

            $table->unsignedBigInteger('voucher_id');
            $table->timestamp('redeemed_at');

            $table->foreign('voucher_id')->references('id')->on($this->voucherTable);
        });

        Schema::create($this->itemPivotTable, function (Blueprint $table) {
            $table->string('item_type');
            $table->string('item_id');
            $table->index(['item_type', 'item_id']);

            $table->unsignedBigInteger('voucher_id');

            $table->foreign('voucher_id')->references('id')->on($this->voucherTable);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists($this->itemPivotTable);
        Schema::dropIfExists($this->redeemerPivotTable);
        Schema::dropIfExists($this->voucherTable);
    }
}
