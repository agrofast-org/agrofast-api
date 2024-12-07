<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('message', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->string('chat_id');
            $table->unsignedBigInteger('answer_to')->nullable();
            $table->text('message');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->timestamp('inactivated_in')->nullable();

            $table->foreign('answer_to')->references('id')->on('message')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message');
    }
};
