<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SaleListingReportReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleListingReport extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'reporter_id',
        'reviewed_by',
        'reason',
        'details',
        'reporter_hash',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => SaleListingReportReason::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(SaleListing::class, 'sale_listing_id')->withTrashed();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id')->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }
}
