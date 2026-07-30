<?php

declare(strict_types=1);

use App\Enums\ResultStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_competition_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('participant_name');
            $table->string('club_name')->nullable();
            $table->string('category')->nullable();
            $table->string('score', 64);
            $table->unsignedInteger('place')->nullable()->index();
            $table->string('classification')->nullable();
            $table->string('status', 32)
                ->default(ResultStatus::Official->value)
                ->index();
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_competition_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_results');
    }
};
