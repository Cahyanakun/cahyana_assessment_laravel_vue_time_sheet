<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 8, 2);
            $table->timestamps();

            $table->index(['company_id', 'employee_id', 'date']);
            $table->index(['project_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
