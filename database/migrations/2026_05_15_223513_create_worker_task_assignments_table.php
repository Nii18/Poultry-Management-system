<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('worker_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('worker_tasks')->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->date('assignment_date');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['assigned_to', 'assignment_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('worker_task_assignments');
    }
};