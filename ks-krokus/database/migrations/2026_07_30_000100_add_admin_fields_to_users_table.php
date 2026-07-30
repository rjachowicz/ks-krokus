<?php

declare(strict_types=1);

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 32)
                ->default(UserRole::User->value)
                ->index()
                ->after('password');
            $table->string('phone', 32)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->index()->after('phone');
            $table->boolean('is_trainer')->default(false)->index()->after('is_active');
            $table->boolean('has_range_access')->default(false)->index()->after('is_trainer');
            $table->boolean('show_email_publicly')->default(false)->after('has_range_access');
            $table->boolean('show_phone_publicly')->default(false)->after('show_email_publicly');
            $table->text('trainer_bio')->nullable()->after('show_phone_publicly');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'role',
                'phone',
                'is_active',
                'is_trainer',
                'has_range_access',
                'show_email_publicly',
                'show_phone_publicly',
                'trainer_bio',
            ]);
        });
    }
};
