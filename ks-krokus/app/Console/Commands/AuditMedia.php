<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\SaleListingImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class AuditMedia extends Command
{
    protected $signature = 'media:audit';

    protected $description = 'Sprawdza, czy pliki mediów wskazane w bazie istnieją na skonfigurowanym dysku.';

    public function handle(): int
    {
        $diskName = (string) config('media.disk');
        $disk = Storage::disk($diskName);
        $groups = [
            'Okładki aktualności — źródła' => Post::withTrashed()->pluck('cover_image_path')->all(),
            'Okładki aktualności — warianty' => Post::withTrashed()->pluck('cover_variant_path')->all(),
            'Galerie aktualności — źródła' => PostImage::query()->pluck('path')->all(),
            'Galerie aktualności — miniatury' => PostImage::query()->pluck('thumbnail_path')->all(),
            'Ogłoszenia — źródła' => SaleListingImage::query()->pluck('path')->all(),
            'Ogłoszenia — miniatury' => SaleListingImage::query()->pluck('thumbnail_path')->all(),
        ];
        $rows = [];
        $missingTotal = 0;
        $referencedTotal = 0;

        try {
            foreach ($groups as $label => $paths) {
                $paths = array_values(array_filter($paths, static fn (mixed $path): bool => is_string($path) && $path !== ''));
                $missing = 0;

                foreach ($paths as $path) {
                    if (! $disk->exists($path)) {
                        $missing++;
                    }
                }

                $referencedTotal += count($paths);
                $missingTotal += $missing;
                $rows[] = [$label, count($paths), $missing];
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->error("Nie udało się odczytać dysku mediów „{$diskName}”.");

            return self::FAILURE;
        }

        $this->components->info("Audyt dysku mediów: {$diskName}");
        $this->table(['Zakres', 'Referencje', 'Brakujące'], $rows);
        $this->line("Łącznie referencji: {$referencedTotal}");
        $this->line("Łącznie brakujących plików: {$missingTotal}");
        $this->components->info('Audyt był tylko do odczytu. Żaden rekord ani plik nie został usunięty.');

        return $missingTotal === 0 ? self::SUCCESS : self::FAILURE;
    }
}
