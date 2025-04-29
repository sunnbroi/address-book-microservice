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
        Schema::create('delivery_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->uuid('recipient_id');
            $table->uuid('address_book_id');
            $table->enum('status', ['OK', 'ERROR', 'PENDING'])->default('PENDING');
            $table->text('error_message')->nullable();
            $table->timestamps();
        
            
        
            $table->foreign('recipient_id')
                  ->references('id')->on('recipients')
                  ->onDelete('cascade');
            
            $table->foreign('message_id')
                  ->references('id')->on('messages')
                  ->onDelete('cascade');

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
        Schema::dropIfExists('delivery_logs');
    }
};
