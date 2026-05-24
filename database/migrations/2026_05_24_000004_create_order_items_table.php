<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            return;
        }

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_id', 20);
            $table->string('product_id', 100)->nullable();
            $table->string('product_name');
            $table->string('product_code', 100)->nullable();
            $table->string('category')->nullable();
            $table->string('color_id', 20)->nullable();
            $table->string('color_name', 100)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->index('order_id');
            $table->index('product_code');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
