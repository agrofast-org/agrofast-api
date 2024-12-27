<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('hr.document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->integer('document_type');
            $table->string('document')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->timestamp('inactivated_in')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr.document');
    }
};
