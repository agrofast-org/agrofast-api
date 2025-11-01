<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.machinery ALTER COLUMN plate DROP NOT NULL;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.machinery DROP CONSTRAINT IF EXISTS machinery_plate_key;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.machinery ALTER COLUMN plate SET NOT NULL;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE transport.machinery ADD CONSTRAINT machinery_plate_key UNIQUE (plate);');
    }
};
