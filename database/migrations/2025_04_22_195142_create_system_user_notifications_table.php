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
        Schema::create('system.user_notifications', function (Blueprint $table) {
            $table->id()->unique()->primary();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('hr.user');
            $table->foreignId('system_message_id')->constrained('system.system_message');
            $table->boolean('read')->default(false);
            $table->boolean('sent_email')->default(false);
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
        Schema::dropIfExists('system.user_notifications');
    }
};
