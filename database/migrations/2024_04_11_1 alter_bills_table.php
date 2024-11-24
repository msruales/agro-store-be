<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('accessKey', 49)->nullable();
            $table->integer('sequential')->nullable();
            $table->string('status_voucher')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('serial_voucher', 7)->nullable();
            $table->string('num_voucher', 10);
            $table->dropColumn('status_voucher');
        });
    }
};
