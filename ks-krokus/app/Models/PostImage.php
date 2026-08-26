<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\MediaAsset;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'path',
        'thumbnail_path',
        'crop',
        'alt_text',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'crop' => 'array',
        ];
    }

    public function url(): ?string
    {
        return MediaAsset::url($this->path);
    }

    public function thumbnailUrl(): ?string
    {
        return MediaAsset::firstUrl($this->thumbnail_path, $this->path);
    }

    /** @return list<string> */
    public function filePaths(): array
    {
        return array_values(array_filter([$this->path, $this->thumbnail_path]));
    }
}
