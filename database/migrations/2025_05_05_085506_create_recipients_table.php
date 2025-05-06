<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('telegram_user_id');
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('recipients');
    }
};
