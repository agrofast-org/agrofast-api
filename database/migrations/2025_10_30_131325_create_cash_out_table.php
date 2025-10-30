<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\Traits\CreatesPostgresEnums;
use App\Enums\CashOutStatus;

return new class extends Migration
{
    use CreatesPostgresEnums;

    public function up(): void
    {
        $this->createEnum('status_enum', CashOutStatus::class);

        Schema::create('cash_out', function (Blueprint $table) {
            $table->id()->unique()->primary();
            $table->uuid()->unique();

            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->text('rejected_for')->nullable();
            $table->foreignId('payment_proof_id')->nullable()->constrained('file.file');
            
            $table->timestamps();
            $table->softDeletes();
        });

        $this->addEnumColumn('cash_out', 'status', 'status_enum', CashOutStatus::Pending->value);
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_out');
        $this->dropEnumIfExists('status_enum');
    }
};
