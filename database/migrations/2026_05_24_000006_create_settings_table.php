<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 191);
            $table->text('value')->nullable();

            $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
