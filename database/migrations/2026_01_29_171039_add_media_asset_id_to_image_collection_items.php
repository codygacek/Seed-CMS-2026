<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('image_collection_items', function (Blueprint $table) {
            $table->unsignedBigInteger('media_asset_id')->nullable()->after('image');
            $table->index('media_asset_id');
        });
    }

    public function down(): void
    {
        Schema::table('image_collection_items', function (Blueprint $table) {
            $table->dropIndex(['media_asset_id']);
            $table->dropColumn('media_asset_id');
        });
    }
};