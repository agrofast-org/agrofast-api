<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('chat.chat_user', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamp('joined_in')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('left_in')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat.chat_user');
    }
};
