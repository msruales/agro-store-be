<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateVariablesTable extends Migration
{
    public function up(): void
    {
        Schema::create('template_variables', function (Blueprint $table) {
            $table->id();
            $table->string('template_id');
            $table->string('template_name');
            $table->string('variable_name');
            $table->string('field_name')->default(null);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_variables');
    }
}
