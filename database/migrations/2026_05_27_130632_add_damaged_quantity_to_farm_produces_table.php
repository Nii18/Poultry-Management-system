<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('farm_produces', function (Blueprint $table) {
        $table->decimal('quantity_damaged', 10, 2)->default(0)->after('quantity');
        // quantity_damaged only makes sense for eggs mostly, but we allow it for all types
        // net_quantity = quantity - quantity_damaged (computed, not stored)
    });
}

public function down()
{
    Schema::table('farm_produces', function (Blueprint $table) {
        $table->dropColumn('quantity_damaged');
    });
}
};
