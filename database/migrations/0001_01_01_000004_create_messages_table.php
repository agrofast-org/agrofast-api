<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
  public function up()
  {
    Schema::create('messages', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->uuid('chat_id');
      $table->foreign('chat_id')->references('uuid')->on('chats')->onDelete('cascade');
      $table->unsignedBigInteger('answer_to')->nullable();
      $table->foreign('answer_to')->references('id')->on('messages')->onDelete('set null');
      $table->text('message');
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('messages');
  }
}
