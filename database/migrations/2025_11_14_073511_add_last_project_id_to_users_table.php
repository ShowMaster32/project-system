<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('last_project_id')->nullable()
                  ->constrained('projects')
                  ->nullOnDelete();
            
            $table->index('last_project_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['last_project_id']);
            $table->dropColumn('last_project_id');
        });
    }
};