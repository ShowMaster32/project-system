<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->date('due_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->string('status', 50)->default('pending');
            
            // Validation workflow
            $table->boolean('requires_validation')->default(true);
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_notes')->nullable();
            
            $table->timestamps();
            
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};