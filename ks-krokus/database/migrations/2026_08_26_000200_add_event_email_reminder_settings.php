<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('event_email_notifications_enabled')
                ->default(false);
            $table->timestamp('event_email_notifications_confirmed_at')
                ->nullable();
        });

        Schema::table('sport_events', function (Blueprint $table): void {
            $table->boolean('email_reminders_enabled')
                ->default(false)
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('sport_events', function (Blueprint $table): void {
            $table->dropColumn('email_reminders_enabled');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'event_email_notifications_enabled',
                'event_email_notifications_confirmed_at',
            ]);
        });
    }
};
