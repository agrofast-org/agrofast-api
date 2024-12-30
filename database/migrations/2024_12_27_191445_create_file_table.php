<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file.image', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->foreignId('uploaded_by')->nullable()->constrained('hr.user')->onDelete('set null');
            $table->boolean('active')->default(true);
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file.image');
    }
};
