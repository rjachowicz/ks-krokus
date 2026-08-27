<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\MediaAsset;
use App\Support\PostContentSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'content_format',
        'cover_image_path',
        'cover_variant_path',
        'cover_crop',
        'cover_image_alt',
        'status',
        'published_at',
        'author_id',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'cover_crop' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PublicationStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverUrl(): ?string
    {
        if ($this->cover_image_path === null) {
            return null;
        }

        return MediaAsset::url($this->cover_image_path);
    }

    public function coverVariantUrl(): ?string
    {
        return MediaAsset::firstUrl($this->cover_variant_path, $this->cover_image_path);
    }

    public function safeContentHtml(): string
    {
        if ($this->content_format === 'html') {
            return app(PostContentSanitizer::class)->sanitize($this->content);
        }

        return nl2br(e($this->content));
    }

    public function plainTextContent(): string
    {
        if ($this->content_format === 'html') {
            return trim(preg_replace(
                '/\s+/u',
                ' ',
                html_entity_decode(strip_tags($this->content)),
            ) ?? '');
        }

        return $this->content;
    }
}
