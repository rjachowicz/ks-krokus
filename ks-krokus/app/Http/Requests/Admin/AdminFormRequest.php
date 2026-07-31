<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\LocalizedFormRequest;

abstract class AdminFormRequest extends LocalizedFormRequest
{
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'title.required' => 'Podaj tytuł.',
            'name.required' => 'Podaj nazwę.',
            'location_name.required' => 'Podaj miejsce wydarzenia.',
            'start_at.required' => 'Podaj datę rozpoczęcia.',
            'score.required' => 'Podaj wynik.',
            'content.required' => 'Wpisz treść aktualności.',
            'cover_image.mimes' => 'Wybrane zdjęcie musi być w formacie JPG, PNG lub WEBP.',
            'gallery_images.*.mimes' => 'Każde zdjęcie musi być w formacie JPG, PNG lub WEBP.',
            'cover_image.uploaded' => 'Nie udało się przesłać zdjęcia głównego.',
            'gallery_images.*.uploaded' => 'Nie udało się przesłać jednego ze zdjęć galerii.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nazwa', 'title' => 'tytuł', 'slug' => 'identyfikator URL',
            'code' => 'kod', 'email' => 'adres e-mail', 'password' => 'hasło',
            'role' => 'rola systemowa', 'phone' => 'numer telefonu',
            'description' => 'opis', 'sort_order' => 'kolejność', 'is_active' => 'aktywność',
            'is_trainer' => 'status trenera', 'has_range_access' => 'dostęp do strzelnicy',
            'show_email_publicly' => 'publiczna widoczność adresu e-mail',
            'show_phone_publicly' => 'publiczna widoczność telefonu', 'trainer_bio' => 'opis trenera',
            'user_ids' => 'przypisani użytkownicy', 'user_ids.*' => 'przypisany użytkownik',
            'user_sort_orders.*' => 'kolejność użytkownika', 'discipline' => 'dyscyplina',
            'competition_system' => 'system rozgrywek', 'event_type' => 'rodzaj wydarzenia',
            'start_at' => 'data rozpoczęcia', 'end_at' => 'data zakończenia',
            'location_name' => 'miejsce', 'address' => 'adres', 'status' => 'status',
            'is_public' => 'widoczność publiczna', 'registration_url' => 'adres zapisów',
            'competition_ids' => 'konkurencje', 'competition_ids.*' => 'konkurencja',
            'excerpt' => 'krótkie streszczenie', 'content' => 'treść', 'content_format' => 'format treści',
            'published_at' => 'data publikacji', 'cover_image' => 'zdjęcie główne',
            'cover_image_alt' => 'tekst alternatywny zdjęcia', 'gallery_images' => 'galeria zdjęć',
            'gallery_images.*' => 'zdjęcie w galerii',
            'existing_images.*.alt_text' => 'tekst alternatywny zdjęcia',
            'existing_images.*.caption' => 'podpis zdjęcia',
            'existing_images.*.sort_order' => 'kolejność zdjęcia',
            'delete_images.*' => 'usuwane zdjęcie', 'event_competition_id' => 'wydarzenie i konkurencja',
            'user_id' => 'powiązany użytkownik', 'participant_name' => 'imię i nazwisko zawodnika',
            'club_name' => 'klub', 'category' => 'kategoria', 'score' => 'wynik',
            'place' => 'miejsce', 'classification' => 'klasyfikacja', 'notes' => 'uwagi',
        ];
    }
}
