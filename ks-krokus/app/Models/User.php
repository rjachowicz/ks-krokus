<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
        'is_trainer',
        'has_range_access',
        'show_email_publicly',
        'show_phone_publicly',
        'trainer_bio',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'is_trainer' => 'boolean',
            'has_range_access' => 'boolean',
            'show_email_publicly' => 'boolean',
            'show_phone_publicly' => 'boolean',
            'password_link_sent_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: static fn (string $value): string => mb_strtolower(trim($value)),
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function canManageContent(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::Moderator], true);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification((string) $token));
    }

    public function saleListings(): HasMany
    {
        return $this->hasMany(SaleListing::class);
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function verifiedMemberProfiles(): HasMany
    {
        return $this->hasMany(MemberProfile::class, 'verified_by');
    }

    public function reviewedAccountRequests(): HasMany
    {
        return $this->hasMany(AccountRequest::class, 'reviewed_by');
    }

    public function createdFromAccountRequests(): HasMany
    {
        return $this->hasMany(AccountRequest::class, 'created_user_id');
    }

    public function passwordLinkSender(): BelongsTo
    {
        return $this->belongsTo(self::class, 'password_link_sent_by');
    }

    public function scopeTrainers(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('is_trainer', true)
            ->orderBy('name');
    }

    public function scopeRangeAccess(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('has_range_access', true)
            ->orderBy('name');
    }
}
