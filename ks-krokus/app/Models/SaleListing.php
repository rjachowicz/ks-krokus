<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingCondition;
use App\Enums\SaleListingFirearmType;
use App\Enums\SaleListingStatus;
use Database\Factories\SaleListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleListing extends Model
{
    /** @use HasFactory<SaleListingFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'approved_by',
        'rejected_by',
        'title',
        'slug',
        'category',
        'firearm_type',
        'manufacturer',
        'model',
        'caliber',
        'condition',
        'year_of_manufacture',
        'price',
        'price_negotiable',
        'description',
        'location',
        'contact_name',
        'contact_phone',
        'contact_email',
        'show_phone',
        'show_email',
        'status',
        'is_hidden',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'sold_at',
        'expires_at',
        'published_at',
        'expiration_reminder_sent_at',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'category' => SaleListingCategory::class,
            'firearm_type' => SaleListingFirearmType::class,
            'condition' => SaleListingCondition::class,
            'status' => SaleListingStatus::class,
            'price' => 'decimal:2',
            'price_negotiable' => 'boolean',
            'show_phone' => 'boolean',
            'show_email' => 'boolean',
            'is_hidden' => 'boolean',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'sold_at' => 'datetime',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'expiration_reminder_sent_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by')->withTrashed();
    }

    public function images(): HasMany
    {
        return $this->hasMany(SaleListingImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(SaleListingImage::class)
            ->where('is_primary', true)
            ->orderBy('sort_order');
    }

    public function moderations(): HasMany
    {
        return $this->hasMany(SaleListingModeration::class)->latest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(SaleListingReport::class)->latest();
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', SaleListingStatus::Approved->value)
            ->where('is_hidden', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $builder): void {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isPubliclyVisible(): bool
    {
        return ! $this->trashed()
            && $this->status === SaleListingStatus::Approved
            && ! $this->is_hidden
            && $this->published_at?->lte(now()) === true
            && ($this->expires_at === null || $this->expires_at->gt(now()));
    }

    public function formattedPrice(): string
    {
        if ($this->price === null) {
            return 'Cena do uzgodnienia';
        }

        return number_format((float) $this->price, 2, ',', ' ').' zł';
    }
}
