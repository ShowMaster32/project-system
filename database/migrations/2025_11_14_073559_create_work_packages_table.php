<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days')->nullable();
            
            $table->string('status', 50)->default('active');
            $table->integer('progress')->default(0);
            
            $table->string('color', 7)->default('#3b82f6');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
            $table->index('leader_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_packages');
    }
};