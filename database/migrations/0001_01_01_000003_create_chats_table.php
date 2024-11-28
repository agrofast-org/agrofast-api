<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
  public function up()
  {
    Schema::create('chats', function (Blueprint $table) {
      $table->uuid('uuid')->primary();
      $table->string('name')->nullable();
      $table->string('picture')->nullable();
      $table->boolean('is_group')->default(false);
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('chats');
  }
}
