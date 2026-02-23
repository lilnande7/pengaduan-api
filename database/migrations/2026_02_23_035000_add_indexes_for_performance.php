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
        // Add indexes for better performance
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['status']);
            $table->index(['category']);
            $table->index(['created_at']);
            $table->index(['phone']);
        });
        
        Schema::table('replies', function (Blueprint $table) {
            $table->index(['ticket_id']);
            $table->index(['admin_id']);
            $table->index(['created_at']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['category']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['phone']);
        });
        
        Schema::table('replies', function (Blueprint $table) {
            $table->dropIndex(['ticket_id']);
            $table->dropIndex(['admin_id']);
            $table->dropIndex(['created_at']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};