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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('first_name',150)->nullable();
            $table->string('last_name',150)->nullable();
            $table->string('document_type',20)->nullable();
            $table->string('document_number',13)->nullable()->unique();
            $table->string('direction',70)->nullable();
            $table->string('phone_number',10)->nullable();
            $table->string('email',50)->nullable()->unique();
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
        Schema::dropIfExists('persons');
    }
};
