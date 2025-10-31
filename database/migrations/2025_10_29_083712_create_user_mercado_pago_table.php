<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('hr.user_mercado_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('hr.user')->cascadeOnDelete();

            $table->string('mp_user_id')->nullable();
            $table->string('public_key')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('token_type')->nullable();
            $table->text('scope')->nullable();
            $table->boolean('live_mode')->default(false);
            $table->integer('expires_in')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr.user_mercado_pago');
    }
};
