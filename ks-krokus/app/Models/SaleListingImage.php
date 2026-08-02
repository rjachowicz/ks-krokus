<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SaleListingImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SaleListingImage extends Model
{
    /** @use HasFactory<SaleListingImageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'path',
        'thumbnail_path',
        'alt_text',
        'caption',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(SaleListing::class, 'sale_listing_id');
    }

    public function url(): string
    {
        return Storage::disk(config('listings.media_disk'))->url($this->path);
    }

    public function thumbnailUrl(): string
    {
        return Storage::disk(config('listings.media_disk'))->url($this->thumbnail_path ?? $this->path);
    }

    /** @return list<string> */
    public function filePaths(): array
    {
        return array_values(array_filter([$this->path, $this->thumbnail_path]));
    }
}
