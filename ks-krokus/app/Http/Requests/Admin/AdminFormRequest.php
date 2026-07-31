<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class AdminFormRequest extends FormRequest
{
    public function messages(): array
    {
        return [
            'required' => 'Pole :attribute jest wymagane.',
            'required_without' => 'Pole :attribute jest wymagane, gdy pole :values nie zostało uzupełnione.',
            'confirmed' => 'Potwierdzenie pola :attribute nie jest zgodne.',
            'email' => 'Pole :attribute musi zawierać poprawny adres e-mail.',
            'url' => 'Pole :attribute musi zawierać poprawny adres URL.',
            'date' => 'Pole :attribute musi zawierać poprawną datę.',
            'after_or_equal' => 'Pole :attribute musi wskazywać datę nie wcześniejszą niż :date.',
            'string' => 'Pole :attribute musi być tekstem.',
            'integer' => 'Pole :attribute musi być liczbą całkowitą.',
            'boolean' => 'Pole :attribute ma niepoprawną wartość.',
            'array' => 'Pole :attribute ma niepoprawny format.',
            'image' => 'Plik w polu :attribute musi być obrazem.',
            'mimes' => 'Plik w polu :attribute musi mieć jeden z formatów: :values.',
            'max.string' => 'Pole :attribute może mieć maksymalnie :max znaków.',
            'max.numeric' => 'Pole :attribute nie może być większe niż :max.',
            'max.file' => 'Plik w polu :attribute może mieć maksymalnie :max KB.',
            'max.array' => 'Pole :attribute może zawierać maksymalnie :max elementów.',
            'min.numeric' => 'Pole :attribute nie może być mniejsze niż :min.',
            'unique' => 'Taka wartość pola :attribute jest już używana.',
            'exists' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
            'distinct' => 'Pole :attribute zawiera powtarzające się wartości.',
            'enum' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
            'password.letters' => 'Pole :attribute musi zawierać co najmniej jedną literę.',
            'password.mixed' => 'Pole :attribute musi zawierać małą i wielką literę.',
            'password.numbers' => 'Pole :attribute musi zawierać co najmniej jedną cyfrę.',
            'password.min' => 'Pole :attribute musi mieć co najmniej :min znaków.',
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
            'excerpt' => 'krótkie streszczenie', 'content' => 'treść',
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
