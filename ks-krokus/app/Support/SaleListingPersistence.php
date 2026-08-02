<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\SaleListingStatus;
use App\Http\Requests\SaleListingFormRequest;
use App\Models\SaleListing;
use App\Models\SaleListingImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SaleListingPersistence
{
    /** @var list<string> */
    private const EDITABLE_FIELDS = [
        'title', 'category', 'firearm_type', 'manufacturer', 'model', 'caliber',
        'condition', 'year_of_manufacture', 'price', 'price_negotiable',
        'description', 'location', 'contact_name', 'contact_phone', 'contact_email',
        'show_phone', 'show_email',
    ];

    public function __construct(
        private readonly SaleListingImageStorage $imageStorage,
        private readonly SaleListingWorkflow $workflow,
    ) {}

    public function create(SaleListingFormRequest $request): SaleListing
    {
        $uploads = $this->prepareUploads($request);

        try {
            return DB::transaction(function () use ($request, $uploads): SaleListing {
                $data = Arr::only($request->validated(), self::EDITABLE_FIELDS);
                $data['user_id'] = $request->user()->getKey();
                $data['slug'] = UniqueSlug::for(SaleListing::class, (string) $data['title']);
                $data['status'] = SaleListingStatus::Draft;
                $listing = SaleListing::query()->create($data);
                $this->createImages($listing, $uploads, $request);
                $this->workflow->recordCreated($listing, $request->user());

                if ($request->input('intent') === 'pending') {
                    $this->workflow->submit($listing, $request->user());
                }

                return $listing->refresh();
            });
        } catch (Throwable $exception) {
            $this->deletePaths($this->uploadPaths($uploads));

            throw $exception;
        }
    }

    public function update(
        SaleListingFormRequest $request,
        SaleListing $listing,
    ): SaleListing {
        $uploads = $this->prepareUploads($request);

        try {
            [$updated, $pathsToDelete] = DB::transaction(function () use ($request, $listing, $uploads): array {
                $locked = SaleListing::query()
                    ->whereKey($listing->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $images = $locked->images()->lockForUpdate()->get();
                $fromStatus = $locked->status;
                $data = Arr::only($request->validated(), self::EDITABLE_FIELDS);

                if ($locked->title !== $data['title']) {
                    $data['slug'] = UniqueSlug::for(
                        SaleListing::class,
                        (string) $data['title'],
                        (int) $locked->getKey(),
                    );
                }

                $locked->update($data);
                $pathsToDelete = $this->updateImages($locked, $images, $uploads, $request);

                if (! $request->user()->canManageContent() && $fromStatus === SaleListingStatus::Approved) {
                    $locked->update([
                        'status' => SaleListingStatus::Draft,
                        'approved_by' => null,
                        'approved_at' => null,
                        'published_at' => null,
                        'expires_at' => null,
                        'expiration_reminder_sent_at' => null,
                    ]);
                }

                $this->workflow->recordEdited($locked, $request->user(), $fromStatus);

                $shouldSubmit = (
                    ! $request->user()->canManageContent()
                    && $fromStatus === SaleListingStatus::Approved
                ) || (
                    $request->input('intent') === 'pending'
                    && $locked->user_id === $request->user()->getKey()
                    && in_array($locked->status, [SaleListingStatus::Draft, SaleListingStatus::Rejected], true)
                );

                if ($shouldSubmit) {
                    $this->workflow->submit($locked, $request->user());
                }

                return [$locked->refresh(), $pathsToDelete];
            });
        } catch (Throwable $exception) {
            $this->deletePaths($this->uploadPaths($uploads));

            throw $exception;
        }

        $this->deletePaths($pathsToDelete);

        return $updated;
    }

    public function duplicate(SaleListing $source, User $author): SaleListing
    {
        $source->load('images');
        $copiedPaths = [];

        try {
            return DB::transaction(function () use ($source, $author, &$copiedPaths): SaleListing {
                $copy = $source->replicate([
                    'approved_by', 'rejected_by', 'status', 'rejection_reason',
                    'submitted_at', 'approved_at', 'rejected_at', 'sold_at',
                    'expires_at', 'published_at', 'expiration_reminder_sent_at',
                    'view_count', 'is_hidden',
                ]);
                $copy->user_id = $author->getKey();
                $copy->title = $source->title.' — kopia';
                $copy->slug = UniqueSlug::for(SaleListing::class, $copy->title);
                $copy->status = SaleListingStatus::Draft;
                $copy->view_count = 0;
                $copy->is_hidden = false;
                $copy->save();

                foreach ($source->images as $image) {
                    $path = $this->copyPath($image->path);
                    $copiedPaths[] = $path;
                    $thumbnailPath = $image->thumbnail_path !== null
                        ? $this->copyPath($image->thumbnail_path)
                        : null;
                    if ($thumbnailPath !== null) {
                        $copiedPaths[] = $thumbnailPath;
                    }
                    $copy->images()->create([
                        'path' => $path,
                        'thumbnail_path' => $thumbnailPath,
                        'alt_text' => $image->alt_text,
                        'caption' => $image->caption,
                        'sort_order' => $image->sort_order,
                        'is_primary' => $image->is_primary,
                    ]);
                }

                $this->workflow->recordCreated($copy, $author);

                return $copy;
            });
        } catch (Throwable $exception) {
            $this->deletePaths($copiedPaths);

            throw $exception;
        }
    }

    /** @return list<array{path: string, thumbnail_path: string|null, index: int}> */
    private function prepareUploads(SaleListingFormRequest $request): array
    {
        $files = $request->file('images', []);

        if (! is_array($files)) {
            return [];
        }

        $uploads = [];

        try {
            foreach (array_values($files) as $index => $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $stored = $this->imageStorage->store($file);
                $uploads[] = [...$stored, 'index' => $index];
            }
        } catch (Throwable $exception) {
            $this->deletePaths($this->uploadPaths($uploads));

            throw $exception;
        }

        return $uploads;
    }

    /** @param list<array{path: string, thumbnail_path: string|null, index: int}> $uploads */
    private function createImages(
        SaleListing $listing,
        array $uploads,
        SaleListingFormRequest $request,
    ): void {
        $created = [];
        $alts = (array) $request->input('new_image_alt', []);
        $captions = (array) $request->input('new_image_caption', []);

        foreach ($uploads as $position => $upload) {
            $created[] = $listing->images()->create([
                'path' => $upload['path'],
                'thumbnail_path' => $upload['thumbnail_path'],
                'alt_text' => filled($alts[$upload['index']] ?? null)
                    ? $alts[$upload['index']]
                    : $listing->title,
                'caption' => $captions[$upload['index']] ?? null,
                'sort_order' => $position,
                'is_primary' => false,
            ]);
        }

        if ($created !== []) {
            $primaryIndex = (int) $request->input('primary_new_index', 0);
            ($created[$primaryIndex] ?? $created[0])->update(['is_primary' => true]);
        }
    }

    /**
     * @param  Collection<int, SaleListingImage>  $images
     * @param  list<array{path: string, thumbnail_path: string|null, index: int}>  $uploads
     * @return list<string>
     */
    private function updateImages(
        SaleListing $listing,
        $images,
        array $uploads,
        SaleListingFormRequest $request,
    ): array {
        $deleteIds = $this->normalizedIds((array) $request->input('delete_images', []));
        $selected = $images->whereIn('id', $deleteIds);
        $pathsToDelete = $selected->flatMap(
            static fn (SaleListingImage $image): array => $image->filePaths(),
        )->values()->all();

        if ($selected->isNotEmpty()) {
            $listing->images()->whereKey($selected->modelKeys())->delete();
        }

        $metadata = (array) $request->input('existing_images', []);

        foreach ($images->whereNotIn('id', $deleteIds) as $image) {
            $values = $metadata[$image->getKey()] ?? [];

            if (! is_array($values)) {
                continue;
            }

            $image->update([
                'alt_text' => $values['alt_text'] ?? null,
                'caption' => $values['caption'] ?? null,
                'sort_order' => (int) ($values['sort_order'] ?? $image->sort_order),
            ]);
        }

        $nextOrder = ((int) $listing->images()->max('sort_order')) + 1;
        $alts = (array) $request->input('new_image_alt', []);
        $captions = (array) $request->input('new_image_caption', []);
        $createdByIndex = [];

        foreach ($uploads as $upload) {
            $createdByIndex[$upload['index']] = $listing->images()->create([
                'path' => $upload['path'],
                'thumbnail_path' => $upload['thumbnail_path'],
                'alt_text' => filled($alts[$upload['index']] ?? null)
                    ? $alts[$upload['index']]
                    : $listing->title,
                'caption' => $captions[$upload['index']] ?? null,
                'sort_order' => $nextOrder++,
                'is_primary' => false,
            ]);
        }

        $remaining = $listing->images()->orderBy('sort_order')->orderBy('id')->get();

        if ($remaining->isNotEmpty()) {
            $primary = null;
            $primaryNewIndex = $request->input('primary_new_index');
            $primaryImageId = $request->integer('primary_image_id');

            if ($primaryNewIndex !== null) {
                $primary = $createdByIndex[(int) $primaryNewIndex] ?? null;
            }

            $primary ??= $remaining->firstWhere('id', $primaryImageId);
            $primary ??= $remaining->firstWhere('is_primary', true);
            $primary ??= $remaining->first();
            $listing->images()->update(['is_primary' => false]);
            $primary->update(['is_primary' => true]);
        }

        return $pathsToDelete;
    }

    private function copyPath(string $source): string
    {
        $disk = Storage::disk(config('listings.media_disk'));
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $directory = trim(pathinfo($source, PATHINFO_DIRNAME), '.');
        $target = $directory.'/'.Str::uuid().($extension !== '' ? '.'.$extension : '');

        if (! $disk->copy($source, $target)) {
            throw ValidationException::withMessages([
                'images' => 'Nie udało się skopiować zdjęć do nowego ogłoszenia.',
            ]);
        }

        return $target;
    }

    /** @param list<array{path: string, thumbnail_path: string|null, index: int}> $uploads @return list<string|null> */
    private function uploadPaths(array $uploads): array
    {
        return array_merge(...array_map(
            static fn (array $upload): array => [$upload['path'], $upload['thumbnail_path']],
            $uploads,
        )) ?: [];
    }

    /** @param list<string|null> $paths */
    private function deletePaths(array $paths): void
    {
        try {
            if (! $this->imageStorage->delete($paths)) {
                Log::warning('Nie wszystkie pliki ogłoszenia zostały usunięte.', ['paths' => $paths]);
            }
        } catch (Throwable $exception) {
            report($exception);
            Log::warning('Nie udało się usunąć plików ogłoszenia.', ['paths' => $paths]);
        }
    }

    /** @param array<array-key, mixed> $values @return list<int> */
    private function normalizedIds(array $values): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): ?int => filter_var($value, FILTER_VALIDATE_INT) ?: null,
            $values,
        ))));
    }
}
