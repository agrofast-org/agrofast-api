<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr.pix_payment', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->unsignedBigInteger('payment_id')->unique()->comment('Payment ID on Mercado Pago');
            $table->string('status')->comment('Payment status');
            $table->string('status_detail')->nullable()->comment('Payment status details');
            $table->decimal('transaction_amount', 12, 2)->comment('Transaction amount');
            $table->string('external_reference')->nullable()->comment('External reference');
            $table->timestamp('date_created')->nullable()->comment('Payment creation date');
            $table->timestamp('date_approved')->nullable()->comment('Payment approval date');
            $table->timestamp('date_last_updated')->nullable()->comment('Date of last update');
            $table->timestamp('date_of_expiration')->nullable()->comment('Payment expiration date (up to 30 days)');
            $table->text('qr_code')->comment('QR Code string for payment');
            $table->text('qr_code_base64')->comment('QR Code image in Base64');
            $table->string('ticket_url')->nullable()->comment('URL with payment instructions');
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.pix_payment');
    }
};
