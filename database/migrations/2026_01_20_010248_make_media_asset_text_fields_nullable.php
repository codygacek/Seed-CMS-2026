<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('alt_text')->nullable()->change();
            $table->string('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('alt_text')->nullable(false)->change();
            $table->string('content')->nullable(false)->change();
        });
    }
};