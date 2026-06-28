<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('flocks', function (Blueprint $table) {
            $table->enum('sex', ['male', 'female'])->nullable()->after('is_breeding_stock');
        });
    }

    public function down()
    {
        Schema::table('flocks', function (Blueprint $table) {
            $table->dropColumn('sex');
        });
    }
};