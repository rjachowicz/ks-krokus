<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_reminder_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('sport_event_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('subscribed_at');
            $table->timestamp('reminder_sent_at')->nullable()->index();
            $table->timestamps();

            $table->unique(
                ['user_id', 'sport_event_id'],
                'event_reminder_subscription_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reminder_subscriptions');
    }
};
