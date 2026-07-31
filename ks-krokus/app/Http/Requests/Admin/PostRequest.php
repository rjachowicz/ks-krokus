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
                'max:6144',
            ],
            'cover_image_alt' => ['nullable', 'string', 'max:255'],
            'remove_cover' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array', 'max:12'],
            'gallery_images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:6144',
            ],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'existing_images.*.caption' => ['nullable', 'string', 'max:1000'],
            'existing_images.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:post_images,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (trim(strip_tags((string) $this->input('content'))) === '') {
                $validator->errors()->add('content', 'Wpisz treść aktualności.');
            }

            /** @var Post|null $post */
            $post = $this->route('post');
            $existingCount = $post?->images()->count() ?? 0;
            $deletedCount = $post?->images()
                ->whereKey($this->input('delete_images', []))
                ->count() ?? 0;
            $newFiles = $this->file('gallery_images', []);
            $newCount = is_array($newFiles) ? count($newFiles) : 0;

            if (($existingCount - $deletedCount + $newCount) > 12) {
                $validator->errors()->add(
                    'gallery_images',
                    'Galeria może zawierać maksymalnie 12 zdjęć.',
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('content_format') === 'html') {
            $this->merge([
                'content' => app(PostContentSanitizer::class)
                    ->sanitize((string) $this->input('content')),
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
        ];
    }
}
