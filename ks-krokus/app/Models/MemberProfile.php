<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemberAgeCategory;
use App\Enums\MemberVerificationStatus;
use Database\Factories\MemberProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MemberProfile extends Model
{
    /** @use HasFactory<MemberProfileFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'pzss_license_number',
        'pzss_license_expires_at',
        'shooting_patent_number',
        'firearm_permit_number',
        'club_member_number',
        'joined_club_year',
        'age_category',
        'disciplines',
        'verification_status',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'pzss_license_expires_at' => 'date',
            'joined_club_year' => 'integer',
            'age_category' => MemberAgeCategory::class,
            'disciplines' => 'array',
            'verification_status' => MemberVerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
