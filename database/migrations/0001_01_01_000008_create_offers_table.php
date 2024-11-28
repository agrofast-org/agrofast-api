<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
  public function up()
  {
    Schema::create('offers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->foreignId('request_id')->constrained('transport_requests')->onDelete('cascade');
      $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
      $table->decimal('float', 10, 2);
      $table->boolean('active')->default(true);
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('offers');
  }
}
