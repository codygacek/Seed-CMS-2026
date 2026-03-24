<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nav_menu_id');
            $table->string('label');
            $table->string('link');
            $table->unsignedInteger('position');
            $table->unsignedInteger('parent_id');
            $table->boolean('new_window');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_menu_items');
    }
};
