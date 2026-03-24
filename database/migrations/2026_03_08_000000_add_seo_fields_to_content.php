<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add SEO fields to pages table
        Schema::table('pages', function (Blueprint $table) {
            $table->string('meta_title', 60)->nullable()->after('slug');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->boolean('index')->default(true)->after('meta_keywords');
        });

        // Add SEO fields to articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->string('meta_title', 60)->nullable()->after('slug');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->boolean('index')->default(true)->after('meta_keywords');
        });

        // Add SEO fields to events table
        Schema::table('events', function (Blueprint $table) {
            $table->string('meta_title', 60)->nullable()->after('slug');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->boolean('index')->default(true)->after('meta_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'index']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'index']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'index']);
        });
    }
};
