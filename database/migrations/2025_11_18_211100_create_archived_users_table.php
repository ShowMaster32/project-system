<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archived_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id')->index();
            $table->string('name')->nullable();
            $table->string('email')->index();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('deleted_by')->nullable()->index();
            $table->timestamp('deleted_at')->useCurrent();
            $table->json('data_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_users');
    }
};
