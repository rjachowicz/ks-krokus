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
            $table->foreignId('password_link_sent_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('password_link_sent_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('password_link_sent_by');
            $table->dropColumn('password_link_sent_at');
        });
    }
};
