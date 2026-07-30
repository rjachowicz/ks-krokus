<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isModerator(): bool
    {
        return $this->role === UserRole::Moderator;
    }

    public function canManageContent(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::Moderator], true);
    }

    public function scopeTrainers(Builder $query): Builder
    {
        return $query
            ->where('is_trainer', true)
            ->orderBy('name');
    }

    public function scopeRangeAccess(Builder $query): Builder
    {
        return $query
            ->where('has_range_access', true)
            ->orderBy('name');
    }

    public function clubPositions(): BelongsToMany
    {
        return $this->belongsToMany(ClubPosition::class)
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(SportEvent::class, 'created_by');
    }

    public function enteredResults(): HasMany
    {
        return $this->hasMany(EventResult::class, 'entered_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(EventResult::class);
    }
}
