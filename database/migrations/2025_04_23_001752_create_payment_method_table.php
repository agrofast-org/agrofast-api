<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr.payment_method', function (Blueprint $table) {
            $table->id()->unique()->primary();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('hr.user');
            $table->foreignId('payment_method_type')->constrained('hr.payment_method_type')->onDelete('cascade');
            $table->string('provider')->nullable();
            $table->string('number');
            $table->string('holder_name')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.payment_method');
    }
};
