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
            $table->uuid('address_book_id');
            $table->string('full_name');
            $table->string('username')->nullable();
            $table->string('chat_id');
            $table->string('type')->default('user');
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();
        
            $table->foreign('address_book_id')
                  ->references('id')->on('address_books')
                  ->onDelete('cascade');
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
