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
        Schema::table('events', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->change();
            $table->string('venue_address')->nullable()->change();
            $table->string('venue_website')->nullable()->change();
            $table->string('image')->nullable()->change();   // also recommended
            $table->text('content')->nullable()->change();   // recommended
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('venue_name')->nullable(false)->change();
            $table->string('venue_address')->nullable(false)->change();
            $table->string('venue_website')->nullable(false)->change();
            $table->string('image')->nullable(false)->change();
            $table->text('content')->nullable(false)->change();
        });
    }
};
