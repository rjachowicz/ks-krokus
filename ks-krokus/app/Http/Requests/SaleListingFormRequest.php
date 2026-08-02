<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingCondition;
use App\Enums\SaleListingFirearmType;
use App\Enums\SaleListingStatus;
use App\Models\SaleListing;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class SaleListingFormRequest extends LocalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        $email = $this->input('contact_email');

        $this->merge([
            'contact_email' => is_string($email) ? mb_strtolower(trim($email)) : $email,
            'price_negotiable' => $this->boolean('price_negotiable'),
            'show_phone' => $this->boolean('show_phone'),
            'show_email' => $this->boolean('show_email'),
        ]);
    }

    public function rules(): array
    {
        $maxSize = (int) config('listings.image_max_size_kb');
        $maxImages = (int) config('listings.max_images');
        $listing = $this->route('saleListing');
        $listingId = $listing instanceof SaleListing ? (int) $listing->getKey() : 0;

        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(SaleListingCategory::class)],
            'firearm_type' => ['nullable', Rule::enum(SaleListingFirearmType::class)],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'caliber' => ['nullable', 'string', 'max:64'],
            'condition' => ['nullable', Rule::enum(SaleListingCondition::class)],
            'year_of_manufacture' => ['nullable', 'integer', 'min:1800', 'max:'.now()->year],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'price_negotiable' => ['boolean'],
            'description' => ['required', 'string', 'min:30', 'max:20000'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'required_if:show_phone,1', 'string', 'max:32'],
            'contact_email' => ['nullable', 'required_if:show_email,1', 'email', 'max:255'],
            'show_phone' => ['boolean'],
            'show_email' => ['boolean'],
            'intent' => ['nullable', Rule::in(['draft', 'pending', 'save'])],
            'images' => ['nullable', 'array', "max:{$maxImages}"],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                "max:{$maxSize}",
                'dimensions:max_width=12000,max_height=12000',
            ],
            'new_image_alt' => ['nullable', 'array', "max:{$maxImages}"],
            'new_image_alt.*' => ['nullable', 'string', 'max:255'],
            'new_image_caption' => ['nullable', 'array', "max:{$maxImages}"],
            'new_image_caption.*' => ['nullable', 'string', 'max:1000'],
            'primary_new_index' => ['nullable', 'integer', 'min:0', 'max:9'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['array'],
            'existing_images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'existing_images.*.caption' => ['nullable', 'string', 'max:1000'],
            'existing_images.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'primary_image_id' => [
                'nullable',
                'integer',
                Rule::exists('sale_listing_images', 'id')->where('sale_listing_id', $listingId),
            ],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => [
                'integer',
                Rule::exists('sale_listing_images', 'id')->where('sale_listing_id', $listingId),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $description = $this->input('description');

            if (is_string($description) && strip_tags($description) !== $description) {
                $validator->errors()->add('description', 'Opis nie może zawierać kodu HTML.');
            }

            $listing = $this->route('saleListing');
            $existingIds = $listing instanceof SaleListing
                ? $listing->images()->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all()
                : [];
            $submittedIds = $this->normalizedIds(array_keys((array) $this->input('existing_images', [])));
            $foreignMetadata = array_diff($submittedIds, $existingIds);

            if ($foreignMetadata !== []) {
                $validator->errors()->add('existing_images', 'Nie można edytować zdjęć innego ogłoszenia.');
            }

            $deleteIds = $this->normalizedIds((array) $this->input('delete_images', []));
            $deletedCount = count(array_intersect($existingIds, $deleteIds));
            $newFiles = $this->file('images', []);
            $newCount = is_array($newFiles) ? count($newFiles) : 0;
            $remainingCount = count($existingIds) - $deletedCount + $newCount;
            $maxImages = (int) config('listings.max_images');

            if ($remainingCount > $maxImages) {
                $validator->errors()->add('images', "Możesz dodać maksymalnie {$maxImages} zdjęć.");
            }

            $ownerEditingApproved = $listing instanceof SaleListing
                && $listing->status === SaleListingStatus::Approved
                && $this->user()?->canManageContent() === false;

            if (($this->input('intent') === 'pending' || $ownerEditingApproved) && $remainingCount < 1) {
                $validator->errors()->add('images', 'Dodaj co najmniej jedno zdjęcie.');
            }
        });
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'title.required' => 'Podaj tytuł ogłoszenia.',
            'category.required' => 'Wybierz kategorię.',
            'description.required' => 'Podaj pełny opis przedmiotu.',
            'description.min' => 'Opis musi mieć co najmniej 30 znaków.',
            'price.numeric' => 'Cena musi być liczbą.',
            'price.min' => 'Cena nie może być wartością ujemną.',
            'contact_phone.required_if' => 'Podaj telefon albo wyłącz zgodę na jego publikację.',
            'contact_email.required_if' => 'Podaj adres e-mail albo wyłącz zgodę na jego publikację.',
            'images.max' => 'Możesz dodać maksymalnie 10 zdjęć.',
            'images.*.uploaded' => 'Nie udało się przesłać zdjęcia. Pojedynczy plik może mieć maksymalnie 6 MB.',
            'images.*.image' => 'Każdy przesłany plik musi być obrazem.',
            'images.*.mimes' => 'Zdjęcie musi być w formacie JPG, PNG lub WEBP.',
            'images.*.max' => 'Jedno zdjęcie może mieć maksymalnie 6 MB.',
            'images.*.dimensions' => 'Zdjęcie ma zbyt duże wymiary. Maksymalnie 12000 × 12000 px.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'tytuł ogłoszenia',
            'category' => 'kategoria',
            'firearm_type' => 'rodzaj przedmiotu',
            'manufacturer' => 'producent',
            'model' => 'model',
            'caliber' => 'kaliber',
            'condition' => 'stan',
            'year_of_manufacture' => 'rok produkcji',
            'price' => 'cena',
            'price_negotiable' => 'cena do negocjacji',
            'description' => 'opis',
            'location' => 'lokalizacja',
            'contact_name' => 'imię kontaktowe',
            'contact_phone' => 'telefon',
            'contact_email' => 'adres e-mail',
            'show_phone' => 'zgoda na publikację telefonu',
            'show_email' => 'zgoda na publikację adresu e-mail',
            'images' => 'zdjęcia',
            'images.*' => 'zdjęcie',
            'new_image_alt.*' => 'tekst alternatywny zdjęcia',
            'new_image_caption.*' => 'opis zdjęcia',
            'existing_images' => 'zapisane zdjęcia',
            'existing_images.*.alt_text' => 'tekst alternatywny zdjęcia',
            'existing_images.*.caption' => 'opis zdjęcia',
            'existing_images.*.sort_order' => 'kolejność zdjęcia',
            'primary_image_id' => 'zdjęcie główne',
            'delete_images' => 'usuwane zdjęcia',
            'delete_images.*' => 'usuwane zdjęcie',
        ];
    }

    /** @return list<string> */
    protected function oldInputArrayFields(): array
    {
        return [
            'images',
            'new_image_alt',
            'new_image_caption',
            'existing_images',
            'delete_images',
        ];
    }

    /** @param array<array-key, mixed> $values @return list<int> */
    private function normalizedIds(array $values): array
    {
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
}
