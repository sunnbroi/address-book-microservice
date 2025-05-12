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
            $table->id();
            $table->foreignUuid('message_id')->nullable()->references('id')->on('messages')->onDelete('cascade');
            $table->foreignUuid('address_book_id')->references('id')->on('address_books')->onDelete('cascade');
            $table->foreignUuid('recipient_id')->references('id')->on('recipients')->onDelete('cascade')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending')->nullable();
            $table->text('error')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable(); // время успешной доставки
            $table->index('message_id');
            $table->index('address_book_id');
            $table->index('recipient_id');      
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
