<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('offers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
      $table->foreignId('request_id')->constrained('requests')->onDelete('cascade');
      $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
      $table->decimal('price', 10, 2);
      $table->boolean('active')->default(true);
      $table->timestamps();
      $table->timestamp('inactivated_in')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('offers');
  }
};
