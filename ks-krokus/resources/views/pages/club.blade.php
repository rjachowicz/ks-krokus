@extends('layouts.app')

@section('title', 'Informacje klubowe — KS Krokus')
@section('meta_description', 'Składki, dokumenty, szkolenia, procedury urzędowe i zasady korzystania ze strzelnicy KS Krokus LOK w Nowym Sączu.')

@section('content')
    <section class="hero regulamin-hero" aria-labelledby="club-page-title">
        <div class="hero-content regulamin-hero-content">
            <span class="category-tag">KOMPENDIUM WIEDZY</span>
            <h1 id="club-page-title">Informacje <span class="highlight">Klubowe</span></h1>
            <p>Zasady finansowe, procedury urzędowe, dokumentacja, szkolenia oraz infrastruktura Klubu Strzeleckiego
                „KROKUS” Ligi Obrony Kraju w Nowym Sączu.</p>
        </div>
    </section>

    <section class="features-section club-section" aria-label="Informacje i procedury klubowe">
        <div class="bento-grid">
            <div id="finanse" class="card bento-span-2 card-equal">
                <div class="card-content-inner">
                    <span class="card-code">SEC_01 // FINANSE I SKŁADKI</span>
                    <h3>Składki Członkowskie i Rachunek Bankowy</h3>
                    <p>Prosimy o dokonywanie wpłat składek członkowskich oraz opłat na licencję PZSS na przypisany dla
                        Klubu rachunek bankowy:</p>

                    <div class="bank-box">
                        <strong>LOK BZW Kraków</strong><br>
                        <span class="bank-account">45 1240 6292 1111 0011 2343 2158</span>
                    </div>

                    <div class="fees-grid">
                        <div class="fee-box">
                            <strong>Składka podstawowa:</strong>
                            <ul class="plain-list">
                                <li>• Mężczyźni: <strong>370 zł</strong></li>
                                <li>• Kobiety: <strong>310 zł</strong></li>
                            </ul>
                        </div>
                        <div class="fee-box">
                            <strong>Składka + Licencja PZSS (2027):</strong>
                            <ul class="plain-list">
                                <li>• Mężczyźni: <strong>420 zł</strong> (składka + 50 zł lic.)</li>
                                <li>• Kobiety: <strong>360 zł</strong> (składka + 50 zł lic.)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="club-info-list">
                        <p><strong>1) Termin płatności:</strong> Składkę członkowską opłacamy w terminie do <strong>31.03.2026
                                r.</strong></p>
                        <p><strong>2) Opis przelewu (składka):</strong> <code>składka członkowska KS Krokus, Imię
                                Nazwisko, rok składki</code></p>
                        <p><strong>3)</strong> Składka wpisowa dla nowych członków: <strong>1000 zł</strong></p>
                        <p><strong>4)</strong> Składka wpisowa dla zawodników przepisujących się z innego klubu
                            (posiadających Patent i licencję): <strong>500 zł</strong></p>
                        <p><strong>5) Opis przelewu (wpisowe):</strong> <code>składka wpisowa KS Krokus, Imię
                                Nazwisko</code></p>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="card-status">TERMIN: 31.03.2026</span>
                    <span class="card-link">KROKUS_FINANCE</span>
                </div>
            </div>

            <div id="dokumenty" class="card card-equal card-highlight-download">
                <div class="card-content-inner">
                    <span class="card-code">SEC_02 // DOKUMENTACJA</span>
                    <div class="resource-badge">PLIK DO POBRANIA</div>
                    <h3>Deklaracja Członkowska</h3>
                    <p>Wstąpienie do K.S. Krokus jest możliwe po złożeniu podpisanej deklaracji członkowskiej Zarządowi
                        Klubu oraz opłaceniu składki wpisowej i członkowskiej na konto Klubu.</p>
                </div>
                <div class="card-footer card-footer-stack">
                    <span class="card-status">FORMAT PDF</span>
                    <a href="{{ asset('files/Deklaracja.pdf') }}" class="btn-action" target="_blank"
                       rel="noopener noreferrer">POBIERZ DEKLARACJĘ
                        &rarr;</a>
                </div>
            </div>

            <div id="szkolenie-patentowe" class="card card-equal">
                <div class="card-content-inner">
                    <span class="card-code">SEC_03 // SZKOLENIE PATENTOWE</span>
                    <div class="resource-badge">NOWY REGULAMIN</div>
                    <h3>Szkolenie do Egzaminu na Patent</h3>
                    <p>Zgodnie z nowym regulaminem PZSS (od 01.07.2025 r.), Zarząd KS KROKUS prowadzi szkolenia dla
                        członków zainteresowanych patentem.</p>
                    <ul class="plain-list">
                        <li>• <strong>Program:</strong> 23 godz. lekcyjne (w tym 6h praktyki z bronią).</li>
                        <li>• <strong>Koszt:</strong> 1.800 zł (instruktor, amunicja, broń i strzelnica). Opłata
                            niezależna od wpisowego.
                        </li>
                    </ul>
                </div>
                <div class="card-footer card-footer-stack">
                    <span class="card-status">WYTYCZNE PZSS</span>
                    <a href="https://www.pzss.org.pl/assets/files/licencyjno-patentowa/regulamin_patentstrzelecki_2025.pdf"
                       target="_blank" rel="noopener noreferrer" class="btn-action">REGULAMIN PZSS &rarr;</a>
                </div>
            </div>

            <div id="refundacja" class="card bento-span-2 card-equal card-highlight-download">
                <div class="card-content-inner">
                    <span class="card-code">SEC_04 // SPORT I REFUNDACJA</span>
                    <div class="resource-badge">WNIOSEK DO POBRANIA</div>
                    <h3>Zwrot Kosztów za Zawody</h3>
                    <p>Klub na wniosek zwraca członkom koszt startowego (opłata wpisowa na zawody) po spełnieniu
                        kryteriów regulaminowych.</p>

                    <h4 class="club-subtitle">Wymogi konieczne do
                        spełnienia:</h4>
                    <ul class="club-list-small">
                        <li>Brak zaległości finansowych względem Klubu na dany rok oraz aktualna licencja zawodnicza.
                        </li>
                        <li>Delegowanie przez Klub do udziału w zawodach z kalendarza PZSS lub MZSS (mistrzostwa Polski
                            / pucharu Polski).
                        </li>
                        <li>Przedstawienie wydatków, komunikatu potwierdzającego udział oraz złożenie wniosku do
                            <strong>30 dni</strong> od zakończenia zawodów.
                        </li>
                    </ul>

                    <div class="invoice-box">
                        <strong>Dane do faktury (zwrot następuje wyłącznie na jej podstawie):</strong><br>
                        Biuro Zarządu Małopolsko-Świętokrzyskiej Organizacji Regionalnej LOK w Krakowie<br>
                        <em>(skrót: Biuro Zarządu Mał. Święt. Org. Reg. LOK)</em>, ul. Pomorska 2, 30-039 Kraków<br>
                        <strong>NIP: 521-008-68-32</strong>
                    </div>
                </div>
                <div class="card-footer card-footer-stack">
                    <span class="card-status">REFUNDACJA STARTOWEGO</span>
                    <a href="{{ asset('files/Wniosek-o-zwrot-startowego.pdf') }}" class="btn-action" target="_blank"
                       rel="noopener noreferrer">POBIERZ
                        WNIOSEK O ZWROT &rarr;</a>
                </div>
            </div>

            <div id="pozwolenie" class="card bento-span-2 card-equal card-highlight-download">
                <div class="card-content-inner">
                    <span class="card-code">SEC_05 // PROCEDURY URZĘDOWE</span>
                    <div class="resource-badge">UCHWAŁA DO POBRANIA</div>
                    <h3>Pozwolenie na Broń – Zaświadczenia Klubowe</h3>
                    <p>Członkowie mogą wystąpić do Komendanta Wojewódzkiego Policji z wnioskiem o wydanie decyzji
                        uprawniającej do posiadania broni do celów sportowych i kolekcjonerskich.</p>

                    <h4 class="club-subtitle">Procedura wydawania
                        zaświadczeń:</h4>
                    <ol class="club-steps">
                        <li>Prześlij zapytanie na <a href="mailto:zarzad@ks-krokus.pl" class="club-link">zarzad@ks-krokus.pl</a>
                            (podaj imię, nazwisko, rodzaj zaświadczeń oraz informację czy to pierwsza decyzja, czy
                            rozszerzenie).
                        </li>
                        <li>Zaświadczenia są podpisywane podpisem zaufanym, wysyłane zwrotnie na maila oraz bezpośrednio
                            do WPA do <strong>1 dnia roboczego</strong>.
                        </li>
                        <li>Wydrukuj zaświadczenie i dołącz do wniosku wraz z pobraną uchwałą o kolekcjonerstwie.</li>
                    </ol>

                    <div class="wpa-box">
                        <strong>Wydział Postępowań Administracyjnych KWP w Krakowie (Siedziba w Nowym
                            Sączu)</strong><br>
                        ul. Grottgera 50, 33-300 Nowy Sącz | Tel: 47 83-34-240; 47 83-34-238; 47 83-34-239<br>
                        <strong>Godziny przyjęć:</strong> Pon: 10:00–14:00 | Wt: 9:00–17:00 | Śr: 10:00–14:00 | Czw:
                        9:00–14:00
                    </div>
                </div>
                <div class="card-footer card-footer-actions">
                    <a href="{{ asset('files/Uchwala_kolekcjonerska.pdf') }}" class="btn-action" target="_blank"
                       rel="noopener noreferrer">POBIERZ
                        UCHWAŁĘ KOLEKCJONERSKĄ &rarr;</a>
                    <a href="https://malopolska.policja.gov.pl/krk/kontakt/pozwolenia-na-bro/310,Pozwolenia-na-bron-i-licencje.html"
                       target="_blank" rel="noopener noreferrer" class="btn-action">STRONA WPA KRAKÓW &rarr;</a>
                </div>
            </div>

            <div class="card card-equal">
                <div class="card-content-inner">
                    <span class="card-code">SEC_06 // SPOŁECZNOŚĆ</span>
                    <h3>WhatsApp – Społeczność Krokus</h3>

                    <p>
                        Zachęcamy do dołączenia do społeczności K.S. Krokus LOK Nowy Sącz
                        na platformie WhatsApp. Rejestracja jest dobrowolna.
                    </p>

                    <p class="whatsapp-subtext">
                        Zgłoszenia prosimy kierować na
                        <a href="mailto:zarzad@ks-krokus.pl" class="club-link">
                            zarzad@ks-krokus.pl
                        </a>
                        lub do członków społeczności.
                    </p>

                    <div class="whatsapp-disclaimer">
                        * Oficjalne wydarzenia (zawody, szkolenia) publikowane są
                        na stronie internetowej.
                    </div>
                </div>

                <div class="card-footer">
                    <span class="card-status">DOBROWOLNA</span>
                    <span class="card-link">GRUPY TEMATYCZNE</span>
                </div>
            </div>

            <div id="dostep-do-strzelnicy" class="card bento-full card-equal">
                <div class="card-content-inner">
                    <span class="card-code">SEC_07 // INFRASTRUKTURA</span>
                    <h3>Elektroniczny Dostęp do Strzelnicy</h3>
                    <p>Strzelnica przy ul. Tarnowskiej w Nowym Sączu objęta jest systemem kontroli dostępu. Karty (koszt
                        10 zł) wydaje wiceprezes <strong>Michał Kohmann</strong> po spełnieniu warunków:</p>
                    <ul class="plain-list">
                        <li>• Opłacona składka członkowska na bieżący rok.</li>
                        <li>• Staż członkowski min. 2 lata oraz uprawnienia Prowadzącego strzelanie.</li>
                    </ul>

                    <div class="fees-grid">
                        <div class="fee-box">
                            <strong>Przed treningiem:</strong>
                            <ul class="club-list-small">
                                <li>Obowiązkowy wpis do książki strzelań.</li>
                                <li>Zakaz strzelania z karabinów centralnego zapłonu, strzelb amunicją kulową i po
                                    zmroku.
                                </li>
                                <li>Pistolety centralnego zapłonu wyłącznie do tarcz papierowych.</li>
                            </ul>
                        </div>
                        <div class="fee-box">
                            <strong>Po treningu:</strong>
                            <ul class="club-list-small">
                                <li>Segregacja śmieci i zebranie łusek do koszy.</li>
                                <li>Zasunięcie rolet na stanowiskach.</li>
                                <li>Zamknięcie drzwi na oś i posprzątanie po sobie.</li>
                            </ul>
                        </div>
                    </div>

                    <h4 class="club-subtitle">Osoby uprawnione z
                        dostępem do strzelnicy:</h4>
                    <div class="persons-grid">
                        <div class="person-tile">
                            <span>Michał Kohmann</span><br><a href="tel:+48515117066" class="club-link">515 117 066</a>
                        </div>
                        <div class="person-tile">
                            <span>Piotr Duszyński</span><br><a href="tel:+48514006212" class="club-link">514 006 212</a>
                        </div>
                        <div class="person-tile">
                            <span>Marcin Ogórek</span><br><a href="tel:+48723771675" class="club-link">723 771 675</a>
                        </div>
                        <div class="person-tile">
                            <span>Marcin Oświęcimka</span><br><a href="tel:+48660478716" class="club-link">660 478
                                716</a></div>
                        <div class="person-tile">
                            <span>Dawid Żołądź</span><br><a href="tel:+48692590900" class="club-link">692 590 900</a>
                        </div>
                        <div class="person-tile">
                            <span>Tadeusz Górka</span><br><a href="tel:+48502939561" class="club-link">502 939 561</a>
                        </div>
                        <div class="person-tile">
                            <span>Andrzej Węgrzyn</span><br><a href="tel:+48606310891" class="club-link">606 310 891</a>
                        </div>
                        <div class="person-tile">
                            <span>Dariusz Cichostępski</span><br><a href="tel:+48508301091" class="club-link">508 301
                                091</a></div>
                        <div class="person-tile">
                            <span>Kinga Zmarzły-Jarek</span><br><a href="tel:+48609517403" class="club-link">609 517
                                403</a></div>
                        <div class="person-tile">
                            <span>Krzysztof Bereś</span><br><a href="tel:+48885788281" class="club-link">885 788 281</a>
                        </div>
                        <div class="person-tile">
                            <span>Leszek Kaliński</span><br><a href="tel:+48696039354" class="club-link">696 039 354</a>
                        </div>
                        <div class="person-tile">
                            <span>Marek Kaczor</span><br><a href="tel:+48509875809" class="club-link">509 875 809</a>
                        </div>
                        <div class="person-tile">
                            <span>Paweł Rosiek</span><br><a href="tel:+48601529351" class="club-link">601 529 351</a>
                        </div>
                        <div class="person-tile">
                            <span>Marcin Zieliński</span><br><a href="tel:+48512938279" class="club-link">512 938
                                279</a></div>
                        <div class="person-tile">
                            <span>Marcin Schiller</span><br><a href="tel:+48693291557" class="club-link">693 291 557</a>
                        </div>
                        <div class="person-tile">
                            <span>Szymon Mika</span><br><a href="tel:+48734433382" class="club-link">734 433 382</a>
                        </div>
                        <div class="person-tile">
                            <span>Łukasz Pierzchała</span><br><a href="tel:+48501455396" class="club-link">501 455
                                396</a></div>
                        <div class="person-tile">
                            <span>Łukasz Poremba</span><br><a href="tel:+48666147148" class="club-link">666 147 148</a>
                        </div>
                        <div class="person-tile">
                            <span>Michał Potoczek</span><br><a href="tel:+48602267089" class="club-link">602 267 089</a>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="card-status">KONTROLA DOSTĘPU</span>
                    <span class="card-link">UL. TARNOWSKA</span>
                </div>
            </div>


        </div>
    </section>
@endsection
