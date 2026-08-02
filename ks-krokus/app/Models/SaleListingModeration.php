<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SaleListingModerationAction;
use App\Enums\SaleListingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleListingModeration extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'actor_id',
        'action',
        'from_status',
        'to_status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'action' => SaleListingModerationAction::class,
            'from_status' => SaleListingStatus::class,
            'to_status' => SaleListingStatus::class,
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(SaleListing::class, 'sale_listing_id')->withTrashed();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }
}
