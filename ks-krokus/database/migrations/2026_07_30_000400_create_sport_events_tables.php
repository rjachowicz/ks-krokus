<?php

declare(strict_types=1);

use App\Enums\PublicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('discipline', 32)->index();
            $table->string('competition_system', 32)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sport_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('event_type', 32)->index();
            $table->text('description')->nullable();
            $table->dateTime('start_at')->index();
            $table->dateTime('end_at')->nullable();
            $table->string('location_name');
            $table->string('address')->nullable();
            $table->string('discipline', 32)->nullable()->index();
            $table->string('competition_system', 32)->nullable()->index();
            $table->string('status', 32)
                ->default(PublicationStatus::Draft->value)
                ->index();
            $table->boolean('is_public')->default(true)->index();
            $table->string('registration_url', 2048)->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_competitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sport_event_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('competition_definition_id')
                ->constrained()
                ->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique([
                'sport_event_id',
                'competition_definition_id',
            ], 'event_competition_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_competitions');
        Schema::dropIfExists('sport_events');
        Schema::dropIfExists('competition_definitions');
    }
};
