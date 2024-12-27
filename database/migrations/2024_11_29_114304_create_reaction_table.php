<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('chat.reaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat.message')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('element');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat.reaction');
    }
};
