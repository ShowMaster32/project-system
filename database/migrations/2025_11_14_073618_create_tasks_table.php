<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days')->nullable();
            
            $table->jsonb('depends_on')->default('[]');
            
            $table->string('status', 50)->default('pending');
            $table->integer('progress')->default(0);
            
            $table->string('color', 7)->nullable();
            $table->boolean('is_critical_path')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
            $table->index('work_package_id');
            $table->index('parent_id');
        });
        
        // GIN index per JSONB
        DB::statement('CREATE INDEX idx_tasks_depends_on ON tasks USING gin(depends_on)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};