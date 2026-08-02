<?php

declare(strict_types=1);

use App\Enums\SaleListingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 48);
            $table->string('firearm_type', 32)->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('caliber', 64)->nullable();
            $table->string('condition', 32)->nullable();
            $table->unsignedSmallInteger('year_of_manufacture')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('price_negotiable')->default(false);
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 32)->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_email')->default(false);
            $table->string('status', 32)->default(SaleListingStatus::Draft->value);
            $table->boolean('is_hidden')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expiration_reminder_sent_at')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('published_at');
            $table->index('expires_at');
            $table->index(['status', 'is_hidden', 'published_at', 'expires_at']);
            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['category', 'firearm_type']);
            $table->index('caliber');
        });

        Schema::create('sale_listing_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_listing_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['sale_listing_id', 'sort_order']);
            $table->index(['sale_listing_id', 'is_primary']);
        });

        Schema::create('sale_listing_moderations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['sale_listing_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        Schema::create('sale_listing_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 32);
            $table->text('details')->nullable();
            $table->string('reporter_hash', 64);
            $table->string('status', 24)->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['sale_listing_id', 'reporter_hash', 'reason'], 'sale_listing_report_dedup');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_listing_reports');
        Schema::dropIfExists('sale_listing_moderations');
        Schema::dropIfExists('sale_listing_images');
        Schema::dropIfExists('sale_listings');
    }
};
