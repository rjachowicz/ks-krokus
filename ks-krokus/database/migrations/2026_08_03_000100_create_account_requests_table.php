<?php

declare(strict_types=1);

use App\Enums\AccountRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('email')->unique();
            $table->string('phone', 32);
            $table->date('birth_date');
            $table->string('pzss_license_number', 100)->unique();
            $table->date('pzss_license_expires_at');
            $table->string('patent_number', 100);
            $table->string('firearm_permit_number', 100)->nullable();
            $table->string('member_number', 100)->nullable();
            $table->unsignedSmallInteger('joined_year')->nullable();
            $table->json('disciplines');
            $table->text('additional_information')->nullable();
            $table->boolean('data_processing_consent');
            $table->string('status', 32)->default(AccountRequestStatus::Pending->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_requests');
    }
};
