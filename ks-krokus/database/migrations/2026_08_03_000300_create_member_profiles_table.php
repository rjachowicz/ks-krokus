<?php

declare(strict_types=1);

use App\Enums\AccountRequestStatus;
use App\Enums\MemberVerificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('pzss_license_number', 100)->nullable()->unique();
            $table->date('pzss_license_expires_at')->nullable();
            $table->string('shooting_patent_number', 100)->nullable();
            $table->string('firearm_permit_number', 100)->nullable();
            $table->string('club_member_number', 100)->nullable();
            $table->unsignedSmallInteger('joined_club_year')->nullable();
            $table->json('disciplines')->nullable();
            $table->string('verification_status', 32)
                ->default(MemberVerificationStatus::Unverified->value)
                ->index();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = now();

        DB::table('account_requests')
            ->where('status', AccountRequestStatus::Approved->value)
            ->whereNotNull('created_user_id')
            ->orderBy('id')
            ->each(function (object $accountRequest) use ($now): void {
                DB::table('member_profiles')->insertOrIgnore([
                    'user_id' => $accountRequest->created_user_id,
                    'pzss_license_number' => $accountRequest->pzss_license_number,
                    'pzss_license_expires_at' => $accountRequest->pzss_license_expires_at,
                    'shooting_patent_number' => $accountRequest->patent_number,
                    'firearm_permit_number' => $accountRequest->firearm_permit_number,
                    'club_member_number' => $accountRequest->member_number,
                    'joined_club_year' => $accountRequest->joined_year,
                    'disciplines' => $accountRequest->disciplines,
                    'verification_status' => MemberVerificationStatus::Verified->value,
                    'verified_at' => $accountRequest->reviewed_at ?? $now,
                    'verified_by' => $accountRequest->reviewed_by,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
