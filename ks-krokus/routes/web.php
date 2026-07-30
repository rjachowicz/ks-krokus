<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/klub', 'pages.club')->name('club');
Route::view('/kontakt', 'pages.contact')->name('contact');
Route::view('/regulamin', 'pages.rules')->name('rules');
Route::view('/rodo', 'pages.rodo')->name('rodo');

Route::permanentRedirect('/informacje-klubowe', '/klub')
    ->name('club.legacy');
