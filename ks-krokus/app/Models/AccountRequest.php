<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountRequestStatus;
use Database\Factories\AccountRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AccountRequest extends Model
{
    /** @use HasFactory<AccountRequestFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'pzss_license_number',
        'pzss_license_expires_at',
        'patent_number',
        'firearm_permit_number',
        'member_number',
        'joined_year',
        'disciplines',
        'additional_information',
        'data_processing_consent',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'internal_notes',
        'created_user_id',
        'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'pzss_license_expires_at' => 'date',
            'joined_year' => 'integer',
            'disciplines' => 'array',
            'data_processing_consent' => 'boolean',
            'status' => AccountRequestStatus::class,
            'reviewed_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', AccountRequestStatus::Pending->value);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
