<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostImage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'path',
        'alt_text',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function url(): string
    {
        return Storage::disk(config('content.media_disk'))->url($this->path);
    }
}
