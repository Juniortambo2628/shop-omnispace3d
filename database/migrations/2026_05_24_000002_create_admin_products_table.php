<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_products')) {
            return;
        }

        Schema::create('admin_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prod_id', 100);
            $table->string('code', 100);
            $table->string('name');
            $table->string('category_id', 50);
            $table->json('colors')->nullable();
            $table->string('dimensions')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('price_display', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 100)->default('per item');
            $table->boolean('is_poa')->default(false);
            $table->boolean('is_override')->default(false);
            $table->string('original_catalog_id', 100)->nullable();
            $table->boolean('active')->default(true);
            $table->string('created_by', 100)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();

            $table->unique('prod_id');
            $table->index('code');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_products');
    }
};
