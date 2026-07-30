<?php

declare(strict_types=1);

return [
    'name' => 'Klub Strzelecki „KROKUS” LOK',
    'full_name' => 'Klub Strzelecki „KROKUS” Ligi Obrony Kraju w Nowym Sączu',
    'short_name' => 'KS Krokus',
    'email' => 'zarzad@ks-krokus.pl',
    'founded_year' => 1999,
    'annual_events' => 20,

    'address' => [
        'street' => 'ul. Tarnowska 32',
        'postal_code' => '33-300',
        'city' => 'Nowy Sącz',
        'formatted' => 'ul. Tarnowska 32, 33-300 Nowy Sącz',
    ],

    'invoice' => [
        'name' => 'Biuro Zarządu Małopolsko-Świętokrzyskiej Organizacji Regionalnej LOK w Krakowie',
        'short_name' => 'Biuro Zarządu Mał.-Święt. Org. Reg. LOK',
        'address' => 'ul. Pomorska 2, 30-039 Kraków',
        'nip' => '521-008-68-32',
    ],

    'finance' => [
        'account_holder' => 'LOK BZW Kraków',
        'account_number' => '45 1240 6292 1111 0011 2343 2158',
        'membership_deadline' => '31.03.2026',
        'membership_year' => 2026,
        'license_year' => 2027,
        'license_fee' => 50,
        'fees' => [
            'membership' => [
                'men' => 370,
                'women' => 310,
            ],
            'membership_with_license' => [
                'men' => 420,
                'women' => 360,
            ],
            'entry_new_member' => 1000,
            'entry_transfer' => 500,
        ],
        'transfer_titles' => [
            'membership' => 'składka członkowska KS Krokus, Imię Nazwisko, rok składki',
            'entry' => 'składka wpisowa KS Krokus, Imię Nazwisko',
        ],
    ],

    'patent_training' => [
        'effective_from' => '01.07.2025',
        'hours' => 23,
        'practice_hours' => 6,
        'price' => 1800,
        'regulations_url' => 'https://www.pzss.org.pl/assets/files/licencyjno-patentowa/regulamin_patentstrzelecki_2025.pdf',
    ],

    'documents' => [
        'membership_declaration' => 'files/Deklaracja.pdf',
        'refund_application' => 'files/Wniosek-o-zwrot-startowego.pdf',
        'collectors_resolution' => 'files/Uchwala_kolekcjonerska.pdf',
    ],

    'firearm_permit' => [
        'wpa_url' => 'https://malopolska.policja.gov.pl/krk/kontakt/pozwolenia-na-bro/310,Pozwolenia-na-bron-i-licencje.html',
        'office' => [
            'name' => 'Wydział Postępowań Administracyjnych KWP w Krakowie (Siedziba w Nowym Sączu)',
            'address' => 'ul. Grottgera 50, 33-300 Nowy Sącz',
            'phones' => ['47 83-34-240', '47 83-34-238', '47 83-34-239'],
            'hours' => 'Pon: 10:00–14:00 | Wt: 9:00–17:00 | Śr: 10:00–14:00 | Czw: 9:00–14:00',
        ],
    ],

    'range_access' => [
        'card_cost' => 10,
        'issuer' => 'Michał Kohmann',
        'requirements' => [
            'Opłacona składka członkowska na bieżący rok.',
            'Staż członkowski minimum 2 lata oraz uprawnienia Prowadzącego strzelanie.',
        ],
        'before_training' => [
            'Obowiązkowy wpis do książki strzelań.',
            'Zakaz strzelania z karabinów centralnego zapłonu, strzelb amunicją kulową i po zmroku.',
            'Pistolety centralnego zapłonu wyłącznie do tarcz papierowych.',
        ],
        'after_training' => [
            'Segregacja śmieci i zebranie łusek do koszy.',
            'Zasunięcie rolet na stanowiskach.',
            'Zamknięcie drzwi na oś i posprzątanie po sobie.',
        ],
    ],

    'locations' => [
        [
            'name' => 'Nowy Sącz, ul. Tarnowska 32',
            'type' => 'STRZELNICA SPORTOWA',
            'description' => 'Główny obiekt treningowy i siedziba klubu.',
            'status' => 'NOWY SĄCZ',
            'span' => 2,
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d728!2d20.6942834!3d49.6353922!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x473dfaabef77b20f%3A0x807ec81260609079!2sKlub%20Strzelecki%20Krokus%20LOK%20Nowy%20S%C4%85cz!5e0!3m2!1spl!2spl!4v1650000000000!5m2!1spl!2spl',
            'map_url' => 'https://www.google.com/maps/search/?api=1&query=Klub+Strzelecki+Krokus+LOK+Nowy+S%C4%85cz',
        ],
        [
            'name' => 'Tylicz „u Leśników”',
            'type' => 'STRZELNICA MYŚLIWSKA',
            'description' => 'Leśny Zakład Doświadczalny w Krynicy-Zdroju.',
            'status' => 'TYLICZ',
            'span' => 1,
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2596.11!2d20.9934041!3d49.4060517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x473e77da8622b5ef%3A0x6b4be703772cd116!2sStrzelnica%20My%C5%9Bliwska%20%22u%20Le%C5%9Bnik%C3%B3w%22%20Le%C5%9Bny%20Zak%C5%82ad%20Do%C5%9Bwiadczalny%20w%20Krynicy-Zdroju!5e0!3m2!1spl!2spl!4v1650000000000!5m2!1spl!2spl',
            'map_url' => 'https://www.google.com/maps/search/?api=1&query=Strzelnica+My%C5%9Bliwska+u+Le%C5%9Bnik%C3%B3w+Tylicz',
        ],
    ],
];
