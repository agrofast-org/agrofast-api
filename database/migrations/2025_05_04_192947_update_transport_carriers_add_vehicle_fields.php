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
        Schema::table('transport.carriers', function (Blueprint $table) {
            $table->string('renavam')->after('plate');
            $table->string('chassi')->after('renavam');
            $table->string('manufacturer')->after('chassi');
            $table->integer('manufacture_year')->after('model');
            $table->string('licensing_uf', 2)->after('manufacture_year');
            $table->string('vehicle_type')->after('licensing_uf');
            $table->string('body_type')->after('vehicle_type');
            $table->decimal('plank_length', 8, 2)->after('body_type');
            $table->decimal('tare', 8, 2)->after('plank_length');
            $table->decimal('pbtc', 8, 2)->after('tare');
            $table->unsignedTinyInteger('axles')->after('pbtc');
            $table->unsignedTinyInteger('tires_per_axle')->after('axles');
            $table->string('traction')->after('tires_per_axle');
            $table->string('rntrc')->after('traction');
            $table->text('obs')->nullable()->after('rntrc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport.carrier', function (Blueprint $table) {
            $table->dropColumn([
                'renavam',
                'chassi',
                'manufacturer',
                'manufacture_year',
                'licensing_uf',
                'vehicle_type',
                'body_type',
                'plank_length',
                'tare',
                'pbtc',
                'axles',
                'tires_per_axle',
                'traction',
                'rntrc',
                'obs',
            ]);
        });
    }
};
