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
        Schema::create('tickets', function (Blueprint $table) {
           $table->id();
        $table->string('ticket_number')->unique();
        $table->string('name');
        $table->string('phone');
        $table->string('email')->nullable();
        $table->string('category');
        $table->text('message');
        $table->string('evidence_file')->nullable();
        $table->enum('status',['baru','diproses','selesai'])->default('baru');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
