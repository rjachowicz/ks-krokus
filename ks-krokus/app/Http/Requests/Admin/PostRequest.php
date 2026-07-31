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
            'remove_cover' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array', "max:{$maxGalleryImages}"],
            'gallery_images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                "max:{$maxImageSize}",
                'dimensions:max_width=12000,max_height=12000',
            ],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['array'],
            'existing_images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'existing_images.*.caption' => ['nullable', 'string', 'max:1000'],
            'existing_images.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
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
            $existingCount = $post?->images()->count() ?? 0;
            $deleteImageIds = $this->input('delete_images', []);

            if (! is_array($deleteImageIds)) {
                $deleteImageIds = [];
            }

            $deletedCount = $post?->images()
                ->whereKey($deleteImageIds)
                ->count() ?? 0;
            $newFiles = $this->file('gallery_images', []);
            $newCount = is_array($newFiles) ? count($newFiles) : 0;

            $maxGalleryImages = (int) config('content.gallery_max_images');

            if (($existingCount - $deletedCount + $newCount) > $maxGalleryImages) {
                $validator->errors()->add(
                    'gallery_images',
                    "Galeria może zawierać maksymalnie {$maxGalleryImages} zdjęć.",
                );
            }

            $existingImageData = $this->input('existing_images', []);

            if (is_array($existingImageData) && $existingImageData !== []) {
                $submittedIds = array_values(array_filter(
                    array_map(
                        static fn (mixed $id): ?int => filter_var(
                            $id,
                            FILTER_VALIDATE_INT,
                        ) !== false ? (int) $id : null,
                        array_keys($existingImageData),
                    ),
                    static fn (?int $id): bool => $id !== null,
                ));
                $ownedCount = $post?->images()
                    ->whereKey($submittedIds)
                    ->count() ?? 0;

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
            'delete_images.*.exists' => 'Co najmniej jedno usuwane zdjęcie nie należy do tej aktualności.',
        ];
    }
}
