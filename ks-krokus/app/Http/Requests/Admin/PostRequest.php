<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\PublicationStatus;
use App\Models\Post;
use App\Support\PostContentSanitizer;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PostRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() === true;
    }

    public function rules(): array
    {
        $maxImageSize = (int) config('content.image_max_size_kb');
        $maxGalleryImages = (int) config('content.gallery_max_images');
        /** @var Post|null $post */
        $post = $this->route('post');
        $postId = $post?->getKey() ?? 0;

        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string', 'max:100000'],
            'content_format' => ['nullable', Rule::in(['plain', 'html'])],
            'status' => ['required', Rule::enum(PublicationStatus::class)],
            'published_at' => ['nullable', 'date'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                "max:{$maxImageSize}",
                'dimensions:max_width=12000,max_height=12000',
            ],
            'cover_image_alt' => ['nullable', 'string', 'max:255'],
            'cover_crop' => ['nullable', 'array:x,y,width,height'],
            'cover_crop.x' => ['required_with:cover_crop', 'numeric', 'between:0,1'],
            'cover_crop.y' => ['required_with:cover_crop', 'numeric', 'between:0,1'],
            'cover_crop.width' => ['required_with:cover_crop', 'numeric', 'gt:0', 'max:1'],
            'cover_crop.height' => ['required_with:cover_crop', 'numeric', 'gt:0', 'max:1'],
            'remove_cover' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array', "max:{$maxGalleryImages}"],
            'gallery_images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                "max:{$maxImageSize}",
                'dimensions:max_width=12000,max_height=12000',
            ],
            'gallery_crops' => ['nullable', 'array', "max:{$maxGalleryImages}"],
            'gallery_crops.*' => ['array:x,y,width,height'],
            'gallery_crops.*.x' => ['required', 'numeric', 'between:0,1'],
            'gallery_crops.*.y' => ['required', 'numeric', 'between:0,1'],
            'gallery_crops.*.width' => ['required', 'numeric', 'gt:0', 'max:1'],
            'gallery_crops.*.height' => ['required', 'numeric', 'gt:0', 'max:1'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['array'],
            'existing_images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'existing_images.*.caption' => ['nullable', 'string', 'max:1000'],
            'existing_images.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'existing_images.*.crop' => ['nullable', 'array:x,y,width,height'],
            'existing_images.*.crop.x' => ['required_with:existing_images.*.crop', 'numeric', 'between:0,1'],
            'existing_images.*.crop.y' => ['required_with:existing_images.*.crop', 'numeric', 'between:0,1'],
            'existing_images.*.crop.width' => ['required_with:existing_images.*.crop', 'numeric', 'gt:0', 'max:1'],
            'existing_images.*.crop.height' => ['required_with:existing_images.*.crop', 'numeric', 'gt:0', 'max:1'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => [
                'integer',
                Rule::exists('post_images', 'id')->where('post_id', $postId),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $content = $this->input('content');

            if (
                ! is_string($content)
                || trim(strip_tags($content)) === ''
            ) {
                $validator->errors()->add('content', 'Wpisz treść aktualności.');
            }

            /** @var Post|null $post */
            $post = $this->route('post');
            $postImageIds = $post instanceof Post
                ? $post->images()
                    ->pluck('id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all()
                : [];
            $existingCount = count($postImageIds);
            $deleteImageIds = $this->normalizedImageIds(
                $this->input('delete_images', []),
            );
            $deletedCount = count(array_intersect(
                $postImageIds,
                $deleteImageIds,
            ));
            $newFiles = $this->file('gallery_images', []);
            $newCount = is_array($newFiles) ? count($newFiles) : 0;

            $maxGalleryImages = (int) config('content.gallery_max_images');

            if (($existingCount - $deletedCount + $newCount) > $maxGalleryImages) {
                $validator->errors()->add(
                    'gallery_images',
                    "Galeria może zawierać maksymalnie {$maxGalleryImages} zdjęć.",
                );
            }

            $this->validateCropBounds($validator, ['cover' => $this->input('cover_crop')], 'cover_crop');
            $this->validateCropBounds($validator, (array) $this->input('gallery_crops', []), 'gallery_crops');

            foreach ((array) $this->input('existing_images', []) as $imageId => $values) {
                if (is_array($values) && isset($values['crop'])) {
                    $this->validateCropBounds(
                        $validator,
                        [$imageId => $values['crop']],
                        'existing_images',
                    );
                }
            }

            $existingImageData = $this->input('existing_images', []);

            if (is_array($existingImageData) && $existingImageData !== []) {
                $submittedIds = $this->normalizedImageIds(
                    array_keys($existingImageData),
                );
                $ownedCount = count(array_intersect(
                    $postImageIds,
                    $submittedIds,
                ));

                if (
                    count($submittedIds) !== count($existingImageData)
                    || $ownedCount !== count(array_unique($submittedIds))
                ) {
                    $validator->errors()->add(
                        'existing_images',
                        'Co najmniej jedno edytowane zdjęcie nie należy do tej aktualności.',
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $content = $this->input('content');

        if (
            $this->input('content_format') === 'html'
            && is_string($content)
        ) {
            $this->merge([
                'content' => app(PostContentSanitizer::class)
                    ->sanitize($content),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'title.required' => 'Podaj tytuł aktualności.',
            'content.required' => 'Wpisz treść aktualności.',
            'status.required' => 'Wybierz status publikacji.',
            'cover_image.uploaded' => 'Nie udało się przesłać zdjęcia głównego. Pojedynczy plik może mieć maksymalnie 6 MB.',
            'cover_image.image' => 'Zdjęcie główne musi być prawidłowym obrazem.',
            'cover_image.mimes' => 'Zdjęcie główne musi być w formacie JPG, PNG lub WebP.',
            'cover_image.max' => 'Zdjęcie główne może mieć maksymalnie 6 MB.',
            'cover_image.dimensions' => 'Zdjęcie główne może mieć maksymalnie 12 000 × 12 000 pikseli.',
            'gallery_images.max' => 'Galeria może zawierać maksymalnie 12 zdjęć.',
            'gallery_images.*.uploaded' => 'Nie udało się przesłać jednego ze zdjęć galerii. Pojedynczy plik może mieć maksymalnie 6 MB.',
            'gallery_images.*.image' => 'Każdy plik galerii musi być prawidłowym obrazem.',
            'gallery_images.*.mimes' => 'Zdjęcia galerii muszą być w formacie JPG, PNG lub WebP.',
            'gallery_images.*.max' => 'Każde zdjęcie galerii może mieć maksymalnie 6 MB.',
            'gallery_images.*.dimensions' => 'Każde zdjęcie galerii może mieć maksymalnie 12 000 × 12 000 pikseli.',
            'cover_crop.*.numeric' => 'Współrzędne kadru okładki muszą być liczbami.',
            'gallery_crops.*.*.numeric' => 'Współrzędne kadru miniatury muszą być liczbami.',
            'delete_images.*.exists' => 'Co najmniej jedno usuwane zdjęcie nie należy do tej aktualności.',
        ];
    }

    /**
     * @return list<int>
     */
    private function normalizedImageIds(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $ids = [];

        foreach ($values as $value) {
            if (! is_int($value) && ! is_string($value)) {
                continue;
            }

            $id = filter_var($value, FILTER_VALIDATE_INT);

            if ($id !== false && $id > 0) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /** @param array<array-key, mixed> $crops */
    private function validateCropBounds(Validator $validator, array $crops, string $prefix): void
    {
        foreach ($crops as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $x = filter_var($value['x'] ?? null, FILTER_VALIDATE_FLOAT);
            $y = filter_var($value['y'] ?? null, FILTER_VALIDATE_FLOAT);
            $width = filter_var($value['width'] ?? null, FILTER_VALIDATE_FLOAT);
            $height = filter_var($value['height'] ?? null, FILTER_VALIDATE_FLOAT);

            if (
                $x !== false
                && $y !== false
                && $width !== false
                && $height !== false
                && (($x + $width) > 1.000001 || ($y + $height) > 1.000001)
            ) {
                $field = match ($prefix) {
                    'cover_crop' => 'cover_crop',
                    'existing_images' => "existing_images.{$key}.crop",
                    default => "{$prefix}.{$key}",
                };
                $validator->errors()->add($field, 'Kadr nie może wykraczać poza zdjęcie.');
            }
        }
    }
}
