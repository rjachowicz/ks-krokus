<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SaleListing;
use App\Models\SaleListingImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SaleListingImage> */
class SaleListingImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sale_listing_id' => SaleListing::factory(),
            'path' => 'sale-listings/images/'.fake()->uuid().'.jpg',
            'thumbnail_path' => null,
            'alt_text' => 'Zdjęcie przedmiotu',
            'caption' => null,
            'sort_order' => 0,
            'is_primary' => true,
        ];
    }
}
