<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_levels')) {
            return;
        }

        Schema::create('stock_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_code', 100);
            $table->string('product_name');
            $table->integer('stock_limit')->nullable();

            $table->unique('product_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
