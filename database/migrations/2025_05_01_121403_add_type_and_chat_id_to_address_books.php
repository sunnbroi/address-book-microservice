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
         Schema::table('address_books', function (Blueprint $table) {
        $table->string('type')->default('manual')->after('id'); // 'manual' или 'telegram'
        $table->string('chat_id')->nullable()->after('type');   // chat_id только если telegram
    });
}

public function down(): void
{
    Schema::table('address_books', function (Blueprint $table) {
        $table->dropColumn(['type', 'chat_id']);
    });
    }
};
