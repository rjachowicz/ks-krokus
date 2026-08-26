<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('cover_variant_path')->nullable()->after('cover_image_path');
            $table->json('cover_crop')->nullable()->after('cover_variant_path');
        });

        Schema::table('post_images', function (Blueprint $table): void {
            $table->string('thumbnail_path')->nullable()->after('path');
            $table->json('crop')->nullable()->after('thumbnail_path');
        });

        Schema::table('sale_listing_images', function (Blueprint $table): void {
            $table->json('crop')->nullable()->after('thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('sale_listing_images', function (Blueprint $table): void {
            $table->dropColumn('crop');
        });

        Schema::table('post_images', function (Blueprint $table): void {
            $table->dropColumn(['thumbnail_path', 'crop']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['cover_variant_path', 'cover_crop']);
        });
    }
};
