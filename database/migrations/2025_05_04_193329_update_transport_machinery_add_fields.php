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
        Schema::table('transport.machinery', function (Blueprint $table) {
            $table->string('type')->after('model');
            $table->string('manufacturer')->after('type');
            $table->date('manufacturer_date')->after('manufacturer');
            $table->decimal('weight', 8, 2)->nullable()->after('manufacturer_date');
            $table->decimal('lenght', 8, 2)->nullable()->after('weight');
            $table->decimal('width', 8, 2)->nullable()->after('lenght');
            $table->decimal('height', 8, 2)->nullable()->after('width');
            $table->unsignedTinyInteger('axles')->nullable()->after('height');
            $table->string('tire_config')->nullable()->after('axles');
            $table->text('obs')->nullable()->after('tire_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport.machinery', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'manufacturer',
                'manufacturer_date',
                'weight',
                'lenght',
                'width',
                'height',
                'axles',
                'tire_config',
                'obs',
            ]);
        });
    }
};
