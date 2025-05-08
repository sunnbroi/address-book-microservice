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
            $table->foreignUuid('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreignUuid('recipient_id')->references('id')->on('recipients')->onDelete('cascade');
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();
            
            $table->index('message_id');
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
