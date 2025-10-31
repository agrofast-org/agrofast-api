<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cash_out', function (Blueprint $table) {
            $table->id()->unique()->primary();
            $table->uuid()->unique();

            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejected_for')->nullable();
            $table->foreignId('payment_proof_id')->nullable()->constrained('file.file');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_out');
    }
};
