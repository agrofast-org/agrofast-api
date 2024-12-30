<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr.user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('number')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->boolean('number_verified')->default(false);
            $table->timestamp('number_verified_at')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('hr.password_reset_token', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('hr.auth_code', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('code');
            $table->unsignedInteger('attempts')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('hr.session', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('auth_code_id')->constrained('hr.auth_code')->onDelete('cascade');
            $table->boolean('authenticated')->default(false);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('last_activity')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.auth_code');
        Schema::dropIfExists('hr.session');
        Schema::dropIfExists('hr.hr.password_reset_tokens');
        Schema::dropIfExists('hr.user');
    }
};
