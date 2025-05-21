<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';
    /**
     * Run the migrations.
     * 
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('client_key')->unique();
           /* $table->unsignedBigInteger('api_user_id')->unique()->nullable(); */
            $table->foreign('api_user_id')->references('id')->on('api_users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('secret_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     //   Schema::dropIfExists('clients');
    }
};
