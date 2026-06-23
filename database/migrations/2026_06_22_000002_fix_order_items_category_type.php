<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $current = Schema::getColumnType('order_items', 'category');
        if ($current !== 'string') {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('category', 100)->nullable()->default(null)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('category')->default(0)->change();
        });
    }
};
