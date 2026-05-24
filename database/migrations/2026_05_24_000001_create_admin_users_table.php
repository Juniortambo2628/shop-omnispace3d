<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_users')) {
            return;
        }

        Schema::create('admin_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 255);
            $table->string('password_hash');
            $table->string('display_name');
            $table->string('role', 50)->default('order_manager');
            $table->boolean('active')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
