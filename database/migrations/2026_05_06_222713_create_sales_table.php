<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->nullable()->constrained('flocks')->onDelete('set null');
            $table->string('product_type'); // eggs_tray, eggs_crate, live_bird, meat_kg, etc.
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->date('sale_date');
            $table->string('customer_name')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('sale_date');
            $table->index('product_type');
            $table->index('flock_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};