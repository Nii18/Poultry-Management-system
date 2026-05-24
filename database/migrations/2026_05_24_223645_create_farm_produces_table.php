<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_produces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->nullable()->constrained('flocks')->nullOnDelete();
            $table->string('product_type', 50); // eggs, live_bird, meat, breeding_stock, manure
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 30)->default('pieces'); // pieces, kg, bags, litres
            $table->date('produce_date');
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['product_type', 'produce_date']);
            $table->index('flock_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_produces');
    }
};