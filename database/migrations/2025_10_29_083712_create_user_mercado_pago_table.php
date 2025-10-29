<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr.user_mercado_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('hr.user')->cascadeOnDelete();

            $table->string('full_name'); // nome completo
            $table->string('cpf', 11)->unique(); // formato 00000000000
            $table->string('email')->nullable(); // email usado na conta MP
            $table->string('phone')->nullable(); // telefone do titular

            $table->string('mp_user_id')->nullable();
            $table->string('mp_access_token', 255)->nullable();
            $table->string('mp_refresh_token', 255)->nullable();
            $table->timestamp('mp_token_expires_at')->nullable();

            $table->enum('status', ['pending', 'connected', 'disconnected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr.user_mercado_pago');
    }
};
