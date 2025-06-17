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
            $table->foreignUuid('address_book_id')->nullable()->references('id')->on('address_books')->onDelete('cascade');
            $table->foreignUuid('recipient_id')->nullable()->references('id')->on('recipients')->onDelete('cascade');
            $table->enum('type', ['message', 'photo', 'document', 'video', 'audio', 'voice'])->default('message');
            $table->text('text');
            $table->string('link')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
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
