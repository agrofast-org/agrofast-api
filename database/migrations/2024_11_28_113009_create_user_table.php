<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr.user', function (Blueprint $table) {
            $table->id()->unique()->primary();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('surname');
            $table->string('number')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('language', 10)->default('pt-BR');
            $table->boolean('number_verified')->default(false);
            $table->timestamp('number_verified_at')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });

        Schema::create('hr.password_reset_token', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.user');
        Schema::dropIfExists('hr.password_reset_token');
    }
};
