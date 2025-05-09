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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_book_id')->references('id')->on('address_books')->onDelete('cascade');
            $table->foreignUuid('recipient_id')->references('id')->on('recipients')->onDelete('cascade')->nullable();
            $table->enum('type', ['message', 'photo', 'document']);
            $table->text('text');
            $table->string('file')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
