<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class UniqueSlug
{
    /**
     * @param class-string<Model> $modelClass
     */
    public static function for(
        string $modelClass,
        string $source,
        ?int $ignoreId = null,
        string $column = 'slug',
    ): string {
        if (! is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException('Podana klasa nie jest modelem Eloquent.');
        }

        $base = Str::slug($source);

        if ($base === '') {
            $base = 'wpis';
        }

        $slug = $base;
        $counter = 2;

        $model = new $modelClass();
        $keyName = $model->getKeyName();

        while (self::slugExists(
            modelClass: $modelClass,
            model: $model,
            keyName: $keyName,
            column: $column,
            slug: $slug,
            ignoreId: $ignoreId,
        )) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private static function slugExists(
        string $modelClass,
        Model $model,
        string $keyName,
        string $column,
        string $slug,
        ?int $ignoreId,
    ): bool {
        $query = $modelClass::query();

        if (method_exists($model, 'getDeletedAtColumn')) {
            $query->withTrashed();
        }

        return $query
            ->when(
                $ignoreId !== null,
                fn ($builder) => $builder->where($keyName, '!=', $ignoreId),
            )
            ->where($column, $slug)
            ->exists();
    }
}
