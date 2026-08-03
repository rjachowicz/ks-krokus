<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_requests', function (Blueprint $table): void {
            $table->timestamp('anonymized_at')->nullable();
            $table->index(
                ['status', 'reviewed_at', 'anonymized_at'],
                'account_requests_retention_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('account_requests', function (Blueprint $table): void {
            $table->dropIndex('account_requests_retention_index');
            $table->dropColumn('anonymized_at');
        });
    }
};
