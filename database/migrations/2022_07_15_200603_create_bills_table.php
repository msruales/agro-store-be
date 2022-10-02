<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('persons');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type_voucher',['invoice', 'sales_note']);
            $table->string('serial_voucher',7)->nullable();
            $table->string('num_voucher',10);
            $table->dateTime('date');
            $table->decimal('tax',10,2);
            $table->decimal('utility',10,2);
            $table->decimal('total',11,2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills');
    }
};
