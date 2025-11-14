<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            
            // Relazione polimorfica
            $table->string('documentable_type')->nullable();
            $table->unsignedBigInteger('documentable_id')->nullable();
            
            // Folder structure
            $table->foreignId('parent_id')->nullable()->constrained('documents')->cascadeOnDelete();
            $table->boolean('is_folder')->default(false);
            
            $table->string('name');
            $table->string('file_path', 1000)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('size_bytes')->nullable();
            
            // Versioning
            $table->integer('version')->default(1);
            $table->boolean('is_latest_version')->default(true);
            
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
            $table->index('parent_id');
            $table->index(['documentable_type', 'documentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};