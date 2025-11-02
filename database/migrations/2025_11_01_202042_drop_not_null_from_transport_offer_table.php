<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.offer ALTER COLUMN gain DROP NOT NULL;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.offer DROP CONSTRAINT IF EXISTS offer_gain_key;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.offer ALTER COLUMN gain SET NOT NULL;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.offer ADD CONSTRAINT offer_gain_key UNIQUE (gain);');
    }
};
