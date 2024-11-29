<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('user', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('surname');
      $table->string('number')->unique();
      $table->string('email')->unique()->nullable();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->boolean('authenticated')->default(false);
      $table->boolean('active')->default(true);
      $table->string('profile_picture')->nullable();
      $table->rememberToken();
      $table->timestamps();
    });

    Schema::create('password_reset_token', function (Blueprint $table) {
      $table->string('email')->primary();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->foreignId('user_id')->nullable()->constrained('user')->onDelete('cascade');
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->longText('payload');
      $table->integer('last_activity')->index();
    });

    Schema::create('auth_code', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
      $table->string('code');
      $table->unsignedInteger('attempts')->default(0);
      $table->boolean('active')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('auth_codes');
    Schema::dropIfExists('sessions');
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('user');
  }
};
