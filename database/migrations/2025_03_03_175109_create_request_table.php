<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transport.request', function (Blueprint $table) {
            $table->id()->primary();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');

            $table->string('origin_place_id');
            $table->string('origin_place_name')->nullable();
            $table->string('origin_latitude')->nullable();
            $table->string('origin_longitude')->nullable();
            $table->string('destination_place_id');
            $table->string('destination_place_name')->nullable();
            $table->string('destination_origin_latitude')->nullable();
            $table->string('destination_origin_longitude')->nullable();

            $table->integer('distance')->nullable()->comment('Distance in meters between origin and destination in meters');
            $table->integer('estimated_time')->nullable()->comment('Estimated travel time in seconds between origin and destination');
            $table->string('estimated_cost')->nullable();

            $table->foreignId('payment_id')->nullable(); // ->constrained('payment')->onDelete('cascade');

            $table->timestamp('desired_date')->nullable();
            $table->enum('state', [
                'pending',
                'payment_pending',
                'approved',
                'rejected',
                'in_progress',
                'canceled',
                'completed',
            ])->default('pending');
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
        Schema::dropIfExists('transport.requests');
    }
};
