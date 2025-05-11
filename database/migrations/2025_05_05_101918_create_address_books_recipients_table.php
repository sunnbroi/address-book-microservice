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
        Schema::create('address_books_recipients', function (Blueprint $table) {
            $table->uuid('address_book_id');
            $table->uuid(column: 'recipient_id');
            $table->timestamps();

            $table->primary(['address_book_id', 'recipient_id']);

            $table->foreign('address_book_id')
                ->references('id')
                ->on('address_books')
                ->onDelete('cascade');
            
            $table->foreign('recipient_id')
                ->references('id')
                ->on('recipients')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('address_books_recipients');
    }
};
